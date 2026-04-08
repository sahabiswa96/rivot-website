<?php
session_start();

/* Run DB insert only on the initial booking POST.
   - booking form has: price (and no orderId / checksum)
   - Zaakpay handoff has: orderId (no checksum yet)
   - Zaakpay callback/return has: checksum
*/
$isBookingStage = ($_SERVER['REQUEST_METHOD'] === 'POST')
                  && isset($_POST['price'])
                  && !isset($_POST['orderId'])
                  && !isset($_POST['checksum']);

if ($isBookingStage) {
    // --- DB connection ---
    $conn = new mysqli('localhost', 'rivot', 'Riv0t@211', 'rivot_booking');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // --- Collect booking fields ---
    $price        = $_POST['price'] ?? '';
    $model        = $_POST['model'] ?? '';
    $color        = $_POST['color'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $name         = $_POST['name'] ?? '';
    $lastName     = $_POST['lastName'] ?? '';
    $mobile       = $_POST['mobile'] ?? '';
    $email        = $_POST['email'] ?? '';
    $address      = $_POST['address'] ?? '';
    $country      = $_POST['country'] ?? '';
    $pincode      = $_POST['pincode'] ?? '';
    $state        = $_POST['state'] ?? '';
    $city         = $_POST['city'] ?? '';
    $source       = $_POST['source'] ?? '';
    $referralCode = $_POST['referralCode'] ?? '';
    $terms        = isset($_POST['terms']) ? 1 : 0;

    // Generate trackId if not provided
    $trackId = $_POST['product1Description'] ?? '';
    if ($trackId === '') {
        $p = strtoupper(preg_replace('/\s+/', '', $product_name ?: 'NX100'));
        $m = strtoupper(preg_replace('/\s+/', '', $model ?: 'CLASSIC'));
        $c = strtoupper(preg_replace('/\s+/', '', $color ?: 'GRAY'));
        $trackId = "{$p}-{$m}-{$c}-" . mt_rand(100000, 999999);
    }

    $_SESSION['trackId'] = $trackId;

    if (empty($_REQUEST['product1Description'])) {
        $_REQUEST['product1Description'] = $trackId;
    }

    $orderId_init        = '';
    $transaction_id_init = '';
    $amount_init         = '';
    $statid_init         = '0';
    $payment_status_init = 'order_not_completed';
    $productDescription  = ($product_name ?: 'nx100') . '-' . ($color ?: '') . '-' . ($model ?: '');

    $sql = "INSERT INTO orders (
                price, model, color, product_name,
                trackId, orderId, productDescription, transaction_id,
                amount, statid, payment_status,
                name, lastName, mobile, email, address,
                country, pincode, state, city, source, referralCode,
                terms
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'dsssssssssssssssssssssi',
        $price,
        $model,
        $color,
        $product_name,
        $trackId,
        $orderId_init,
        $productDescription,
        $transaction_id_init,
        $amount_init,
        $statid_init,
        $payment_status_init,
        $name,
        $lastName,
        $mobile,
        $email,
        $address,
        $country,
        $pincode,
        $state,
        $city,
        $source,
        $referralCode,
        $terms
    );

    if (!$stmt->execute()) {
        error_log('Order insert failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}

// Map booking form fields to Zaakpay fields
$prefill = [
    'buyerFirstName'      => $_REQUEST['name'] ?? '',
    'buyerLastName'       => $_REQUEST['lastName'] ?? '',
    'buyerAddress'        => $_REQUEST['address'] ?? '',
    'buyerCity'           => $_REQUEST['city'] ?? '',
    'buyerState'          => $_REQUEST['state'] ?? '',
    'buyerCountry'        => $_REQUEST['country'] ?? '',
    'buyerPincode'        => $_REQUEST['pincode'] ?? '',
    'buyerPhoneNumber'    => $_REQUEST['mobile'] ?? '',
    'buyerEmail'          => $_REQUEST['email'] ?? '',
    'buyerPrice'          => $_REQUEST['price'] ?? '',
    'product_name'        => $_REQUEST['product_name'] ?? '',
    'color'               => $_REQUEST['color'] ?? '',
    'model'               => $_REQUEST['model'] ?? '',
    'productDescription'  => $_REQUEST['productDescription'] ?? '',
    'product1Description' => $_REQUEST['product1Description'] ?? '',
];

if (array_key_exists('orderId', $_POST) && !array_key_exists('checksum', $_POST)) {
    require_once('./../lib/Checksum.php');
    require_once('./../../../resources/Config.php');

    $checksumFlag = $environment != "https://api.zaakpay.com";

    $all = Checksum::getAllParams();
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
        <form id="processPaymentForm" action="<?php echo $environment . $transactApi; ?>" method="post" style="display: none;">
            <table width="650px;" class="customtable-all card" style="display: none;">
                <tr>
                    <th colspan="2">REQUEST PARAMETERS</th>
                </tr>
                <tr>
                    <th>Order Id</th>
                    <td><?php echo $_POST['orderId']; ?></td>
                </tr>
                <tr>
                    <th>Checksum String</th>
                    <td><?php echo htmlentities($all); ?></td>
                </tr>
                <tr>
                    <th>Calculated Checksum</th>
                    <td><?php echo $checksum; ?></td>
                </tr>
            </table>
            <?php Checksum::outputForm($checksum); ?>
        </form>
        <script type="text/javascript">
            document.getElementById('processPaymentForm').submit();
        </script>
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
            <form action="<?php echo $environment . $transactApi; ?>" method="post">
                <?php Checksum::outputForm($checksum); ?>
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

<?php if (!array_key_exists('orderId', $_POST)) {
    require_once('./../../../resources/Config.php');
?>
<html>
<head>
    <link rel="stylesheet" href="./../css/style.css">
    <title>Sample Input</title>
    <script type="text/javascript">
    function autoPop() {
        document.getElementById("orderId").value = "ZPLive" + String(new Date().getTime());
        var today = new Date();
        var dateString = String(today.getFullYear()).concat("-").concat(String(today.getMonth() + 1)).concat("-").concat(String(today.getDate()));
        document.getElementById("txnDate").value = dateString;
    }

    function submitForm() {
        var form = document.getElementById("checkoutForm") || document.forms[0];

        var amtStr = document.getElementById("amount").value || "";
        var cleaned = amtStr.toString().replace(/,/g, "");
        var rupees = parseFloat(cleaned);

        // Zaakpay amount goes in paise
        var paise = Math.round(rupees * 100);

        if (!isFinite(paise) || paise <= 0) {
            alert("Invalid amount.");
            return;
        }

        document.getElementById("amount").value = paise;
        form.action = "Payment.php";
        form.submit();
    }

    function autoStart() {
        autoPop();

        var emailEl = document.querySelector('input[name="buyerEmail"]');
        var amountEl = document.getElementById('amount');

        var email = emailEl ? emailEl.value.trim() : "";
        var amount = amountEl ? amountEl.value.trim() : "";

        if (email && amount) {
            setTimeout(function(){ submitForm(); }, 0);
        } else {
            console.warn('Missing buyerEmail or amount. Not auto-submitting.');
        }
    }

    window.addEventListener('load', function () {
        try { if (typeof autoPop === 'function') autoPop(); } catch(e) {}
        setTimeout(function(){
            var btn = document.getElementById('payNowBtn');
            if (btn) btn.click();
        }, 0);
    });
    </script>
</head>

<body onload="autoStart()">
<div align="center" style="display: none;">
<form id="checkoutForm" action="Payment.php" method="post" style="display: none;">
    <table width="650px;" class="customtable-all card">
        <tr>
            <th colspan="2">MANDATORY PARAMETERS</th>
        </tr>

        <input type="hidden" name="merchantIdentifier" value="<?php echo $merchantIdentifier; ?>" />
        <input type="hidden" id="orderId" name="orderId" />
        <input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>" />
        <input type="hidden" name="currency" value="INR" />

        <tr>
            <td width="50%" align="right" valign="middle">Amount</td>
            <td width="50%" align="center" valign="middle">
                <input type="hidden" name="amount" id="amount" value="<?php echo $prefill['buyerPrice']; ?>" placeholder="In Rupees" />
            </td>
        </tr>

        <tr>
            <td width="50%" align="right" valign="middle">Buyer Email</td>
            <td width="50%" align="center" valign="middle">
                <input type="hidden" name="buyerEmail" value="<?php echo htmlspecialchars($prefill['buyerEmail']); ?>" />
            </td>
        </tr>

        <tr>
            <th align="center" colspan="2" style="text-align: center;"><br>
                <input id="payNowBtn" onclick="javascript:submitForm()" type="submit" value="Pay Now"><br><br>
            </th>
        </tr>
    </table>
    <br><br>

    <input type="hidden" name="buyerFirstName" value="<?php echo htmlspecialchars($prefill['buyerFirstName']); ?>" />
    <input type="hidden" name="buyerLastName" value="<?php echo htmlspecialchars($prefill['buyerLastName']); ?>" />
    <input type="hidden" name="buyerAddress" value="<?php echo htmlspecialchars($prefill['buyerAddress']); ?>" />
    <input type="hidden" name="buyerCity" value="<?php echo htmlspecialchars($prefill['buyerCity']); ?>" />
    <input type="hidden" name="buyerState" value="<?php echo htmlspecialchars($prefill['buyerState']); ?>" />
    <input type="hidden" name="buyerCountry" value="<?php echo htmlspecialchars($prefill['buyerCountry']); ?>" />
    <input type="hidden" name="buyerPincode" value="<?php echo htmlspecialchars($prefill['buyerPincode']); ?>" />
    <input type="hidden" name="buyerPhoneNumber" value="<?php echo htmlspecialchars($prefill['buyerPhoneNumber']); ?>" />
    <input type="hidden" name="productDescription" value="<?php echo htmlspecialchars($prefill['product_name'] . "-" . $prefill['color'] . "-" . $prefill['model']); ?>" />
    <input type="hidden" name="product1Description" value="<?php echo htmlspecialchars($prefill['product1Description']); ?>" />
    <input type="hidden" name="txnDate" id="txnDate" />
</form>
</div>
<br><br><br><br><br>
</body>
</html>
<?php } ?>