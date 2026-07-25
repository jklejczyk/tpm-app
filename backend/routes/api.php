<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('work-orders', [WorkOrderController::class, 'index']);
    Route::post('work-orders', [WorkOrderController::class, 'report']);
    Route::get('work-orders/{id}', [WorkOrderController::class, 'show']);

    Route::post('work-orders/{id}/assign', [WorkOrderController::class, 'assign']);
    Route::post('work-orders/{id}/start', [WorkOrderController::class, 'start']);
    Route::post('work-orders/{id}/hold', [WorkOrderController::class, 'hold']);
    Route::post('work-orders/{id}/resume', [WorkOrderController::class, 'resume']);
    Route::post('work-orders/{id}/resolve', [WorkOrderController::class, 'resolve']);
    Route::post('work-orders/{id}/close', [WorkOrderController::class, 'close']);
});
