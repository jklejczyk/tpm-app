<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MachineController;
use App\Http\Controllers\Api\V1\OeeController;
use App\Http\Controllers\Api\V1\ProductionRecordController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users/role/{role}', [UserController::class, 'byRole']);

    Route::get('machines', [MachineController::class, 'index']);
    Route::get('machines/{id}/oee', [OeeController::class, 'show']);

    Route::get('production-records', [ProductionRecordController::class, 'index']);
    Route::post('production-records', [ProductionRecordController::class, 'store']);

    Route::get('work-orders', [WorkOrderController::class, 'index']);
    Route::post('work-orders', [WorkOrderController::class, 'report']);
    Route::get('work-orders/{id}', [WorkOrderController::class, 'show']);

    Route::post('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign']);
    Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'start']);
    Route::post('work-orders/{workOrder}/hold', [WorkOrderController::class, 'hold']);
    Route::post('work-orders/{workOrder}/resume', [WorkOrderController::class, 'resume']);
    Route::post('work-orders/{workOrder}/resolve', [WorkOrderController::class, 'resolve']);
    Route::post('work-orders/{workOrder}/close', [WorkOrderController::class, 'close']);
});
