<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once('vendor/autoload.php');

    (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('Must run via CLI');

    if(isset($argc)) {

    }
    else {
        die("argc is disabled\n");
    }

    $smtp = [

    ];

     //Set basic details
     $mail = new PHPMailer();
     $mail->isHTML(true);
     $mail->Subject = $subject;
     $mail->Body = $template;