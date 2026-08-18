<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/sslcommerz.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Direct access not allowed");
}

$pdo = getDB();

$tran_id = $_POST['tran_id'] ?? '';
$val_id = $_POST['val_id'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($tran_id)) {
    die("Transaction ID missing");
}

// Fetch pending ticket
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE tran_id = ? AND status = 'pending'");
$stmt->execute([$tran_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Invalid Transaction ID or already processed.");
}

$ticket_id = $ticket['ticket_id'];

// ===== SUCCESS / VALID =====
if ($status === 'VALID' || $status === 'SUCCESS') {
    // Validate with SSLCommerz Server
    $requested_url = (SSLCZ_VALIDATION_API . "?val_id=" . $val_id . "&store_id=" . SSLCZ_STORE_ID . "&store_passwd=" . urlencode(SSLCZ_STORE_PASSWORD) . "&v=1&format=json");

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $requested_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    if ($code == 200 && !(curl_errno($handle))) {
        $result = json_decode($result, true);

        if (isset($result['status']) && ($result['status'] == 'VALID' || $result['status'] == 'VALIDATED')) {
            // Amount check (allowing minor differences sometimes from SSL, but usually exact match)
            if ($result['amount'] >= $ticket['total_price']) {
                // Payment Validated - Update DB
                $pdo->prepare("UPDATE tickets SET status = 'booked' WHERE ticket_id = ?")->execute([$ticket_id]);
                
                // Fetch details for email
                $stmt = $pdo->prepare("
                    SELECT t.*, m.title, c.email, c.name 
                    FROM tickets t 
                    JOIN shows s ON t.show_id = s.show_id 
                    JOIN movies m ON s.movie_id = m.movie_id 
                    JOIN customers c ON t.customer_id = c.customer_id 
                    WHERE t.ticket_id = ?
                ");
                $stmt->execute([$ticket_id]);
                $fullTicket = $stmt->fetch();

                if ($fullTicket) {
                    require_once __DIR__ . '/email_helper.php';
                    if (function_exists('sendTicketEmail')) {
                        sendTicketEmail($fullTicket['email'], $fullTicket['name'], $ticket_id, $fullTicket['title'], $fullTicket['seat_details'], $fullTicket['total_price'], $fullTicket['qr_code']);
                    }
                }

                header("Location: ../book_ticket.php?success_ticket_id=" . $ticket_id);
                exit;
            }
        }
    }
}

// ===== FAILED / CANCELLED / INVALID =====
// If we reach here, it failed or validation failed.
// Mark ticket as failed
$pdo->prepare("UPDATE tickets SET status = 'failed' WHERE ticket_id = ?")->execute([$ticket_id]);

// Free up the reserved seats
$pdo->prepare("
    UPDATE seats s 
    JOIN ticket_seats ts ON s.seat_id = ts.seat_id 
    SET s.status = 'available' 
    WHERE ts.ticket_id = ?
")->execute([$ticket_id]);

header("Location: ../book_ticket.php?fail=1");
exit;
