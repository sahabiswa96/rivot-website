<!-- Below code is used to show the checksum calculating parameters and checksum string -->
<!-- =========================================================================================================================== -->
<?php
	if(array_key_exists('orderId', $_POST) and !(array_key_exists('checksum', $_POST))) {
		require_once('./../lib/Checksum.php');   
	   	require_once('./../../../resources/Config.php');  

	   	$checksumFlag = $environment != "https://api.zaakpay.com" ; //This should not be changed

		$data = "{merchantIdentifier:".$_POST['merchantIdentifier'].",mode:0,orderDetail:{orderId:".$_POST['orderId']."}}" ;
		$checksum = Checksum::calculateChecksum($secretKey, $data);
?>

<?php if ($checksumFlag) { ?>

<html>
<head>
	<link rel="stylesheet" href="./../css/style.css">
   	<title>Request Parameters</title>
</head>
<body>
	<div align="center">
		<form action="<?php echo $environment.$checkStatusApi ; ?>" method="post">
			<table width="650px;" class="customtable-all card" border="1">
				<tr>
					<td colspan="2" align="center" valign="middle">REQUEST PARAMETERS</td>	
				</tr>
				<tr>	
					<td width="50%" align="center" valign="middle">Order Id</td>
					<td width="50%" align="center" valign="middle"><?php echo $_POST['orderId'] ?></td> 
				</tr>
				<tr>
					<th>Checksum String</th>
					<td><?php echo $data ?></td>
				</tr>
				<tr>
					<th>Calculated Checksum</th>
					<td><?php echo $checksum ?></td>
				</tr>
				<tr>
					<div>
						<th colspan="2" align="left"><br><input type="submit" value="Check Status"><br><br></th>
					</div>
				</tr>
			</table>
			<input type="hidden" name="data" value="<?php echo $data ; ?>" />
    		<input type="hidden" name="checksum" value="<?php echo $checksum ; ?>"/>
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
			<form action="<?php echo $environment.$checkStatusApi ; ?>" method="post">
				<input type="hidden" name="data" value="<?php echo $data ; ?>" />
    			<input type="hidden" name="checksum" value="<?php echo $checksum ; ?>"/>
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

<?php if(!array_key_exists('orderId', $_POST)) { 
   	require_once('./../../../resources/Config.php');  

	?>
<!DOCTYPE html>
<html>
<head>
	<title>Check Transaction Status</title>
	<link rel="stylesheet" href="./../css/style.css">
</head>
<body>
	<div align="center"><br><br>
		<form action="TransactionStatus.php" method="post">
			<table width="650px;" class="customtable-all card" border="1">
				<tr>
					<td colspan="2" align="center" valign="middle">This Page behaves like testing input for status check api </td>	
				</tr>
				<tr>	
					<td width="50%" align="center" valign="middle">Merchant Identifier</td>
					<td width="50%" align="center" valign="middle"><input type="text" name="merchantIdentifier" value="<?php echo $merchantIdentifier ?>" /></td> 
				</tr>
				<tr>	
					<td width="50%" align="center" valign="middle">Order Id</td>
					<td width="50%" align="center" valign="middle"><input type="text" name="orderId" placeholder="Enter orderId" /> </td>
				</tr>
				<tr>
					<th align="center" colspan="2" style="text-align: center;"><br><input type="submit" value="Check Status"><br><br></th>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
<?php } ?>

