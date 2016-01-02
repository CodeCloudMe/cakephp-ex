<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/jelly/php/libraries/sendgrid-php/sendgrid-php.php');


function sendGridEmail($to, $from, $subject, $body, $fromName, $replyTo, $bcc){



$sendgrid = new SendGrid('m141v', 'popcorn1');


$email = new SendGrid\Email();

//check if we have several emails as array or just a string
if(is_array($to)){
    for($i=0; $i<count($to); $i++){
        $email->addTo($to[$i]);

    }
}

else{

    if (!filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
        $email->addTo($to);
    } 
    else {
       return array("status"=>"fail", "msg"=>"please send a valid email as param to");
    }

}



if(!isset($from)){
   
   return array("status"=>"fail", "msg"=>"please send a from email as param from");
}


if (!filter_var($from, FILTER_VALIDATE_EMAIL) !== false) {
    return array("status"=>"fail", "msg"=>"please send a valid email as param from");
  
} 


if(!isset($subject)){
   
   return array("status"=>"fail", "msg"=>"please send a subject as param subject");
}
if(!isset($body)){
   
   return array("status"=>"fail", "msg"=>"please send a body as param body");
}




$email->setFrom($from);
$email->setSubject($subject);
$email->setText($body);
$email->setHtml($body);

if(isset($replyTo)){
    $email->setReplyTo($replyTo);
}

if(isset($bcc)){
    $email->setBcc($bcc);
}


if(isset($fromName)){
    $email->setFromName($fromName);
}

$sendgrid->send($email);
  



}



sendGridEmail(array('kunal@better.space', 'm@codecloud.me'), 'info@better.space', 'Hello', 'testing', 'Better Space', 'm@codecloud.me', 'maskedv141@gmail.com');

//echo('{"status":"success", "msg":"message sent"}');


?>