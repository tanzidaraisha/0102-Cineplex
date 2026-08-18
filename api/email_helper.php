<?php
require_once __DIR__ . '/../config/email.php';

function sendTicketEmail($to, $name, $ticket_id, $movie, $seats, $total, $qr_data) {
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
    
    $subject = "Your 0102 Cineplex E-Ticket #$ticket_id";

    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; background: #f0f8ff; padding: 20px;'>
        <div style='max-width: 500px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #1a6fb5, #00c6ff); padding: 25px; text-align: center; color: white;'>
                <h1 style='margin:0;'>0102 CINEPLEX</h1>
                <p style='margin:5px 0 0;'>E-Ticket Confirmation</p>
            </div>
            <div style='padding: 25px;'>
                <p>Hello <strong>$name</strong>,</p>
                <p>Your booking is confirmed!</p>
                <table style='width:100%; border-collapse:collapse; margin: 15px 0;'>
                    <tr><td style='padding:8px; border-bottom:1px solid #eee; color:#888;'>Ticket ID</td><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold;'>#$ticket_id</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #eee; color:#888;'>Movie</td><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold;'>$movie</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #eee; color:#888;'>Seats</td><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold;'>$seats</td></tr>
                    <tr><td style='padding:8px; color:#888;'>Total Paid</td><td style='padding:8px; font-weight:bold; color:#1a6fb5; font-size:1.2em;'>BDT $total</td></tr>
                </table>
                <div style='text-align:center; margin: 20px 0;'>
                    <p style='color:#888; font-size:0.9em;'>Show this QR code at the entrance:</p>
                    <img src='$qr_url' alt='QR Code' style='width:180px; height:180px;'>
                </div>
                <p style='color:#888; font-size:0.85em; text-align:center;'>Enjoy your movie!</p>
            </div>
        </div>
    </body>
    </html>";

    // Save a local copy always
    $logDir = __DIR__ . '/../assets/emails';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    file_put_contents($logDir . "/ticket_$ticket_id.html", $body);

    // Send via SMTP if configured
    if (defined('EMAIL_ENABLED') && EMAIL_ENABLED && !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD)) {
        require_once __DIR__ . '/../libs/SmtpMailer.php';
        $mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD);
        $result = $mailer->send($to, $subject, $body, SMTP_FROM_NAME);
        
        $logFile = $logDir . "/email_log.txt";
        $logEntry = date('Y-m-d H:i:s') . " | Ticket #$ticket_id | To: $to | " . ($result['success'] ? 'SENT' : 'FAILED: ' . ($result['error'] ?? '')) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        return $result['success'];
    }
    
    return false;
}
