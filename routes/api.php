<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\DeviseController;
use App\Http\Controllers\ModelHasPermissionController;

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


Route::apiResource('/devises', DeviseController::class);

// Route::middleware('auth:sanctum')->group(function () { 
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('/permissions', PermissionController::class);
    
    Route::post('/assign-role', [RoleController::class, 'assignRole']);
    Route::get('/assign-role', [RolePermissionController::class, 'assignRole']);

    Route::post('users/{userId}/assign-role', [RolePermissionController::class, 'assignRole']);// Assigner un rôle à un utilisateur
    Route::post('users/{userId}/revoke-role', [RolePermissionController::class, 'revokeRole']);// Retirer un rôle d'un utilisateur
    Route::post('users/{userId}/assign-permission', [RolePermissionController::class, 'assignPermission']);// Assigner une permission à un utilisateur
    Route::post('users/{userId}/revoke-permission', [RolePermissionController::class, 'revokePermission']);// Retirer une permission d'un utilisateur

    Route::post('roles/{roleId}/assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole']);// Assigner une ou plusieurs permissions à un rôle
    Route::post('roles/{roleId}/revoke-permission', [RolePermissionController::class, 'revokePermissionFromRole']);// Retirer une permission d'un rôle
    Route::get('/roles-permissions-liste', [RolePermissionController::class, 'listRolesPermissions']); // Lister rôles et permissions

    // Route pour récupérer les permissions d'un rôle spécifique
Route::get('/role/{roleId}/permissions', [RolePermissionController::class, 'getRolePermissions']);

    Route::prefix('model-permissions')->group(function () {
        Route::post('assign', [ModelHasPermissionController::class, 'assignPermissionToModel']);
        Route::post('list', [ModelHasPermissionController::class, 'getModelPermissions']);
        Route::put('update', [ModelHasPermissionController::class, 'updateModelPermission']);
        Route::delete('revoke', [ModelHasPermissionController::class, 'revokePermissionFromModel']);
    });

// });

use App\Http\Controllers\AgenceController;
Route::apiResource('agences', AgenceController::class);
 
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
Route::post('/transferts/annuler/{id}', [TransfertController::class, 'annulerTransfert']);
Route::post('/transferts/valider-retrait', [TransfertController::class, 'validerRetrait']);

// routes/api.php

use App\Http\Controllers\FactureController;
Route::get('factures', [FactureController::class, 'index']); // Afficher toutes les factures
Route::get('factures/{id}', [FactureController::class, 'show']); // Afficher une facture
Route::post('factures/{id}/payer', [FactureController::class, 'payerFacture']); // Payer une facture
