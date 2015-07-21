<?php

class Mail {

    static function Send($to, $name, $subject, $body) {
        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'mail.soccerwars.xyz';
        $mail->Port = 25;
        $mail->Username = 'admin@soccerwars.xyz';
        $mail->Password = '21322196';
        $mail->SMTPSecure = 'tls';
        $mail->CharSet = 'UTF-8';

        $mail->From = 'admin@soccerwars.xyz';
        $mail->FromName = 'Soccer Wars';
        $mail->addAddress($to, $name);
        $mail->addReplyTo('admin@soccerwars.xyz', 'Administrator');

        $mail->Subject = $subject;
        $mail->Body = $body;

        if ($mail->send())
            return true;
        else {
            return false;
        }
    }

}