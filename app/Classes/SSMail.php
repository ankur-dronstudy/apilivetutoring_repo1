<?php

namespace App\Classes;

//require 'PHPMailerAutoload.php';
use PHPMailer;

class SSMail {

    private $mail;

    public function __construct() {
        $creds = \Config::get('mail');

        $this->mail = new PHPMailer;

        $this->mail->isSMTP();                                      // Set mailer to use SMTP
        $this->mail->Host = $creds['host'];  // Specify main and backup SMTP servers
        $this->mail->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mail->Username = $creds['username'];                 // SMTP username
        $this->mail->Password = $creds['password'];                           // SMTP password
        $this->mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $this->mail->Port = 587;
    }

    public function __destruct() {
        unset($this->mail);
    }

    public function send( $to, $cc, $bcc, $subject, $body) {
        $this->mail->setFrom("preparation@scholarspace.org", "KV-ADMISSION");
        foreach( $to as $r ) {
            $this->mail->addAddress($r);     // Add a recipient
        }
        foreach( $cc as $r ) {
            $this->mail->addCC($r);
        }
        foreach( $bcc as $r ) {
            $this->mail->addBCC('');
        }

        //$this->mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$this->mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $this->mail->isHTML(true);                                  // Set email format to HTML

        $this->mail->Subject = $subject;
        $this->mail->Body = $body;
        $this->mail->AltBody = $body;
        
        if (!$this->mail->send()) {
            return false;
        }else{
            return true;
        } 
    }

}