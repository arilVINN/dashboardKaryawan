<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Kadiv\KadivTaskController;
use Illuminate\Http\Request;

/**
 * Minimal wrapper controller to handle legacy routes that reference KadivController.
 * It forwards the request to the appropriate existing controller methods.
 * This maintains backward compatibility without altering existing business logic.
 */
class KadivController extends Controller
{
    /**
     * Store a new task.
     * Delegates to KadivTaskController::store.
     */
    public function store(Request $request)
    {
        // Resolve the KadivTaskController via the container to honour any dependencies.
        $taskController = app(KadivTaskController::class);
        return $taskController->store($request);
    }
}
