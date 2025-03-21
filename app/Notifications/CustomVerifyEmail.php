<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Génère l'URL de vérification personnalisée.
     */
    protected function verificationUrl($notifiable)
    {
        $signedURL = URL::temporarySignedRoute(
            'verification.verify', // Nom de la route Laravel
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // 👉 Redirige vers ton frontend avec l'URL signée en paramètre
        // return 'https://ton-frontend.com/email-verified?redirect=' . urlencode($signedURL);
        return 'http://localhost:4200/#/auth/validation?redirect=' . urlencode($signedURL);

    }

    /**
     * Contenu de l'e-mail envoyé à l'utilisateur.
     */
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Vérification de votre adresse e-mail')
            ->line('Merci pour votre inscription. Veuillez cliquer sur le bouton ci-dessous pour valider votre adresse e-mail.')
            ->action('Vérifier mon e-mail', $url)
            ->line('Si vous n’avez pas créé de compte, aucune action n’est requise.');
    }
}
 