<?php

	$merchantIdentifier = "a8f315692867403c8b31c8c6002e9173"; //Get your merchant identifier on Zaakpay.com

	$secretKey = "685352c084234eeca0402f3cf8865a67" ; //Get your secret key on Zaakpay.com

	//Api URL
	$environment = "https://api.zaakpay.com" ; //For Live transaction use https://api.zaakpay.com

	//Payment processing URL's
	$transactApi = "/api/paymentTransact/V8" ;

	//Transaction update URL's
	$updateApi = "/updatetransaction" ;

	//Transaction check status URL's
	$checkStatusApi = "/checkTxn?v=5" ;

	//Url for test response file
	$returnUrl = "https://rivotmotors.com/process_payment/src/com/zaakpay/api/Response.php" ; //Change this with your response file
?>
