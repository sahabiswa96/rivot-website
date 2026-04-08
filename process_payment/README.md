# Zaakpay PHP Integration Kit

 1. Copy Zaakpay_PHP_Integration_Kit folder in the root document of your server (for example /var/www/html)

 2. Open Config.php file ( Zaakpay_PHP_Integration_Kit/src/resources/Config.php ) and verify the below values.
	- environment ( https://zaakstaging.zaakpay.com ( Staging ) or https://api.zaakpay.com ( Production ) )
	- returnUrl ( Callback URL on which Zaakpay will send the response )

 3. API folder is having following files:
    - Payment.php – Process transaction through Zaakpay Payment Gateway.
    - Response.php – This is the sample callback page ( You can replace this with your final page ).
    - TransactionStatus.php – Check actual status of a transaction. 
    - Refund.php – Refund ( Full or Partial ) a successful transaction.

 4. Refer [ Zaakpay PHP Integration Document ] ( https://developer.zaakpay.com/docs/payment-gateway-integration-in-php ) for any queries.

 
