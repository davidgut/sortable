<?php

namespace DavidGut\Sortable\Contracts;

interface Sortable
{
    /**
     * Determine if the user can sort the model.
     */
    public function canBeSortedBy($user): bool;

    /**
     * Move the model to a new sort position.
     */
    public function moveTo(int $newPosition): void;

    /**
     * Get the name of the column used for sorting.
     */
    public function getSortColumn(): string;

    /**
     * Get the sort scope column name if defined.
     */
    public function getSortScopeColumn(): string|null;
}
