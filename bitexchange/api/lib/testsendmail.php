<?php
require_once ('/phpmailer/class.phpmailer.php');
require_once ('/phpmailer/class.smtp.php'); // optional, gets called from within class.phpmailer.php if not already loaded
$hodemail = "MichaelThompson1981@outlook.com";
echo '1';
$mail = new PHPMailer(); // create a new object
$mail->IsSMTP(); // enable SMTP
echo '2';
$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
if(false) {

	$mail->SMTPOptions = array(
			'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
			)
	);
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = "ssl://smtp.gmail.com";
	$mail->Port = 465; // or 587
}
else {
	//$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = "192.168.0.13";
	$mail->Port = 25;
}
$mail->IsHTML(true);
$mail->Username = "kaankoca97@gmail.com";
$mail->Password = "test1234!";
$mail->SetFrom("kaankoca97@gmail.com");
$mail->Subject = "Student Feedback";
$mail->Body = "hello, Here's the graph generated";
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
$mail->AddAddress($hodemail);
if(!$mail->Send())
{
	$error = $mail->ErrorInfo;
	echo $error;
}
else
{
	function redirect($url)
	{
		$string = '<script type="text/javascript">';
		$string .= 'window.location = "' . $url . '"';
		$string .= '</script>';
		echo $string;
	}
	redirect('sendmail.php?msg=Email Successfully sent to corresponding HOD!');
}

?>
