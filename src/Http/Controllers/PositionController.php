<?php

namespace DavidGut\Sortable\Http\Controllers;

use DavidGut\Sortable\Traits\HasPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    /**
     * Handles the position update request for a given model instance.
     * 
     * This method:
     * 1. Validates the existence of the model class
     * 2. Finds the model instance
     * 3. Checks if the model uses the HasPosition trait
     * 4. Verifies user authorization
     * 5. Updates the position
     * 6. Returns a JSON response
     */
    public function __invoke(Request $request, string $model, $id): JsonResponse
    {
        $modelClass = config("sortable.models.{$model}");

        if ($modelClass === null) {
            if (class_exists("App\\Models\\{$model}")) {
                $modelClass = "App\\Models\\{$model}";
            } elseif (class_exists($model)) {
                $modelClass = $model;
            } else {
                abort(404, 'Model not found');
            }
        }

        $instance = $modelClass::findOrFail($id);

        if (!in_array(HasPosition::class, class_uses_recursive($instance))) {
            return response()->json(['error' => 'Model does not use HasPosition trait'], 400);
        }

        // Check if the user is authorized to update the position
        if (!$this->isAuthorized($instance)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $instance->setPositionFromRequest($request);

        return response()->json(['message' => 'Position updated successfully']);
    }

    /**
     * Checks if the current user is authorized to update the position of the given model.
     */
    private function isAuthorized(Model $model): bool
    {
        if (method_exists($model, 'canBePositionedBy')) {
            return $model->canBePositionedBy(Auth::user());
        }

        // Deny by default
        return false;
    }
}
