<?php

	$merchantIdentifier = "37b85ce561df4c74b8547d541f198391"; //Get your merchant identifier on Zaakpay.com

	$secretKey = "d38f1436894f4d4f9d423eeebec5a38f" ; //Get your secret key on Zaakpay.com

	//Api URL
	$environment = "https://api.zaakpay.com" ; //For Live transaction use https://api.zaakpay.com

	//Payment processing URL's
	$transactApi = "/api/paymentTransact/V8" ;

	//Transaction update URL's
	$updateApi = "/updatetransaction" ;

	//Transaction check status URL's
	$checkStatusApi = "/checkTxn?v=5" ;

	//Url for test response file
	$returnUrl = "https://website.rivotmotors.com/process_payment/src/com/zaakpay/api/Response.php" ; //Change this with your response file
	//$returnUrl ="https://website.rivotmotors.com/thankyou.html";
?>
