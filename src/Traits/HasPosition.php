<?php

namespace DavidGut\Sortable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasPosition
{
    /**
     * The column name used to scope positions.
     * When set, positions will be scoped to records with the same value.
     * 
     * Define this property in your model to enable scoping:
     * protected string $positionScope = 'parent_id';
     * 
     * This means each parent_id value will have its own position sequence:
     * - parent_id = 1: positions 0, 1, 2, 3...
     * - parent_id = 2: positions 0, 1, 2, 3...
     * - parent_id = null: positions 0, 1, 2, 3...
     */

    /**
     * Boot the HasPosition trait.
     * 
     * Sets up a creating event listener that automatically assigns a
     * position to new models.
     */
    protected static function bootHasPosition(): void
    {
        static::creating(function (Model $model) {
            if ($model->{$model->getPositionColumn()} === null) {
                $maxPosition = $model->getPositionQuery()->max($model->getPositionColumn());
                $model->{$model->getPositionColumn()} = $maxPosition !== null ? $maxPosition + 1 : 0;
            }
        });
    }

    /**
     * Determine if the model can be re-sorted by the given user.
     * By default, only admins can re-sort models.
     * 
     * Override this in your model for custom authorization.
     */
    public function canBeSortedBy($user): bool
    {
        return method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    /**
     * Move the model to a new position.
     * 
     * Updates the position of the current model and adjusts
     * the positions of other affected models accordingly.
     */
    public function setPosition(int $newPosition): void
    {
        $positionColumn = $this->getPositionColumn();
        $oldPosition = $this->{$positionColumn};

        if ($newPosition === $oldPosition) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($oldPosition, $newPosition, $positionColumn) {
            $this->updatePositions($oldPosition, $newPosition);

            $this->{$positionColumn} = $newPosition;
            $this->save();
        });
    }

    /**
     * Update the positions of affected models.
     */
    protected function updatePositions(int $oldPosition, int $newPosition): void
    {
        $positionColumn = $this->getPositionColumn();
        $query = $this->getPositionQuery();

        if ($newPosition > $oldPosition) {
            $query->whereBetween($positionColumn, [$oldPosition + 1, $newPosition])
                ->decrement($positionColumn);
        } else {
            $query->whereBetween($positionColumn, [$newPosition, $oldPosition - 1])
                ->increment($positionColumn);
        }
    }

    /**
     * Get a base position query before applying scope.
     * Override this for custom base queries.
     */
    protected function basePositionQuery(): Builder
    {
        return static::query();
    }

    /**
     * Apply the position scope to a query if $positionScope is set.
     */
    protected function applyPositionScope(Builder $query): Builder
    {
        if (property_exists($this, 'positionScope') && $this->positionScope !== null) {
            $scopeValue = $this->getAttribute($this->positionScope);

            if ($scopeValue === null) {
                $query->whereNull($this->positionScope);
            } else {
                $query->where($this->positionScope, $scopeValue);
            }
        }

        return $query;
    }

    /**
     * Get the position query for the model.
     * 
     * Override this for complex custom queries.
     * The $positionScope property will be applied automatically first.
     * 
     * Example:
     * protected function getPositionQuery(): Builder
     * {
     *     return parent::getPositionQuery()->where('is_active', true);
     * }
     */
    protected function getPositionQuery(): Builder
    {
        $query = $this->basePositionQuery();

        return $this->applyPositionScope($query);
    }

    /**
     * Get the position scope column name if defined.
     */
    public function getPositionScopeColumn(): string|null
    {
        return property_exists($this, 'positionScope') ? $this->positionScope : null;
    }

    /**
     * Get the name of the column used for sorting.
     */
    public function getPositionColumn(): string
    {
        return property_exists($this, 'positionColumn') ? $this->positionColumn : 'position';
    }

    /**
     * Scope the query to order by position.
     */
    public function scopeSorted(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy($this->getPositionColumn(), $direction);
    }
}
