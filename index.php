<?php
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: admin_dashboard.php"); exit; }
if (isset($_SESSION['customer_id'])) { header("Location: customer_dashboard.php"); exit; }
?>
<?php $pageTitle = 'Home'; require_once 'includes/header.php'; ?>
    <div class="container">

        <h1 class="page-title" style="font-size: 3.2rem; margin-top: 60px;">WELCOME TO 0102 CINEPLEX</h1>
        <p class="page-subtitle" style="font-size: 1.3rem;">Your ultimate movie experience starts here!! 🎬</p>

        <p class="text-center" style="font-weight:700; font-size:1.3rem; margin-bottom: 10px; animation: fadeInUp 0.6s ease;">Logging in as —</p>

        <div class="role-selection animate-fade-up delay-2">
            <a href="admin_login.php" class="btn btn-role">🛡️ ADMIN</a>
            <a href="customer_login.php" class="btn btn-role">🎟️ CUSTOMER</a>
        </div>

        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80" alt="Cinema">
        </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
