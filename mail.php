<?php
class Email{
    public $to;
    public $from;
    public $subject;
    public $message;

    public function __construct(){
        $this->from = "jeunice.shakimwa@gmail.com";
    }
    
    //send email
    public function sendEmail(){
        $message = "
            <html>
            <head>
            <title>Software Team Manager</title>
            </head>
            <body>
            <h1>{$this->subject}</h1>
            <div>{$this->message}</div>
            </body>
            </html>
        ";
        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <' .$this->from .'>' . "\r\n";

        return mail($this->to,$this->subject,$message,$headers);
    }
} 

?>