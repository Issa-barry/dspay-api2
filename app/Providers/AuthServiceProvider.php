<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Les policies pour votre application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Enregistrer les services d'authentification et les policies.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Personnaliser le lien de réinitialisation pour les API
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url("/api/reset-password/{$token}?email={$user->email}");
        });

         // Définir l'expiration des routes signées à 1 minute.
         URL::defaults([
            'expire' => now()->addMinutes(1), 
        ]);
    }
 
}
