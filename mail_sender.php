<?php

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/vendor/autoload.php';
require_once 'config.php';
session_start();

if (isset($_POST['mail'])) {
    echo sendMail();
}

function checkValidation(): string
{
    $email = filter_input(INPUT_POST, "mail", FILTER_VALIDATE_EMAIL);
    $subject = filter_input(INPUT_POST, "subject", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $text = strip_tags(filter_input(INPUT_POST, "text", FILTER_SANITIZE_ADD_SLASHES), '<p><a><b><div>');

    if (empty($email)) {
        return "Отсутствует или неверен адрес почты.";
    } elseif (empty($subject)) {
        return "тема не указана";
    } elseif (empty($text)) {
        return "Отсутствует сообщение.";
    }

    return "";
}


function sendMail(): string
{
    $config = getConfig();
    $error = checkValidation();
    if ($error != "") {
        return $error;
    }
    // Initialize PHP Mailer and set SMTP as mailing protocol:
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";
//Set required parameters for making an SMTP connection like server, port and account credentials.
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port = $config['mail_port'];
    $mail->Host = $config['mail_host'];
    $mail->Username = $config['mail_user'];
    $mail->Password = $config['mail_pass'];
    //Set the required parameters for email header and body:
    $mail->IsHTML(true);
    $mail->AddAddress($_POST["mail"]);
    $mail->SetFrom($config['mail_user'], "Admin");
    $mail->Subject = $_POST["subject"];
    $content = $_POST["text"];

    //Delete the directory, related to certain Session ID:
    $sessionID = session_id();
    $path = "upload/$sessionID";
    //Send the email and catch required exceptions:
    $files = array_values(array_filter(scandir($path), function ($file) use ($path) {
        return !is_dir($path . '/' . $file);
    }));

    foreach ($files as $file) {
        $mail->AddAttachment($path . "/" . $file);
    }

    $mail->MsgHTML($content);
    if (!$mail->Send()) {
        return "Error while sending Email.";
    }
    //Delete the files at the default directory:
    foreach ($files as $file) {
        unlink($path . "/" . $file);
    }
    //Delete the directory, related to certain Session ID:
    rmdir("upload/$sessionID/");

    return "Email sent successfully";
}
