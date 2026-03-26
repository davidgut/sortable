<?php

namespace DavidGut\Sortable\Http\Controllers;

use DavidGut\Sortable\Contracts\Sortable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SortableController extends Controller
{
    /**
     * Handle the sort order update request for a given model instance.
     */
    public function __invoke(Request $request, string $model, $id): JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => 'required|integer|min:0',
        ]);

        $modelClass = $this->resolveModelClass($model);

        if ($modelClass === null) {
            abort(404, 'Model not found');
        }

        $instance = $modelClass::findOrFail($id);

        if (!$instance instanceof Sortable) {
            abort(400, 'Model does not implement the Sortable contract');
        }

        if (!$instance->canBeSortedBy(Auth::user())) {
            abort(403, 'Unauthorized');
        }

        $instance->moveTo($validated['sort_order']);

        return response()->json(['message' => 'Sort order updated']);
    }

    /**
     * Resolve the model class from config, or by convention in non-production environments.
     */
    private function resolveModelClass(string $model): string|null
    {
        $modelClass = config("sortable.models.{$model}");

        if ($modelClass !== null) {
            return $modelClass;
        }

        if (App::environment('production')) {
            return null;
        }

        $guess = "App\\Models\\{$model}";

        return class_exists($guess) ? $guess : null;
    }
}
