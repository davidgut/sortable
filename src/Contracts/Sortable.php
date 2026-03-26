<?php

namespace DavidGut\Sortable\Contracts;

interface Sortable
{
    /**
     * Determine if the user can sort the model.
     */
    public function canBeSortedBy($user): bool;

    /**
     * Move the model to a new position.
     */
    public function setPosition(int $newPosition): void;

    /**
     * Get the name of the column used for sorting.
     */
    public function getPositionColumn(): string;

    /**
     * Get the position scope column name if defined.
     */
    public function getPositionScopeColumn(): string|null;
}
