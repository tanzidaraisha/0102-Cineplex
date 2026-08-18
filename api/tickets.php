<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/email_helper.php';
header('Content-Type: application/json');

$pdo = getDB();

// ===== GET REQUESTS =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    // Get customer's tickets
    if ($action === 'my_tickets') {
        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT t.*, m.title, m.poster_url, 
                   DATE_FORMAT(s.show_date, '%a, %b %d, %Y') as show_date_display,
                   DATE_FORMAT(s.show_time, '%h:%i %p') as show_time_display,
                   s.hall_name
            FROM tickets t
            JOIN shows s ON t.show_id = s.show_id
            JOIN movies m ON s.movie_id = m.movie_id
            WHERE t.customer_id = ?
            ORDER BY t.booking_date DESC
        ");
        $stmt->execute([$_SESSION['customer_id']]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    // Get single ticket details
    if ($action === 'ticket_detail') {
        $ticket_id = (int)($_GET['ticket_id'] ?? 0);
        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT t.*, m.title, m.poster_url, m.genre, m.duration,
                   DATE_FORMAT(s.show_date, '%a, %b %d, %Y') as show_date_display,
                   DATE_FORMAT(s.show_time, '%h:%i %p') as show_time_display,
                   s.hall_name, c.name as customer_name, c.email as customer_email
            FROM tickets t
            JOIN shows s ON t.show_id = s.show_id
            JOIN movies m ON s.movie_id = m.movie_id
            JOIN customers c ON t.customer_id = c.customer_id
            WHERE t.ticket_id = ? AND t.customer_id = ?
        ");
        $stmt->execute([$ticket_id, $_SESSION['customer_id']]);
        $ticket = $stmt->fetch();
        if ($ticket) {
            echo json_encode(['status' => 'success', 'ticket' => $ticket]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ticket not found']);
        }
        exit;
    }
}

// ===== POST REQUESTS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['customer_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $customer_id = $_SESSION['customer_id'];

    // ===== BOOK TICKET =====
    if ($action === 'book') {
        require_once __DIR__ . '/../config/sslcommerz.php';
        
        $show_id = (int)($_POST['show_id'] ?? 0);
        $seat_ids = $_POST['seat_ids'] ?? [];
        $quantity = count($seat_ids);

        if ($quantity === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No seats selected']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Get movie price
            $stmt = $pdo->prepare("SELECT m.price, m.title FROM shows s JOIN movies m ON s.movie_id = m.movie_id WHERE s.show_id = ?");
            $stmt->execute([$show_id]);
            $showInfo = $stmt->fetch();
            if (!$showInfo) throw new Exception("Show not found");
            $base_price = (float)$showInfo['price'];

            // Get seat labels and types for display and calculation
            $placeholders = implode(',', array_fill(0, count($seat_ids), '?'));
            $stmt = $pdo->prepare("SELECT seat_id, row_label, seat_number, seat_type, status FROM seats WHERE seat_id IN ($placeholders) AND show_id = ?");
            $params = array_merge($seat_ids, [$show_id]);
            $stmt->execute($params);
            $seatRows = $stmt->fetchAll();

            $seatLabels = [];
            $total_price = 0;

            foreach ($seatRows as $s) {
                if ($s['status'] === 'booked') {
                    throw new Exception("Seat {$s['row_label']}{$s['seat_number']} is already booked!");
                }
                $seatLabels[] = $s['row_label'] . $s['seat_number'];
                
                // Calculate dynamic price
                $seatPrice = $base_price;
                if ($s['seat_type'] === 'premium') $seatPrice += 100;
                if ($s['seat_type'] === 'vip') $seatPrice += 300;
                $total_price += $seatPrice;
            }
            $seat_details = implode(', ', $seatLabels);

            // Generate a unique QR code data string and Transaction ID
            $qr_data = 'CINEPLEX-' . time() . '-' . $customer_id . '-' . $show_id;
            $tran_id = uniqid('SSLCZ_') . time();

            // Insert pending ticket
            $stmt = $pdo->prepare("INSERT INTO tickets (customer_id, show_id, quantity, seat_details, total_price, status, payment_method, tran_id, qr_code) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$customer_id, $show_id, $quantity, $seat_details, $total_price, 'pending', 'sslcommerz', $tran_id, $qr_data]);
            $ticket_id = $pdo->lastInsertId();

            // Book seats and link temporarily
            $updateStmt = $pdo->prepare("UPDATE seats SET status = 'booked' WHERE seat_id = ? AND status = 'available'");
            $linkStmt = $pdo->prepare("INSERT INTO ticket_seats (ticket_id, seat_id) VALUES (?,?)");

            foreach ($seat_ids as $sid) {
                $updateStmt->execute([$sid]);
                if ($updateStmt->rowCount() === 0) {
                    throw new Exception("Seat already booked by someone else!");
                }
                $linkStmt->execute([$ticket_id, $sid]);
            }

            $pdo->commit();

            // Initiate SSLCommerz Session
            $post_data = array();
            $post_data['store_id'] = SSLCZ_STORE_ID;
            $post_data['store_passwd'] = SSLCZ_STORE_PASSWORD;
            $post_data['total_amount'] = $total_price;
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = $tran_id;
            $post_data['success_url'] = "http://localhost/cineplex-ticketing/api/ssl_callback.php";
            $post_data['fail_url'] = "http://localhost/cineplex-ticketing/api/ssl_callback.php";
            $post_data['cancel_url'] = "http://localhost/cineplex-ticketing/api/ssl_callback.php";
            
            // Customer Info
            $post_data['cus_name'] = $_SESSION['customer_name'] ?? 'Customer';
            $post_data['cus_email'] = $_SESSION['customer_email'] ?? 'customer@cineplex0102.com';
            $post_data['cus_add1'] = "Dhaka";
            $post_data['cus_city'] = "Dhaka";
            $post_data['cus_country'] = "Bangladesh";
            $post_data['cus_phone'] = "01700000000";
            
            $post_data['shipping_method'] = "NO";
            $post_data['product_name'] = "Movie Ticket: " . $showInfo['title'];
            $post_data['product_category'] = "Entertainment";
            $post_data['product_profile'] = "general";

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, SSLCZ_SESSION_API );
            curl_setopt($handle, CURLOPT_TIMEOUT, 30);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($handle, CURLOPT_POST, 1 );
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);

            $content = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if ($code == 200 && !(curl_errno($handle))) {
                $sslcommerzResponse = json_decode($content, true);
                if(isset($sslcommerzResponse['status']) && $sslcommerzResponse['status'] == 'SUCCESS') {
                    echo json_encode([
                        'status' => 'success',
                        'gateway_url' => $sslcommerzResponse['GatewayPageURL']
                    ]);
                    exit;
                } else {
                    throw new Exception("SSLCommerz Initialization Failed: " . $sslcommerzResponse['failedreason']);
                }
            } else {
                throw new Exception("Failed to connect to SSLCommerz API");
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== CANCEL TICKET =====
    if ($action === 'cancel') {
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id = ? AND customer_id = ? AND status = 'booked'");
            $stmt->execute([$ticket_id, $customer_id]);
            if ($stmt->rowCount() === 0) throw new Exception("Invalid ticket ID or already cancelled");

            // Free seats
            $pdo->prepare("UPDATE seats s JOIN ticket_seats ts ON s.seat_id = ts.seat_id SET s.status = 'available' WHERE ts.ticket_id = ?")->execute([$ticket_id]);

            // Update ticket
            $pdo->prepare("UPDATE tickets SET status = 'cancelled' WHERE ticket_id = ?")->execute([$ticket_id]);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Ticket #' . $ticket_id . ' cancelled successfully. Refund will be processed.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== RESEND TICKET =====
    if ($action === 'resend') {
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT t.*, m.title, c.email, c.name,
                   DATE_FORMAT(s.show_date, '%a, %b %d, %Y') as show_date_display,
                   DATE_FORMAT(s.show_time, '%h:%i %p') as show_time_display
            FROM tickets t 
            JOIN shows s ON t.show_id = s.show_id 
            JOIN movies m ON s.movie_id = m.movie_id
            JOIN customers c ON t.customer_id = c.customer_id
            WHERE t.ticket_id = ? AND t.customer_id = ? AND t.status = 'booked'
        ");
        $stmt->execute([$ticket_id, $customer_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            echo json_encode(['status' => 'error', 'message' => 'Ticket not found or already cancelled']);
            exit;
        }

        sendTicketEmail($ticket['email'], $ticket['name'], $ticket['ticket_id'], $ticket['title'], $ticket['seat_details'], $ticket['total_price'], $ticket['qr_code']);
        echo json_encode(['status' => 'success', 'message' => 'E-ticket resent to ' . $ticket['email']]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

