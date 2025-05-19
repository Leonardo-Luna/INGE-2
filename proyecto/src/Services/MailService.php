<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService {

    public function __construct() { }

    public function EnviarPassword($password, $to) {
        //Create a new PHPMailer instance
        $mail = new PHPMailer();

        //Tell PHPMailer to use SMTP
        $mail->isSMTP();

        //Enable SMTP debugging
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;

        //Set the hostname of the mail server
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        //Use `$mail->Host = gethostbyname('smtp.gmail.com');`
        //if your network does not support SMTP over IPv6,
        //though this may cause issues with TLS

        //Set the SMTP port number:
        // - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
        // - 587 for SMTP+STARTTLS
        $mail->Port = 465;

        //Set the encryption mechanism to use:
        // - SMTPS (implicit TLS on port 465) or
        // - STARTTLS (explicit TLS on port 587)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = '804591b0a3c6fc';

        //Password to use for SMTP authentication
        $mail->Password = '06674da078b6ab';

        //Set who the message is to be sent from
        //Note that with gmail you can only use your account address (same as `Username`)
        //or predefined aliases that you have configured within your account.
        //Do not use user-submitted addresses in here
        $mail->setFrom('IVTech.UNLP@gmail.com', 'First Last');

        //Set who the message is to be sent to
        $mail->addAddress('lunaleonardo031@gmail.com', 'John Doe');

        //Set the subject line
        $mail->Subject = 'Bienvenido a Alquil.AR';


        //Replace the plain text body with one created manually
        $cambioPassword = "AcáVaLaURLParaCambiarDeContraseña.com";
        $mail->AltBody = 'Tu contraseña para iniciar sesión es ' . $password . '. Puedes cambiarla en cualquier momento visitando ' . $cambioPassword;

        //send the message, check for errors
        $mail->send();
    }
}