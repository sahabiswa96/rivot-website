<?php

Class Checksum {
	static function calculateChecksum($secret_key, $all) {
		$hash = hash_hmac('sha256', $all , $secret_key);
		$checksum = $hash;
		return $checksum;
	}
	
	static function getAllParams() {
		$all = '';
		
		
		//Compatible till version 8
		$checksumsequence= array("amount","bankid","buyerAddress",
				"buyerCity","buyerCountry","buyerEmail","buyerFirstName","buyerLastName","buyerPhoneNumber","buyerPincode",
				"buyerState","currency","debitorcredit","merchantIdentifier","merchantIpAddress","mode","orderId",
				"product1Description","product2Description","product3Description","product4Description",
				"productDescription","productInfo","purpose","returnUrl","shipToAddress","shipToCity","shipToCountry",
				"shipToFirstname","shipToLastname","shipToPhoneNumber","shipToPincode","shipToState","showMobile","txnDate",
				"txnType","zpPayOption");
		
		
		foreach($checksumsequence as $seqvalue)	{
			if(array_key_exists($seqvalue, $_POST))	{
				if(!$_POST[$seqvalue]=="")
				{
					if($seqvalue != 'checksum') 
					{
						$all .= $seqvalue;
						$all .="=";
						$all .= $_POST[$seqvalue];
						$all .= "&";
						}
				}
				
			}
		}
		
		
		
		return $all;
	}
	static function getAllParamsCheckandUpdate() {
		//ksort($_POST);
		$all = '';
		foreach($_POST as $key => $value)	{
			if($key != 'checksum') {
				$all .= "'";
				$all .= $value;
				$all .= "'";
			}
		}
		
		return $all;
	}
	static function outputForm($checksum) {
		//ksort($_POST);
		foreach($_POST as $key => $value) {
			echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		}
		echo '<input type="hidden" name="checksum" value="'.$checksum.'" />'."\n";
	}
	
	static function verifyChecksum($checksum, $all, $secret) {
		$cal_checksum = Checksum::calculateChecksum($secret, $all);
		$bool = 0;
		if($checksum == $cal_checksum)	{
			$bool = 1;
		}
		
		return $bool;
	}
	
	static function outputResponse($bool) {
		foreach($_POST as $key => $value) {
			if ($bool == 0) {
				if ($key == "responseCode") {
					echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td>
						<td width="50%" align="center" valign="middle"><font color=Red>***</font></td></tr>';
				} else if ($key == "responseDescription") {
					echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td>
						<td width="50%" align="center" valign="middle"><font color=Red>This response is compromised.</font></td></tr>';
				} else {
					echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td>
						<td width="50%" align="center" valign="middle">'.$value.'</td></tr>';
				}
			} else {
				echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td>
					<td width="50%" align="center" valign="middle">'.$value.'</td></tr>';
			}
		}
		echo '<tr><td width="50%" align="center" valign="middle">Checksum Verified?</td>';
		if($bool == 1) {
			echo '<td width="50%" align="center" valign="middle">Yes</td></tr>';
		}
		else {
			echo '<td width="50%" align="center" valign="middle"><font color=Red>No</font></td></tr>';
		}
	}
	static function getAllResponseParams() {
		//ksort($_POST);
		$all = '';
		$checksumsequence= array("amount","bank","bankid","cardId",
        "cardScheme","cardToken","cardhashid","doRedirect","orderId",
        "paymentMethod","paymentMode","responseCode","responseDescription",
        "productDescription","product1Description","product2Description",
        "product3Description","product4Description","pgTransId","pgTransTime");
		foreach($checksumsequence as $seqvalue)	{
			if(array_key_exists($seqvalue, $_POST))	{
				
				$all .= $seqvalue;
				$all .="=";
				$all .= $_POST[$seqvalue];
				$all .= "&";
				
				
				
			}
		}
		
		
		return $all;
	}
}
?>
