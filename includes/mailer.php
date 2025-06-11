<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Adjust path if necessary

function getVerificationEmailTemplate($name, $verification_link) {
    $email_template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verifikasi Akun Anda</title>
        <style>
            body {
                font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }
            .header {
                background-color: #FF5722; /* Primary color for Sambal Mama Ana */
                padding: 30px;
                text-align: center;
                color: #ffffff;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
            }
            .content {
                padding: 30px;
                color: #333333;
                line-height: 1.6;
            }
            .content h2 {
                color: #FF5722;
                font-size: 22px;
                margin-top: 0;
                margin-bottom: 15px;
            }
            .content p {
                margin-bottom: 15px;
            }
            .button-container {
                text-align: center;
                margin: 25px 0;
            }
            .button {
                display: inline-block;
                background-color: #FF5722;
                color: #ffffff !important;
                padding: 12px 25px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
                font-size: 16px;
            }
            .footer {
                background-color: #f0f0f0;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #777777;
                border-top: 1px solid #e0e0e0;
            }
            .footer p {
                margin: 5px 0;
            }
            .footer a {
                color: #FF5722;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>Sambal Mama Ana</h1>
            </div>
            <div class="content">
                <h2>Verifikasi Akun Anda</h2>
                <p>Halo ' . htmlspecialchars($name) . ',</p>
                <p>Terima kasih telah mendaftar di Sambal Mama Ana. Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:</p>
                <div class="button-container">
                    <a href="' . htmlspecialchars($verification_link) . '" class="button">Verifikasi Akun Saya</a>
                </div>
                <p>Jika tombol di atas tidak berfungsi, Anda juga bisa menyalin dan menempelkan tautan berikut ke browser Anda:</p>
                <p><a href="' . htmlspecialchars($verification_link) . '">' . htmlspecialchars($verification_link) . '</a></p>
                <p>Jika Anda tidak mendaftar untuk akun ini, Anda bisa mengabaikan email ini dengan aman.</p>
                <p>Salam Hormat,<br>Tim Sambal Mama Ana</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date("Y") . ' Sambal Mama Ana. Semua hak dilindungi undang-undang.</p>
                <p>Jl. Contoh No. 123, Kota Contoh, Negara Contoh</p>
                <p><a href="mailto:support@sambal-mama-ana.com">support@sambal-mama-ana.com</a></p>
            </div>
        </div>
    </body>
    </html>
    ';
    return $email_template;
}

function sendEmail($to_email, $to_name, $subject, $body) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = '49ad4ec6bfe52a'; // Replace with your SMTP username
        $mail->Password = 'c5d07a5c55d54a'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS for port 465
        $mail->Port = 587; // Use 465 for SMTPS, 587 for STARTTLS

        // Recipients
        $mail->setFrom('no-reply@sambal-mama-ana.com', 'Sambal Mama Ana'); // Your sender email and name
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
