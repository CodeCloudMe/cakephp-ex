<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/jelly/php/libraries/sendgrid-php/sendgrid-php.php');


function sendGridEmail($to, $from, $subject, $body, $replyTo, $bcc){



$sendgrid = new SendGrid('m141v', 'popcorn1');


$email = new SendGrid\Email();

for($i=0; $i<count($to); $i++){
	$email->addTo($to[$i]);

}

$email->setFrom($from);
$email->setSubject($subject);
$email->setText($body);
$email->setHtml($body);
$email->setReplyTo($replyTo);
$email->setBcc($bcc);

$sendgrid->send($email);
  


/*

$url = 'https://api.sendgrid.com/';
$user = getenv('sendGridUsername');
$pass = getenv('sendGridPassword');

$params = array(
    'api_user'  => $user,
    'api_key'   => $pass,
    'to'        => $to,
    'subject'   => $subject,
    'html'      => $body,
    'text'      => $body,
    'from'      => $from,
    'replyTo' => 'm@codecloud.me',
    'bcc'=> 'maskedv141@gmail.com'
  );


$request =  $url.'api/mail.send.json';

// Generate curl request
$session = curl_init($request);
// Tell curl to use HTTP POST
curl_setopt ($session, CURLOPT_POST, true);
// Tell curl that this is the body of the POST
curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
// Tell curl not to return headers, but do return the response
curl_setopt($session, CURLOPT_HEADER, false);
// Tell PHP not to use SSLv3 (instead opting for TLS)
curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// obtain response
$response = curl_exec($session);
curl_close($session);

// print everything out
print_r($response);	

*/


}



//sendGridEmail(array('kunal@better.space', 'm@codecloud.me'), 'info@better.space', 'Hello', 'testing', 'm@codecloud.me', 'maskedv141@gmail.com');

//echo('{"status":"success", "msg":"message sent"}');


?>