<!-- Below code is used to show the checksum calculating parameters and checksum string -->
<!-- =========================================================================================================================== -->
<?php
   if(array_key_exists('orderId', $_POST) and !(array_key_exists('checksum', $_POST))) {
      require_once('./../lib/Checksum.php');
         require_once('./../../../resources/Config.php');  
   

      $checksumFlag = $environment != "https://api.zaakpay.com" ; //This should not be changed

      $all = Checksum::getAllParamsCheckandUpdate();
      $checksum = Checksum::calculateChecksum($secretKey, $all);
?>

<?php if ($checksumFlag) { ?>


<html>
<head>
   <link rel="stylesheet" href="./../css/style.css">
      <title>Request Parameters</title>
</head>
<body>
   <div align="center">
      <form action="<?php echo $environment.$updateApi ; ?>" method="post">
         <table width="650px;" class="customtable-all card" border="1">
            <tr>
               <td colspan="2" align="center" valign="middle">REQUEST PARAMETERS</td>  
            </tr>
            <tr>  
               <td width="50%" align="center" valign="middle">Order Id</td>
               <td width="50%" align="center" valign="middle"><?php echo $_POST['orderId'] ?></td> 
            </tr>
            <tr>
               <th>Checksum String <br> ( Raw Request )</th>
               <td><?php echo $all ?></td>
            </tr>
            <tr>
               <th>Calculated Checksum</th>
               <td><?php echo $checksum ?></td>
            </tr>
            <tr>
               <div>
                  <th colspan="2" align="left"><br><input type="submit" value="Refund"><br><br></th>
               </div>
            </tr>
         </table>
         <?php Checksum::outputForm($checksum); ?>
      </form>
   </div>
</body>
</html>
<?php } ?>

<?php if (!$checksumFlag) { ?>

<center>
<table width="500px;">
  <tr>
    <td align="center" valign="middle">Do Not Refresh or Press Back <br/> Redirecting to Zaakpay</td>
  </tr>
  <tr>
    <td align="center" valign="middle">
      <form action="<?php echo $environment.$updateApi ; ?>" method="post">
        <?php
        Checksum::outputForm($checksum);
        ?>
      </form>
    </td>
  </tr>
</table>
</center>
<script type="text/javascript">
var form = document.forms[0];
form.submit();
</script>

<?php } ?>


<?php } ?>


<!-- Below code is used to enter the sample Input -->
<!-- =========================================================================================================================== -->
<?php require_once('./../../../resources/Config.php'); ?>  
<?php if(!array_key_exists('orderId', $_POST)) { ?>
<html>
<head>
	<link rel="stylesheet" href="./../css/style.css">
   	<title>Refund Transaction</title>
</head>
<body>
<br><br>
<div align="center">
	<form action="Refund.php" method="post">
		<table class="customtable-all card" border="1">
   			<tr>
   				<th> <br> Full Refund <br><br></th>
   				<td>
   					<br>
   					This will initiate full refund for the transaction .
   					<br><br>
   				</td>
   			</tr>
   			<tr>
   				<th colspan="2">
   					<br><div align="center">
                     <pre>Merchant Identifier : <input type="text" name="merchantIdentifier" value="<?php echo $merchantIdentifier ?>" ></pre>
                     <input type="text" name="orderId" placeholder="Enter orderId">
                        <input type="hidden" name="mode" value="0" >
                        <input type="hidden" name="updateDesired" value="14" >
                        <input type="hidden" name="updateReason" value="Test reason">
                     <br><br>
                     <button>Initiate Full Refund</button>
   					</div><br>
   				</th>
   			</tr>
   		</table>
</form>
<br><br>

<form action="Refund.php" method="post">
  		<table class="customtable-all card" border="1">
  			<tr>
  				<th> <br> Partial Refund<br><br> </th>
  				<td>
  					<br>
  					This will initiate partial refund for the transaction .
  					<br><br>
  				</td>
  			</tr>
  			<tr>
  				<th colspan="2">
  					<br><div align="center">
                  <pre>Merchant Identifier : <input type="text" name="merchantIdentifier" value="<?php echo $merchantIdentifier ?>" ></pre>
                  <input type="text" name="orderId" placeholder="Enter orderId">
                        <input type="hidden" name="mode" value="0" >
                        <input type="hidden" name="updateDesired" value="22" >
                        <input type="hidden" name="updateReason" value="Test reason">
  	   				<input type="text" name="amount" placeholder="Enter Amount ( Paisa )">
                  <br><br>
                  <button>Initiate Partial Refund</button>
   				</div><br>
   			</th>
   		</tr>
   	</table>
</form>
</div>
<br><br><br><br><br>
</body>
</html>
<?php } ?>`