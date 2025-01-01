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
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AgenceController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/ResetPassword', [AuthController::class, 'resetPassword']);
Route::post('/sendResetPasswordLink', [AuthController::class, 'sendResetPasswordLink']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route::get('devises', [DeviseController::class, 'index']);            
    // Route::post('devises', [DeviseController::class, 'store']);           
    // Route::get('devises/{id}', [DeviseController::class, 'show']);       
    // Route::put('devises/{id}', [DeviseController::class, 'update']);     
    // Route::delete('devises/{id}', [DeviseController::class, 'destroy']);  
    Route::apiResource('devises', DeviseController::class);

    Route::apiResource('users', UserController::class);
    Route::get('contact', [UserController::class, 'createContact']);
    Route::apiResource('agences', AgenceController::class);
    Route::get('/check-token-header', [AuthController::class, 'checkTokenInHeader']);

    Route::apiResource('roles', RoleController::class);
});
// Route::post('resend-verification-email', [AuthController::class, 'resendVerificationEmail']);


Route::post('/assign-role', [RoleController::class, 'assignRole']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
     // Route::post('/permissions', [PermissionController::class, 'create']);
    // Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    // Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    Route::get('/assign-role', [RolePermissionController::class, 'assignRole']);

    Route::apiResource('/devises', DeviseController::class);
});

Route::post('users/{userId}/assign-role', [RolePermissionController::class, 'assignRole']);// Assigner un rôle à un utilisateur
Route::post('users/{userId}/revoke-role', [RolePermissionController::class, 'revokeRole']);// Retirer un rôle d'un utilisateur
Route::post('users/{userId}/assign-permission', [RolePermissionController::class, 'assignPermission']);// Assigner une permission à un utilisateur
Route::post('users/{userId}/revoke-permission', [RolePermissionController::class, 'revokePermission']);// Retirer une permission d'un utilisateur

Route::post('roles/{roleId}/assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole']);// Assigner une ou plusieurs permissions à un rôle
Route::post('roles/{roleId}/revoke-permission', [RolePermissionController::class, 'revokePermissionFromRole']);// Retirer une permission d'un rôle

Route::get('/roles-permissions-liste', [RolePermissionController::class, 'listRolesPermissions']); // Lister rôles et permissions


 
use App\Http\Controllers\AgentController;
Route::apiResource('agents', AgentController::class);

use App\Http\Controllers\TauxEchangeController;
Route::get('taux-echanges', [TauxEchangeController::class, 'index']);
Route::post('taux-echanges', [TauxEchangeController::class, 'store']);
Route::put('taux-echanges/{tauxEchange}', [TauxEchangeController::class, 'update']);
Route::delete('taux-echanges/{tauxEchange}', [TauxEchangeController::class, 'destroy']);

use App\Http\Controllers\ConversionController;
Route::get('conversions', [ConversionController::class, 'index']);
Route::post('conversions', [ConversionController::class, 'store']);
Route::get('conversions/{conversion}', [ConversionController::class, 'show']);
Route::put('conversions/{conversion}', [ConversionController::class, 'update']);
Route::delete('conversions/{conversion}', [ConversionController::class, 'destroy']);

use App\Http\Controllers\TransfertController;
Route::apiResource('/transferts', TransfertController::class);


