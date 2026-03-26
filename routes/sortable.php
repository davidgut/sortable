<?php

use DavidGut\Sortable\Http\Controllers\SortableController;
use Illuminate\Support\Facades\Route;

Route::put('/sortable/{model}/{id}', SortableController::class)
    ->middleware('web')
    ->name('sortable.update');
