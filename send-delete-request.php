<?php

session_start();

$to = "info@myfarmsight.com"; 
$siteName = "MyFarmSight";
$redirectUrl = "/"; 

$limitTime = 300; // Time in seconds

if (isset($_SESSION['last_submit_time'])) {
    $timePassed = time() - $_SESSION['last_submit_time'];
    if ($timePassed < $limitTime) {
        // Silently redirect if submitted too frequently
        header("Location: " . $redirectUrl);
        exit;
    }
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: $redirectUrl");
    exit;
}

if (!empty($_POST['website_hp'])) {
    header("Location: " . $redirectUrl);
    exit;
}

// Prevent XSS
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function cleanHeader($value) {
    return str_replace(["\r", "\n", "%0a", "%0d"], '', trim($value ?? ''));
}

$feedback = clean($_POST["feedback"] ?? "");
$name     = clean($_POST["name"] ?? "");
$email    = clean($_POST["email"] ?? "");
$username = clean($_POST["username"] ?? "");
$phone    = clean($_POST["phone"] ?? "");
$reason   = clean($_POST["reason"] ?? "");
$confirm  = isset($_POST["confirmDelete"]); // checkbox

if (empty($feedback) || empty($name) || empty($email) || empty($username) || !$confirm) {
    header("Location: $redirectUrl");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: $redirectUrl");
    exit;
}

$_SESSION['last_submit_time'] = time();


$cleanUsername = cleanHeader($username);
$subject       = "Account Deletion Request - $cleanUsername";

$message = "Account Deletion Request Submitted\n";
$message .= "------------------------------------\n";
$message .= "Name:      $name\n";
$message .= "Email:     $email\n";
$message .= "Username:  $username\n";
$message .= "Phone:     " . ($phone ?: "N/A") . "\n";
$message .= "Reason:    " . ($reason ?: "N/A") . "\n\n";
$message .= "Feedback:\n$feedback\n";
$message .= "------------------------------------\n";
$message .= "Confirmed irreversible deletion: YES\n";
$message .= "Submitted On: " . date("Y-m-d H:i:s") . "\n";

// Use hardcoded domain sender, set user email ONLY in safely cleaned Reply-To
$headers   = [];
$headers[] = "From: $siteName <info@myfarmsight.com>";
$headers[] = "Reply-To: $email";
$headers[] = "Content-Type: text/plain; charset=UTF-8";

