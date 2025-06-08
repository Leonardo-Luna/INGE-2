<?php

namespace App\Services;

use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailService {

    public function __construct(private TransportInterface $mailer) { }

    public function EnviarPassword($password, $to) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Finalización de registro')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p>Tu contraseña para ingresar al sistema es <b>' . $password . '</b>. No la compartas con nadie.</p>');

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }

    public function Enviar2FA($code, $to) {
        
        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Inicio de sesión en dos pasos')
            ->text('Se ha detectado un inicio de sesión desde tu cuenta de gerente.')
            ->html('<p>Tu código de inicio de sesión en dos pasos es <b>' . $code . '</b>. No lo compartas con nadie.</p>');

        $sent = $this->mailer->send($email);

    }

    public function EnviarRecuperarPassword($link, $to) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live','Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Recuperación de contraseña')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p>Has solicitado recuperar tu contraseña. Si no fuiste tú, ignora este mensaje.</p>
                    <p>Si fuiste tú, haz click aqui para recuperar tu contraseña: <a href="' . $link . '">Recuperar contraseña</a></p>');
       $sent = $this->mailer->send($email);           

    }

    public function EnviarCancelacionSinCuponYSinPolitica($nombreMaquina, $total, $to) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live','Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Cancelación de reserva')
            ->text('Tu cuenta de usuario ha sido eliminada.')
            ->html('<p>Tu cuenta de usuario ha sido eliminada, acércate con este e-mail a tu sucursal más cercana para solicitar tu reembolso de la máquina. <b>' . $nombreMaquina . '</b> por un total de <b>' . $total . '</b>.</p>');
        $sent = $this->mailer->send($email);           

    }

    public function reembolso($monto, $to) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Confirmacion de reembolso')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p> Por la cancelacion de tu reserva te corresponde un reembolso por un monto de: <b>' . $monto . '</b>. Presentate con este correo en tu sucursal mas cercana para recibir tu dinero</p>');

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }
}