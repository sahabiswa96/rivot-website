<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/site_admin/config.php';

/**
 * Get setting value from database
 */
function get_setting($key, $default = '')
{
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (\Throwable $e) {
        error_log("Failed to get setting '{$key}': " . $e->getMessage());
        return $default;
    }
}

/**
 * JSON response helper
 */
function json_response($success, $message, $statusCode = 200, $extra = [])
{
    http_response_code($statusCode);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

/**
 * Safe clean text
 */
function clean_text($value)
{
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

/**
 * Safe text with line breaks for HTML
 */
function clean_text_with_breaks($value)
{
    return nl2br(htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8'));
}

/**
 * Humanize field label
 */
function field_label($key)
{
    $customLabels = [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'company' => 'Company',
        'contact' => 'Contact Person',
        'owner' => 'Owner/Partner Name',
        'location' => 'Preferred Location',
        'category' => 'Category',
        'outlet' => 'Media Outlet',
        'type' => 'Type',
        'deadline' => 'Deadline',
        'range' => 'Investment Range',
        'position' => 'Position',
        'experience' => 'Experience',
        'investment' => 'Investment Capacity',
        'country' => 'Country',
        'business' => 'Business Type',
        'message' => 'Message',
        'cv' => 'CV',
    ];

    if (isset($customLabels[$key])) {
        return $customLabels[$key];
    }

    return ucwords(str_replace('_', ' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));
}

$formTitles = [
    'vendor' => 'Vendor Partnership',
    'dealer' => 'Dealership Opportunity',
    'media' => 'Media Inquiry',
    'investor' => 'Investment Opportunity',
    'careers' => 'Career Opportunities',
    'overseas' => 'Overseas Partnership',
    'support' => 'Customer Support Request',
    'contact' => 'Contact Us Inquiry'
];

// Debug logging
error_log("send_contact_email.php accessed - Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', 405);
}

$formType = $_POST['formType'] ?? '';
if (!$formType || !isset($formTitles[$formType])) {
    json_response(false, 'Invalid form type', 400);
}

$formTitle = $formTitles[$formType];
error_log("Processing form type: {$formType} | Title: {$formTitle}");

/**
 * Required fields by form type
 */
$requiredFields = [
    'vendor'   => ['company', 'contact', 'email', 'phone', 'category'],
    'dealer'   => ['company', 'owner', 'email', 'phone', 'location'],
    'media'    => ['name', 'outlet', 'email', 'type', 'message'],
    'investor' => ['name', 'company', 'email', 'phone', 'type', 'range'],
    'careers'  => ['name', 'email', 'phone', 'position'],
    'overseas' => ['company', 'contact', 'email', 'phone', 'country', 'business'],
    'support'  => ['name', 'email', 'message'],
    'contact'  => ['name', 'email', 'message'],
];

$formData = [];
$missingFields = [];

foreach ($_POST as $key => $value) {
    if ($key === 'formType') {
        continue;
    }

    $trimmed = trim((string)$value);

    if ($trimmed === '' && $key !== 'message') {
        continue;
    }

    $label = field_label($key);

    if ($key === 'message' || stripos($key, 'message') !== false) {
        $formData[$label] = clean_text_with_breaks($value);
    } else {
        $formData[$label] = clean_text($value);
    }
}

// Validate required text fields
if (isset($requiredFields[$formType])) {
    foreach ($requiredFields[$formType] as $field) {
        $value = trim((string)($_POST[$field] ?? ''));

        if ($value === '') {
            $missingFields[] = field_label($field);
        }
    }
}

// Careers CV validation
$hasAttachment = false;
$attachmentPath = '';
$attachmentName = '';

if ($formType === 'careers') {
    if (!isset($_FILES['cv'])) {
        $missingFields[] = 'CV';
    } else {
        if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
            error_log("CV upload error code: " . $_FILES['cv']['error']);
            json_response(false, 'CV upload failed. Please choose the file again.', 400);
        }

        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $attachmentName = basename($_FILES['cv']['name']);
        $fileTmpName = $_FILES['cv']['tmp_name'];
        $fileSize = (int) $_FILES['cv']['size'];
        $fileExtension = strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            json_response(false, 'Invalid CV format. Only PDF, DOC, DOCX files are allowed.', 400);
        }

        if ($fileSize <= 0) {
            json_response(false, 'Uploaded CV file is empty.', 400);
        }

        if ($fileSize > 10 * 1024 * 1024) {
            json_response(false, 'CV file is too large. Maximum allowed size is 10MB.', 400);
        }

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            json_response(false, 'Failed to prepare file upload directory.', 500);
        }

        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $attachmentName);
        $attachmentPath = $uploadDir . time() . '_' . uniqid() . '_' . $safeFileName;

        if (!move_uploaded_file($fileTmpName, $attachmentPath)) {
            error_log("Failed to move uploaded CV file from temp path.");
            json_response(false, 'Failed to upload CV file on server.', 500);
        }

        $hasAttachment = true;
        error_log("CV uploaded successfully: " . $attachmentPath);
    }
}

if (!empty($missingFields)) {
    if ($hasAttachment && $attachmentPath && file_exists($attachmentPath)) {
        @unlink($attachmentPath);
    }

    json_response(false, 'Please fill the required fields: ' . implode(', ', $missingFields), 400);
}

if (empty($formData) && !$hasAttachment) {
    json_response(false, 'No form data received', 400);
}

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'change@rivotmotors.com';
    $mail->Password   = 'RIVOT@M0tors';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Optional debug
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    $adminEmail = get_setting('admin_email', 'parthait2003@gmail.com');

    $mail->setFrom('change@rivotmotors.com', 'RIVOT Motors');
    $mail->addAddress($adminEmail, 'RIVOT Motors Admin');

    // reply-to
    if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $replyName = trim((string)($_POST['name'] ?? $_POST['contact'] ?? $_POST['owner'] ?? 'Website User'));
        $mail->addReplyTo($_POST['email'], $replyName);
    }

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'New ' . $formTitle . ' Submission - RIVOT Motors';

    $submittedAt = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    // HTML mail body
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>New {$formTitle}</title>
    </head>
    <body style='margin:0;padding:20px;background:#111;font-family:Arial,sans-serif;'>
        <div style='max-width:820px;margin:0 auto;background:#1f1f1f;color:#ffffff;border:1px solid #d0d0d0;border-radius:10px;overflow:hidden;'>
            <div style='background:#000000;padding:28px 20px;text-align:center;'>
                <h1 style='margin:0;font-size:24px;line-height:1.3;'>New " . clean_text($formTitle) . "</h1>
            </div>

            <div style='padding:28px;'>
                <h2 style='margin:0 0 14px;font-size:20px;color:#dcdcdc;'>Submission Details:</h2>
                <div style='height:2px;background:#CE6723;margin-bottom:24px;'></div>
    ";

    foreach ($formData as $label => $value) {
        if ($label === 'Message' || stripos($label, 'Message') !== false) {
            $htmlBody .= "
                <div style='margin:18px 0;padding:16px;background:#222222;border-left:4px solid #CE6723;'>
                    <div style='font-weight:bold;color:#CE6723;margin-bottom:8px;'>" . clean_text($label) . ":</div>
                    <div style='color:#f1f1f1;line-height:1.7;'>" . $value . "</div>
                </div>
            ";
        } else {
            $htmlBody .= "
                <div style='margin-bottom:14px;line-height:1.7;'>
                    <span style='display:inline-block;min-width:170px;font-weight:bold;color:#CE6723;vertical-align:top;'>" . clean_text($label) . ":</span>
                    <span style='color:#ffffff;'>" . $value . "</span>
                </div>
            ";
        }
    }

    if ($hasAttachment) {
        $htmlBody .= "
            <div style='margin-bottom:14px;line-height:1.7;'>
                <span style='display:inline-block;min-width:170px;font-weight:bold;color:#CE6723;vertical-align:top;'>Attachment:</span>
                <span style='color:#ffffff;'>" . clean_text($attachmentName) . " (attached to this email)</span>
            </div>
        ";
    }

    $htmlBody .= "
                <div style='margin-top:16px;line-height:1.7;'>
                    <span style='display:inline-block;min-width:170px;font-weight:bold;color:#CE6723;'>Submitted:</span>
                    <span style='color:#ffffff;'>" . clean_text($submittedAt) . "</span>
                </div>
            </div>

            <div style='background:#303030;padding:22px;text-align:center;color:#c8c8c8;font-size:14px;line-height:1.7;'>
                <div>This email was sent from the RIVOT Motors website connect form.</div>
                <div>IP Address: " . clean_text($ipAddress) . "</div>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $htmlBody;

    // Plain text body
    $textBody = "NEW " . strtoupper($formTitle) . " SUBMISSION\n\n";
    $textBody .= "Submission Details:\n";
    $textBody .= str_repeat("=", 50) . "\n\n";

    foreach ($formData as $label => $value) {
        $plainValue = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $value)));

        if ($label === 'Message' || stripos($label, 'Message') !== false) {
            $textBody .= strtoupper($label) . ":\n";
            $textBody .= str_repeat("-", 50) . "\n";
            $textBody .= $plainValue . "\n";
            $textBody .= str_repeat("-", 50) . "\n\n";
        } else {
            $textBody .= $label . ": " . $plainValue . "\n";
        }
    }

    if ($hasAttachment) {
        $textBody .= "Attachment: " . $attachmentName . " (attached to this email)\n";
    }

    $textBody .= "Submitted: " . $submittedAt . "\n";
    $textBody .= "IP Address: " . $ipAddress . "\n\n";
    $textBody .= "This email was sent from the RIVOT Motors website connect form.";

    $mail->AltBody = $textBody;

    // Add attachment
    if ($hasAttachment) {
        if (!file_exists($attachmentPath)) {
            throw new Exception('Uploaded CV file not found before sending mail.');
        }

        $mail->addAttachment($attachmentPath, $attachmentName);
        error_log("Attachment added to email: " . $attachmentName);
    }

    $mail->send();
    error_log("Contact form email sent successfully to {$adminEmail} for form type: {$formType}");

    if ($hasAttachment && $attachmentPath && file_exists($attachmentPath)) {
        @unlink($attachmentPath);
        error_log("Temporary attachment deleted: " . $attachmentPath);
    }

    json_response(true, 'Form submitted successfully', 200);

} catch (Exception $e) {
    error_log("Contact form email failed: " . $mail->ErrorInfo);
    error_log("Exception message: " . $e->getMessage());

    if ($hasAttachment && $attachmentPath && file_exists($attachmentPath)) {
        @unlink($attachmentPath);
        error_log("Temporary attachment deleted after failure: " . $attachmentPath);
    }

    json_response(false, 'Failed to send email: ' . $mail->ErrorInfo, 500);
}
?>