// Send email using implode for standardized line endings
$mailSent = mail($to, $subject, $message, implode("\r\n", $headers));

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Request Submitted</title>

  <style>
    :root {
      --bg: #f8fafc;
      --bg2: #eef2ff;
      --card: rgba(255,255,255,0.92);
      --border: rgba(0,0,0,0.10);
      --text: #0f172a;
      --muted: #64748b;
      --success: #22c55e;
      --danger: #ef4444;
      --shadow: 0 35px 90px rgba(0,0,0,0.25);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", Arial, sans-serif;
    }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: radial-gradient(circle at top left, var(--bg2), var(--bg));
      overflow: hidden;
    }

    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(70px);
      opacity: 0.55;
      animation: floatBlob 8s ease-in-out infinite alternate;
    }

    .blob.one {
      width: 320px;
      height: 320px;
      background: rgba(34, 197, 94, 0.30);
      top: -140px;
      left: -140px;
    }

    .blob.two {
      width: 380px;
      height: 380px;
      background: rgba(239, 68, 68, 0.25);
      bottom: -160px;
      right: -160px;
      animation-delay: 2s;
    }

    @keyframes floatBlob {
      from { transform: translate(0,0) scale(1); }
      to { transform: translate(45px,-30px) scale(1.1); }
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      display: flex;
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.35s ease forwards;
      padding: 20px;
      z-index: 5;
    }

    .modal {
      width: 100%;
      max-width: 520px;
      background: var(--card);
      border-radius: 18px;
      padding: 30px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      transform: translateY(10px);
      animation: slideUp 0.35s ease forwards;
      position: relative;
      overflow: hidden;
    }

    .modal::before {
      content: "";
      position: absolute;
      width: 240px;
      height: 240px;
      border-radius: 50%;
      background: rgba(34,197,94,0.18);
      top: -110px;
      left: -110px;
      filter: blur(25px);
    }

    .icon {
      width: 62px;
      height: 62px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
      border: 1px solid rgba(0,0,0,0.08);
      position: relative;
      z-index: 2;
    }

    .icon.success {
      background: rgba(34,197,94,0.14);
      border-color: rgba(34,197,94,0.25);
    }

    .icon.fail {
      background: rgba(239,68,68,0.14);
      border-color: rgba(239,68,68,0.25);
    }

    .icon svg {
      width: 30px;
      height: 30px;
    }

    h2 {
      font-size: 22px;
      margin-bottom: 8px;
      color: var(--text);
      position: relative;
      z-index: 2;
    }

    p {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.6;
      position: relative;
      z-index: 2;
    }

    .meta {
      margin-top: 14px;
      padding: 12px;
      border-radius: 12px;
      background: rgba(15, 23, 42, 0.04);
      border: 1px solid rgba(0,0,0,0.06);
      font-size: 13px;
      color: #334155;
      position: relative;
      z-index: 2;
    }

    .meta strong {
      color: var(--text);
    }

    .progress {
      margin-top: 18px;
      height: 10px;
      width: 100%;
      border-radius: 999px;
      background: rgba(0,0,0,0.10);
      overflow: hidden;
      position: relative;
      z-index: 2;
    }

    .bar {
      height: 100%;
      width: 0%;
      border-radius: 999px;
      animation: fillBar 4s linear forwards;
    }

    .bar.success {
      background: linear-gradient(90deg, #22c55e, #16a34a);
    }

    .bar.fail {
      background: linear-gradient(90deg, #ef4444, #fb7185);
    }

    .small {
      margin-top: 10px;
      font-size: 12px;
      color: #94a3b8;
      position: relative;
      z-index: 2;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0px); }
    }

    @keyframes fillBar {
      from { width: 0%; }
      to { width: 100%; }
    }
  </style>
</head>

<body>

  <div class="blob one"></div>
  <div class="blob two"></div>

  <div class="overlay">
    <div class="modal">

      <?php if ($mailSent): ?>
        <div class="icon success">
          <svg viewBox="0 0 24 24" fill="#16a34a">
            <path d="M9 16.17l-3.88-3.88a1 1 0 10-1.41 1.41l4.59 4.59a1 1 0 001.41 0l10-10a1 1 0 10-1.41-1.41L9 16.17z"/>
          </svg>
        </div>

        <h2>Request Submitted Successfully</h2>
        <p>
          Your account deletion request has been sent. Our team will review it and process it soon.
        </p>

        <div class="meta">
          <strong>User:</strong> <?php echo $username; ?><br>
          <strong>Email:</strong> <?php echo $email; ?>
        </div>

        <div class="progress">
          <div class="bar success"></div>
        </div>

      <?php else: ?>
        <div class="icon fail">
          <svg viewBox="0 0 24 24" fill="#ef4444">
            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v-2h2v2zm0-4h-2V6h2v6z"/>
          </svg>
        </div>

        <h2>Submission Failed</h2>
        <p>
          We could not send your request. Please try again later or contact support.
        </p>

        <div class="meta">
          <strong>Tip:</strong> Your server may not support PHP mail(). Consider using SMTP.
        </div>

        <div class="progress">
          <div class="bar fail"></div>
        </div>
      <?php endif; ?>

      <div class="small">Redirecting back automatically...</div>
    </div>
  </div>

  <script>
    setTimeout(() => {
     window.location.href = <?php echo json_encode($redirectUrl); ?>;
    }, 4000);
  </script>

</body>
</html>