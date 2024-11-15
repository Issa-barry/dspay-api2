<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Api\DeviseController;

Route::apiResource('users', UserController::class);
Route::get('contact', [UserController::class, 'createContact']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/ResetPassword', [AuthController::class, 'resetPassword']);
Route::post('/sendResetPasswordLink', [AuthController::class, 'sendResetPasswordLink']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
// Route::post('resend-verification-email', [AuthController::class, 'resendVerificationEmail']);


// Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'create']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/assign-role', [RoleController::class, 'assignRole']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'create']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    Route::get('/assign-role', [RolePermissionController::class, 'assignRole']);

    Route::apiResource('/devises', DeviseController::class);
// });

Route::post('users/{userId}/assign-role', [RolePermissionController::class, 'assignRole']);// Assigner un rôle à un utilisateur
Route::post('users/{userId}/revoke-role', [RolePermissionController::class, 'revokeRole']);// Retirer un rôle d'un utilisateur
Route::post('users/{userId}/assign-permission', [RolePermissionController::class, 'assignPermission']);// Assigner une permission à un utilisateur
Route::post('users/{userId}/revoke-permission', [RolePermissionController::class, 'revokePermission']);// Retirer une permission d'un utilisateur

Route::post('roles/{roleId}/assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole']);// Assigner une ou plusieurs permissions à un rôle
Route::post('roles/{roleId}/revoke-permission', [RolePermissionController::class, 'revokePermissionFromRole']);// Retirer une permission d'un rôle

    // Route::get('devises', [DeviseController::class, 'index']);            
    // Route::post('devises', [DeviseController::class, 'store']);           
    // Route::get('devises/{id}', [DeviseController::class, 'show']);       
    // Route::put('devises/{id}', [DeviseController::class, 'update']);     
    // Route::delete('devises/{id}', [DeviseController::class, 'destroy']);  
    Route::apiResource('devises', DeviseController::class);



use App\Http\Controllers\AgenceController;


Route::apiResource('agences', AgenceController::class);
 
use App\Http\Controllers\TauxController;
Route::apiResource('taux', TauxController::class);

use App\Http\Controllers\AgentController;
Route::apiResource('agents', AgentController::class);
