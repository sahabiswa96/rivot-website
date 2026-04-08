<?php require_once('./../lib/Checksum.php'); ?>
<?php require_once('./../../../resources/Config.php'); ?>  


<?php
	
   	$checksumFlag = $environment != "https://api.zaakpay.com" ; //This should not be changed

	$recd_checksum = $_POST['checksum'];
	$all = Checksum::getAllResponseParams();
	$checksum_check = Checksum::verifyChecksum($recd_checksum, $all, $secretKey); //This is used to validate the received checksum
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="./../css/style.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Zaakpay Response</title>
</head>
<body>

<center><br>
<table width="500px;" class="customtable-all card">
	<?php Checksum::outputResponse($checksum_check);
	?>
<?php if ($checksumFlag) { ?>
	<tr>
		<th width="50%" align="center" valign="middle">Response Checksum String</td></tr>
		<td width="50%" align="center" valign="middle"><font color=Blue><?php echo $all; ?></font></td></tr>
	</tr>
<?php } ?>
</table>


</center>



</body>
</html>
