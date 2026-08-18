<?php
// Determine if user is logged in and their role
$isAdmin = isset($_SESSION['admin_id']);
$isCustomer = isset($_SESSION['customer_id']);
$isLoggedIn = $isAdmin || $isCustomer;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? '0102 Cineplex'; ?> - 0102 Cineplex</title>
    <meta name="description" content="0102 Cineplex - Your ultimate movie experience. Book tickets online, choose your seats, and enjoy the show!">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3163/3163478.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ===== MAIN HEADER / NAVBAR ===== -->
<header class="site-header" id="siteHeader">
    <div class="header-inner">
        <a href="index.php" class="header-logo">
            <img src="https://cdn-icons-png.flaticon.com/512/3163/3163478.png" alt="0102 Cineplex">
            <div class="header-logo-text">
                <span class="logo-num">0102</span>
                <span class="logo-name">Cineplex</span>
            </div>
        </a>

        <nav class="header-nav" id="mainNav">
            <?php if ($isAdmin): ?>
                <a href="admin_dashboard.php" class="nav-link <?php echo $currentPage === 'admin_dashboard' ? 'active' : ''; ?>">🎬 Movies</a>
            <?php elseif ($isCustomer): ?>
                <a href="customer_dashboard.php" class="nav-link <?php echo $currentPage === 'customer_dashboard' ? 'active' : ''; ?>">🏠 Home</a>
                <a href="book_ticket.php" class="nav-link <?php echo $currentPage === 'book_ticket' ? 'active' : ''; ?>">🎟️ Book Tickets</a>
                <a href="my_tickets.php" class="nav-link <?php echo $currentPage === 'my_tickets' ? 'active' : ''; ?>">📋 My Tickets</a>
                <a href="cancel_ticket.php" class="nav-link <?php echo $currentPage === 'cancel_ticket' ? 'active' : ''; ?>">❌ Cancel</a>
                <a href="resend_ticket.php" class="nav-link <?php echo $currentPage === 'resend_ticket' ? 'active' : ''; ?>">📧 Resend</a>
            <?php else: ?>
                <a href="index.php" class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a>
                <a href="customer_login.php" class="nav-link <?php echo $currentPage === 'customer_login' ? 'active' : ''; ?>">Movies</a>
                <a href="#footer-contact" class="nav-link">Contact</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <span class="user-badge">
                    <?php if ($isAdmin): ?>
                        🛡️ <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                    <?php else: ?>
                        👤 <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'User'); ?>
                    <?php endif; ?>
                </span>
                <a href="api/logout.php" class="btn btn-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="customer_login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="customer_register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>

            <button class="mobile-toggle" id="mobileToggle" onclick="document.getElementById('mainNav').classList.toggle('open')">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Spacer for fixed header -->
<div class="header-spacer"></div>

<main class="site-main">
