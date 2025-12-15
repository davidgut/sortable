<?php

namespace DavidGut\Sortable\Contracts;

interface Sortable
{
    /**
     * Determine if the user can sort the model.
     * 
     * @param  mixed  $user
     * @return bool
     */
    public function canBePositionedBy($user): bool;
}
