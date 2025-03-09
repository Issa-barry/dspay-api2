<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
 use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\DeviseController;
 use App\Http\Controllers\AgenceController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\User\createUserController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\updateUserController;
use App\Http\Controllers\User\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');
Route::post('/ResetPassword', [AuthController::class, 'resetPassword']);
Route::post('/sendResetPasswordLink', [AuthController::class, 'sendResetPasswordLink']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
// Route::post('resend-verification-email', [AuthController::class, 'resendVerificationEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check-token-header', [AuthController::class, 'checkTokenInHeader']);
});
 
// Route::middleware('auth:sanctum')->group(function () {


Route::apiResource('/devises', DeviseController::class);
    
    Route::apiResource('agents', AgentController::class);
    // Route::apiResource('users', UserController::class);
    // Route::get('contact', [UserController::class, 'createContact']);
    Route::apiResource('agences', AgenceController::class); 
  
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'create']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    Route::apiResource('roles', RoleController::class);
    Route::post('/assign-role', [RoleController::class, 'assignRole']); 
    // Route::post('users/{userId}/assign-role', [RoleController::class, 'assignRole']);// Assigner un rôle à un utilisateur
   
    //Revoke ne marche pas
   // Route::post('users/{userId}/revoke-role', [RoleController::class, 'revokeRole']);// Retirer un rôle d'un utilisateur
    Route::post('users/{userId}/assign-permission', [RolePermissionController::class, 'assignPermission']);// Assigner une permission à un utilisateur
    Route::post('users/{userId}/revoke-permission', [RolePermissionController::class, 'revokePermission']);// Retirer une permission d'un utilisateur

    Route::post('/roles/find-by-name', [RoleController::class, 'findRoleByName']);
    Route::get('roles/{id}/check-users', [RoleController::class, 'checkRoleUsers']);
    Route::get('/roles-permissions-liste', [RolePermissionController::class, 'listRolesPermissions']); // Lister rôles et permissions
    Route::post('roles/{roleId}/assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole']);// Assigner une ou plusieurs permissions à un rôle
    Route::post('roles/{roleId}/revoke-permission', [RolePermissionController::class, 'revokePermissionFromRole']);// Retirer une permission d'un rôle
    Route::get('/role/{roleId}/permissions', [RolePermissionController::class, 'getRolePermissions']);// Route pour récupérer les permissions d'un rôle spécifique
// });

use App\Http\Controllers\TauxEchangeController;
Route::get('taux-echanges', [TauxEchangeController::class, 'index']);
Route::post('taux-echanges', [TauxEchangeController::class, 'store']);
Route::put('taux-echanges/{tauxEchange}', [TauxEchangeController::class, 'update']);
Route::delete('taux-echanges/{tauxEchange}', [TauxEchangeController::class, 'destroy']);

use App\Http\Controllers\ConversionController;
use App\Http\Controllers\Transfert\TransfertEnvoieController;

Route::get('conversions', [ConversionController::class, 'index']);
Route::post('conversions', [ConversionController::class, 'store']);
Route::get('conversions/{conversion}', [ConversionController::class, 'show']);
Route::put('conversions/{conversion}', [ConversionController::class, 'update']);
Route::delete('conversions/{conversion}', [ConversionController::class, 'destroy']);

/**  USER */
Route::get('/users', [ShowUserController::class, 'index']);
Route::get('/users/{id}', [ShowUserController::class, 'show']);
Route::post('/users', [createUserController::class, 'store']);
Route::put('/users/{id}', [updateUserController::class, 'update']);
Route::delete('/users/{id}', [DeleteUserController::class, 'destroy']);


/**  Transfert */
Route::post('/transferts/envoie', [TransfertEnvoieController::class, 'store']);

