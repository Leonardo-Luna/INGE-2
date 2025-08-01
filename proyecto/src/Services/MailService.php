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
            ->html('<p> Por la cancelacion de tu reserva te corresponde un reembolso por un monto de: <b>' . $monto . '</b>. Presentate con este correo en tu sucursal más cercana para recibir tu dinero</p>');

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }

      public function reembolsoCupon($monto, $to, $montoCupon, $Cupon, $fecha, $maquina) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Confirmacion de reembolso')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p> Por motivos ajenos a nuestro control nos vimos obligado a cancelar tu reserva de '. $maquina .'
            para el dia ' . $fecha . ' te corresponde un reembolso por un monto de: <b>' . $monto . '</b>. Presentate con este correo en tu sucursal más cercana para recibir tu dinero.
             Ademas, te dejamos un cupon de descuento para tu proxima compra por un monto de: <b>' . $montoCupon . '</b>. Podes usarlo con el codigo de cupon: <b>' . $Cupon . '</b>. </p>'  );

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }

    public function EnviarReservaFinalizada($total, $to, $nombreMaquina,$diaInicio) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Confirmacion de reembolso')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p> Registramos una nueva reserva a tu nombre por un monto de : $<b>' . $total . '</b>. Presentate con este correo para retirar tu <b>' . $nombreMaquina . '</b> el dia ' . $diaInicio->format('d/m/Y') . '<p>');

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }

    public function EnviarComentario($comentario, $to, $nombreMaquina, $estrellas) {

        $email = (new Email())
            ->from(new Address('alquilar@leostrange.live', 'Alquil.AR'))
            ->to($to)
            ->replyTo('ivtech.unlp@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Alquil.AR | Confirmacion de reembolso')
            ->text('¡Gracias por usar Alquil.AR!')
            ->html('<p> Esperamos que hayas tenido una excelente experiencia con el alquiler de tu' . $nombreMaquina .'. Queríamos informarte que hemos completado la valoración de la devolución del equipo. Hemos calificado tu alquiler con: ' . $estrellas .' estrellas y nuestro equipo quiere hacerte llegar el siguiente comentario' . $comentario . '<p>');

        $sent = $this->mailer->send($email);
        # Se almacena en una variable porque puede usarse para debugging, no es necesario 👍 
    }
}