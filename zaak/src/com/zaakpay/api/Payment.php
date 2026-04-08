<!-- Below code is used to show the checksum calculating parameters and checksum string -->
<!-- =========================================================================================================================== -->

<?php

	if(array_key_exists('orderId', $_POST) and !(array_key_exists('checksum', $_POST))) {
    	require_once('./../lib/Checksum.php');
	   	require_once('./../../../resources/Config.php');

	   	$checksumFlag = $environment != "https://api.zaakpay.com" ; //This should not be changed

		$all = Checksum::getAllParams();
		$checksum = Checksum::calculateChecksum($secretKey, $all); 	 //This is used to generate checksum
 ?>

<?php if ($checksumFlag) { ?>

<html>
<head>
	<link rel="stylesheet" href="./../css/style.css">
   	<title>Request Parameters</title>
</head>
<body>
	<div align="center">
		<form action="<?php echo $environment.$transactApi ; ?>" method="post">
			<table width="650px;" class="customtable-all card" >
				<tr>
					<th colspan="2">REQUEST PARAMETERS</th>
				</tr>
				<tr>
					<th>Order Id</th>
					<td><?php echo $_POST['orderId'] ?></td>
				</tr>
				<tr>
					<th>Checksum String</th>
					<td><?php echo htmlentities($all) ?></td>
				</tr>
				<tr>
					<th>Calculated Checksum</th>
					<td><?php echo $checksum ?></td>
				</tr>
				<tr>
					<div>
						<th colspan="2" align="left"><br><input type="submit" value="Process Payment"><br><br></th>
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
			<form action="<?php echo $environment.$transactApi ; ?>" method="post">
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

<?php if(!array_key_exists('orderId', $_POST)) {
   	require_once('./../../../resources/Config.php');
 ?>
<html>
<head>
	<link rel="stylesheet" href="./../css/style.css">
   	<title>Sample Input</title>
   	<script type="text/javascript">

	function autoPop(){
		document.getElementById("orderId").value= "ZPLive" + String(new Date().getTime());	//	Autopopulating orderId
		var today = new Date();
		var dateString = String(today.getFullYear()).concat("-").concat(String(today.getMonth()+1)).concat("-").concat(String(today.getDate()));
		document.getElementById("txnDate").value= dateString;
	};
 function submitForm(){
	 var form = document.forms[0];
	 var amt = document.getElementById("amount").value;
	 amt = amt.replace(new RegExp(",", 'g'),"");
	 var hiddenAmt = document.getElementById("amount");
	 hiddenAmt.value=parseInt(amt);
	 form.action = "Payment.php";
	 form.submit();
 };
   	</script>
</head>

<body onload="autoPop()">

<div align="center">
<form action="Payment.php" method="post">
<h2>Pay Now to see how Zaakpay will work on your website.</h2>
<p>Note: This page behaves like a shopping cart or checkout page on a website.</p>
<table width="650px;" class="customtable-all card">
<tr>
	<th colspan="2" >MANDATORY PARAMETERS</th>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Merchant Identifier</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="merchantIdentifier" value="<?php echo $merchantIdentifier ; ?>" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Order Id</td>
	<td width="50%" align="center" valign="middle"><input type="text" id="orderId" name="orderId" /></td>
</tr>
<tr>
	  <input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>"/>
	  <input type="hidden" name="currency" value="INR" />
<<!-- </tr>
<tr>
	<td width="50%" align="right" valign="middle">Amount In Paisa</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="amount" value="100" placeholder="In Paisa" /> </td>
</tr>-->

<tr>
	<td width="50%" align="right" valign="middle">Amount</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="amount" id="amount" value="499" placeholder="In Rupees" /> </td>
</tr>

<tr>
	<td width="50%" align="right" valign="middle">Buyer Email</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerEmail" value=""  /> </td>
</tr>
<!--<tr>
	<th align="center" colspan="2" style="text-align: center;"><br><input type="submit" value="Pay Now"><br><br></th>
</tr>-->

<tr>
	<th align="center" colspan="2" style="text-align: center;"><br><input onclick="javascript:submitForm()" type="submit" value="Pay Now"><br><br></th>
</tr>

</table>
<br><br>

<!-- ========================== OPTIONAL PARAMETERS ================================ --->

<table  width="650px;" class="customtable-all card">
<tr>
	<th colspan="2" >OPTIONAL PARAMETERS</th>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer First Name</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerFirstName" value="" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer Last Name</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerLastName" value="" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer Address</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerAddress" value="" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer City</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerCity" value="" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer State</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerState" value="" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer Country</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerCountry" value="" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer Pincode</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerPincode" value="" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Buyer Phone No</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="buyerPhoneNumber" value="" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Product Description</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="productDescription" /> </td>
</tr>
</table>

<!-- Not mandatory  -->
<!-- <tr>
	<td width="50%" align="right" valign="middle">Product1 Description</td>
	<td width="50%" align="center" valign="middle"><input type="hidden" name="product1Description" /></td>
</tr> -->
<!-- <tr>
	<td width="50%" align="right" valign="middle">IPaddress</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="merchantIpAddress" /> </td>
</tr> -->
<!-- <tr>
	<td width="50%" align="right" valign="middle">Purpose</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="purpose" /></td>
</tr> -->
<!-- <tr>
	<td width="50%" align="right" valign="middle">Txntype</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="txnType" value="1" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Zppayoption</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="zpPayOption" value="1" /></td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Mode</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="mode" value="1" /> </td>
</tr>
<tr>
	<td width="50%" align="right" valign="middle">Currency</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="currency" value="INR" /></td>
</tr>
 -->
<!--<tr>
	<td width="50%" align="right" valign="middle">Product2 Description</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="product2Description" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Product3 Description</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="product3Description" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Product4 Description</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="product4Description" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To Address</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToAddress" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To City</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToCity" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To State</td>
	<td width="50%" align="center" valign="middle"></td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToState" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To Country</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToCountry" /> -->

<!--<tr>
	<td width="50%" align="right" valign="midb6415a6443604ec59644a70c8b25a0f6dle">Ship To Pincode</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToPincode" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To Phone Number</td>
	<td width="50%" align="center" valign="middle"> </td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToPhoneNumber" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To Firstname</td>
	<td width="50%" align="center" valign="middle"></td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToFirstname" /> -->

<!--<tr>
	<td width="50%" align="right" valign="middle">Ship To Lastname</td>
	<td width="50%" align="center" valign="middle"></td>
</tr>-->
<!-- Not mandatory <input type="hidden" name="shipToLastname" /> -->
<!--
<tr>
	<td width="50%" align="right" valign="middle">Transaction Date "YYYY-MM-DD"</td>
	<td width="50%" align="center" valign="middle"><input type="text" name="txnDate" id="txnDate" /></td>
</tr> -->
</form>
</div>
<br><br><br><br><br>
</body>
</html>
<?php } ?>

<!-- =========================================================================================================================== -->
