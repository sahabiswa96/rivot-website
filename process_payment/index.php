<?php require_once('./src/resources/Config.php') ; ?>

<html>
    <head>
        <link rel="stylesheet" href="./src/com/zaakpay/css/style.css">
        <title>Zaakpay PHP Integration Kit</title>
    </head> 
  
    <body>

   		<br> <br> <img src="./src/resources/img/Logo.png" alt="logo" /> <br> <br>

   	<div>

         <table class="customtable-all card" style="margin: 10px 10px 10px 20px;float: left;" border="1">
            <tr>
                <th colspan="2" width="120px" style="text-align:center">CURRENT CONFIGURATIONS</th>
            </tr>
            <tr>
               <th> Api URL </th>
               <td> <?php echo $environment ; ?> </td>
            </tr>
            <tr>
               <th> Payment URL </th>
               <td> <?php echo $environment.$transactApi ; ?> </td>
            </tr>
            <tr>
               <th> Transaction Status Check URL </th>
               <td> <?php echo $environment.$checkStatusApi ; ?> </td>
            </tr>
            <tr>
               <th> Return URL <br> Response file path </th>
               <td> <?php echo $returnUrl ; ?> </td>
            </tr>
            <tr>
               <th> Refund URL </th>
               <td> <?php echo $environment.$updateApi ; ?> </td>
            </tr>
         </table>

   		<table class="customtable-all card" style="margin: 10px 100px 10px 10px;float: right;" border="1">
   			<tr>
   				<th> <br> <br> Process Payment <br> <br> </th>
   				<td>
   					
   					<br>
   					This will initiate the payment by submitting the mandatory and optional parameters to Zaakpay which will proceed you further to the payment page.
   					<br>

   				</td>
   			</tr>
   			<tr>
   				<th colspan="2">
   					<br><div align="center">
   						<a href="./src/com/zaakpay/api/Payment.php"><button>Initiate Payment</button></a>
   					</div><br>
   				</th>
   			</tr>
   		</table>
   		<table class="customtable-all card" style=" margin: 10px 100px 10px 100px; float: right" border="1">
   			<tr>
   				<th> <br> <br> Refund Transaction <br> <br> </th>
   				<td>
   		
   					<br>
   					This will allow you to refund ( full or partial ) the successful transaction.
   					<br>

   				</td>
   			</tr>
   			<tr>
   				<th colspan="2">
   					<br><div align="center">
   						<a href="./src/com/zaakpay/api/Refund.php"><button>Refund Transaction</button></a>
   					</div><br>
   				</th>
   			</tr>
   		</table>

   		<table class="customtable-all card" style=" margin: 10px 100px 10px 100px; float: right" border="1">
   			<tr>
   				<th> <br> <br> Transaction Status <br> <br> </th>
   				<td>
   		
   					<br>
   					This will check the transaction status against a particular Order Id.
   					<br>

   				</td>
   			</tr>
   			<tr>
   				<th colspan="2">
   					<br><div align="center">
   						<a href="./src/com/zaakpay/api/TransactionStatus.php"><button>Transaction Status</button></a>
   					</div><br>
   				</th>
   			</tr>
   		</table>
    </body>
</html>
