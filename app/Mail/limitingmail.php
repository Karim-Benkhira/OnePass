<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LimitingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Alerte : Limitation de Taux')
                    ->view('emails.limiting')
                    ->with([
                        'messageContent' => "L'API limitera les tentatives de connexion a 10 tentatives par seconde. Apres avoir depasse ce seuil, l'utilisateur sera bloque pendant une heure."
                    ]);
    }
}
