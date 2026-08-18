<?php
session_start();
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$pdo = getDB();

// ===== GET REQUESTS =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        $search = $_GET['search'] ?? '';
        $stmt = $pdo->prepare("
            SELECT m.*, COUNT(DISTINCT s.show_id) as show_count
            FROM movies m 
            LEFT JOIN shows s ON m.movie_id = s.movie_id 
            WHERE m.title LIKE ?
            GROUP BY m.movie_id
            ORDER BY m.created_date DESC
        ");
        $stmt->execute(["%$search%"]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($action === 'get_shows') {
        $movie_id = (int)($_GET['movie_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT show_id, DATE_FORMAT(show_date, '%a, %b %d') as date_display, 
                   DATE_FORMAT(show_time, '%h:%i %p') as time_display,
                   show_date, show_time, hall_name
            FROM shows 
            WHERE movie_id = ? AND CONCAT(show_date, ' ', show_time) >= NOW()
            ORDER BY show_date ASC, show_time ASC
        ");
        $stmt->execute([$movie_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($action === 'get_seats') {
        $show_id = (int)($_GET['show_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM seats WHERE show_id = ? ORDER BY row_label ASC, seat_number ASC");
        $stmt->execute([$show_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }
}

// ===== POST REQUESTS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD MOVIE
    if ($action === 'add') {
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $title = trim($_POST['title'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $poster_url = trim($_POST['poster_url'] ?? '');
        $price = (float)($_POST['price'] ?? 350);
        $rating = $_POST['rating'] ?? 'PG-13';
        $show_date = $_POST['show_date'] ?? '';
        $show_time = $_POST['show_time'] ?? '';
        $hall_name = $_POST['hall_name'] ?? 'Hall 1';

        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Title is required']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO movies (title, genre, duration, description, poster_url, price, rating) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $genre, $duration, $description, $poster_url, $price, $rating]);
            $movie_id = $pdo->lastInsertId();

            if (!empty($show_date) && !empty($show_time)) {
                $stmt = $pdo->prepare("INSERT INTO shows (movie_id, show_date, show_time, hall_name) VALUES (?,?,?,?)");
                $stmt->execute([$movie_id, $show_date, $show_time, $hall_name]);
                $show_id = $pdo->lastInsertId();
                generateSeats($pdo, $show_id);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ADD SHOW TO EXISTING MOVIE
    if ($action === 'add_show') {
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $movie_id = (int)($_POST['movie_id'] ?? 0);
        $show_date = $_POST['show_date'] ?? '';
        $show_time = $_POST['show_time'] ?? '';
        $hall_name = $_POST['hall_name'] ?? 'Hall 1';

        if (!$movie_id || empty($show_date) || empty($show_time)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields required']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO shows (movie_id, show_date, show_time, hall_name) VALUES (?,?,?,?)");
            $stmt->execute([$movie_id, $show_date, $show_time, $hall_name]);
            $show_id = $pdo->lastInsertId();
            generateSeats($pdo, $show_id);
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // DELETE MOVIE
    if ($action === 'delete') {
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $movie_id = (int)($_POST['movie_id'] ?? 0);
        try {
            // Delete ticket_seats linked to this movie's shows
            $pdo->prepare("DELETE ts FROM ticket_seats ts JOIN seats s ON ts.seat_id = s.seat_id JOIN shows sh ON s.show_id = sh.show_id WHERE sh.movie_id = ?")->execute([$movie_id]);
            // Delete tickets linked to this movie's shows
            $pdo->prepare("DELETE t FROM tickets t JOIN shows sh ON t.show_id = sh.show_id WHERE sh.movie_id = ?")->execute([$movie_id]);
            // Cascading delete handles shows and seats
            $pdo->prepare("DELETE FROM movies WHERE movie_id = ?")->execute([$movie_id]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Generate seats for a show
function generateSeats($pdo, $show_id) {
    $stmt = $pdo->prepare("INSERT INTO seats (show_id, row_label, seat_number, seat_type) VALUES (?,?,?,?)");
    $config = [
        'A' => 'vip', 'B' => 'vip',
        'C' => 'premium', 'D' => 'premium',
        'E' => 'standard', 'F' => 'standard', 'G' => 'standard', 'H' => 'standard'
    ];
    foreach ($config as $row => $type) {
        for ($i = 1; $i <= 12; $i++) {
            $stmt->execute([$show_id, $row, $i, $type]);
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
