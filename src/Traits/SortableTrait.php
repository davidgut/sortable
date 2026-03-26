<?php

namespace DavidGut\Sortable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait SortableTrait
{
    /**
     * The column name used to scope sort order.
     * When set, sort order will be scoped to records with the same value.
     * 
     * Define this property in your model to enable scoping:
     * protected string $sortScope = 'parent_id';
     * 
     * This means each parent_id value will have its own sort sequence:
     * - parent_id = 1: sort_order 0, 1, 2, 3...
     * - parent_id = 2: sort_order 0, 1, 2, 3...
     * - parent_id = null: sort_order 0, 1, 2, 3...
     */

    /**
     * Boot the SortableTrait.
     * 
     * Sets up a creating event listener that automatically assigns a
     * sort order to new models.
     */
    protected static function bootSortableTrait(): void
    {
        static::creating(function (Model $model) {
            if ($model->{$model->getSortColumn()} === null) {
                $maxPosition = $model->sortQuery()->max($model->getSortColumn());
                $model->{$model->getSortColumn()} = $maxPosition !== null ? $maxPosition + 1 : 0;
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
     * Updates the sort order of the current model and adjusts
     * the sort order of other affected models accordingly.
     */
    public function moveTo(int $newPosition): void
    {
        $sortColumn = $this->getSortColumn();
        $oldPosition = $this->{$sortColumn};

        if ($newPosition === $oldPosition) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($oldPosition, $newPosition, $sortColumn) {
            $this->shiftSortOrder($oldPosition, $newPosition);

            $this->{$sortColumn} = $newPosition;
            $this->save();
        });
    }

    /**
     * Shift the sort order of affected models.
     */
    protected function shiftSortOrder(int $oldPosition, int $newPosition): void
    {
        $sortColumn = $this->getSortColumn();
        $query = $this->sortQuery();

        if ($newPosition > $oldPosition) {
            $query->whereBetween($sortColumn, [$oldPosition + 1, $newPosition])
                ->decrement($sortColumn);
        } else {
            $query->whereBetween($sortColumn, [$newPosition, $oldPosition - 1])
                ->increment($sortColumn);
        }
    }

    /**
     * Get a base sort query before applying scope.
     * Override this for custom base queries.
     */
    protected function baseSortQuery(): Builder
    {
        return static::query();
    }

    /**
     * Apply the sort scope to a query if $sortScope is set.
     */
    protected function applySortScope(Builder $query): Builder
    {
        if (property_exists($this, 'sortScope') && $this->sortScope !== null) {
            $scopeValue = $this->getAttribute($this->sortScope);

            if ($scopeValue === null) {
                $query->whereNull($this->sortScope);
            } else {
                $query->where($this->sortScope, $scopeValue);
            }
        }

        return $query;
    }

    /**
     * Get the sort query for the model.
     * 
     * Override this for complex custom queries.
     * The $sortScope property will be applied automatically first.
     * 
     * Example:
     * protected function sortQuery(): Builder
     * {
     *     return parent::sortQuery()->where('is_active', true);
     * }
     */
    protected function sortQuery(): Builder
    {
        $query = $this->baseSortQuery();

        return $this->applySortScope($query);
    }

    /**
     * Get the sort scope column name if defined.
     */
    public function getSortScopeColumn(): string|null
    {
        return property_exists($this, 'sortScope') ? $this->sortScope : null;
    }

    /**
     * Get the name of the column used for sorting.
     */
    public function getSortColumn(): string
    {
        return property_exists($this, 'sortColumn') ? $this->sortColumn : 'sort_order';
    }

    /**
     * Get the sortable update URL for this model instance.
     */
    public function sortableUrl(): string
    {
        $class = get_class($this);
        $alias = array_search($class, config('sortable.models', []));

        if ($alias === false) {
            $alias = class_basename($class);
        }

        return route('sortable.update', ['model' => $alias, 'id' => $this->getKey()]);
    }

    /**
     * Scope the query to order by sort column.
     */
    public function scopeSorted(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy($this->getSortColumn(), $direction);
    }
}
