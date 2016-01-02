<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/jelly/php/libraries/sendgrid-php/sendgrid-php.php');

// $Error = sendGridEmail(
// 	'subject test', 
// 	'body test',  
// 	array(
// 		'Email_Address'=> 'info@better.space', 
// 		'Name'=>'Better Space'
// 	), 
// 	array(
// 		array(
// 			'Email_Address'=>'kunal@better.space', 
// 			'Name'=>'Kunal'
// 		), 
// 		array(
// 			'Email_Address'=>'m@codecloud.me',
// 			'Name'=>'Mike'
// 		)
// 	), 
// 	array('Email_Address'=> 'm@codecloud.me'), 
// 	array('Email_Address'=> 'maskedv141@gmail.com')
// );
// print_r($Error);
//echo('{"status":"success", "msg":"message sent"}');


function sendGridEmail($subject, $body, $from, $to, $replyTo, $bcc) {
	// Initialize SendGrid 
	$sendgrid = new SendGrid('m141v', 'popcorn1');
	$email = new SendGrid\Email();

	// Handle to addresses... 
	// Convert strings to array
	// TODO - validate that it was set?
	for($i=0; $i<count($to); $i++){
		if (!filter_var($to[$i]['Email_Address'], FILTER_VALIDATE_EMAIL) === false){
			if ($to[$i]['Name'])
				$email->addTo($to[$i]['Email_Address'], $to[$i]['Name']);	
			else
				$email->addTo($to[$i]['Email_Address']);
		}
		else {
		   return array("status"=>"fail", "msg"=>"please send a valid email as param to");
		}
	}

	// Handle from address...
	if(!isset($from) || !isset($from['Email_Address'])){ 
	   return array("status"=>"fail", "msg"=>"please send a from email as param from");
	}
	if (!filter_var($from['Email_Address'], FILTER_VALIDATE_EMAIL) !== false) {
		return array("status"=>"fail", "msg"=>"please send a valid email as param from");
	}
	$email->setFrom($from['Email_Address']);
	if (isset($from['Name']))
		$email->setFromName($from['Name']);

	//Handle Reply To
	if(isset($replyTo)){
		// TODO - validate?
		$email->setReplyTo($replyTo['Email_Address']);
	}

	// Handle BCC
	if(isset($bcc)){
		$email->setBcc($bcc['Email_Address']);
	}
	
	//Handle subject
	if(!isset($subject)){
	   return array("status"=>"fail", "msg"=>"please send a subject as param subject");
	}
	$email->setSubject($subject);

	//Handle body
	if(!isset($body)){
	   return array("status"=>"fail", "msg"=>"please send a body as param body");
	}
	$email->setText($body);
	$email->setHtml($body);

	// Send mail
	$sendgrid->send($email);

	// Return success
	return(array("status"=>"success", "msg"=>"message sent"));
}
?>