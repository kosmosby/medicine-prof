<?php
$to = "lapytko.spb@mail.ru";
$subject = "Test mail";
$message = "Hello! This is a simple email message.";
$from = "lapytko-yura@ya.ru";
$headers = "From:" . $from;
echo mail($to,$subject,$message,$headers)."<hr>";
echo "Mail Sent.";
?> 