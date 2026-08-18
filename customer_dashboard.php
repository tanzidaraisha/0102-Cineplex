<?php
session_start();
if (!isset($_SESSION['customer_id'])) { header("Location: customer_login.php"); exit; }
$name = htmlspecialchars($_SESSION['customer_name']);
?>
<?php $pageTitle = 'Customer Dashboard'; require_once 'includes/header.php'; ?>
<div class="container">

        <h1 class="page-title" style="font-size: 2.2rem;">Welcome, <?php echo $name; ?>! 🎬</h1>
        <p class="page-subtitle">Ready for your next movie? Select an option below.</p>

        <div class="dashboard-grid">
            <!-- Sidebar Menu -->
            <div class="sidebar">
                <div class="sidebar-menu">
                    <a href="book_ticket.php" class="sidebar-btn">
                        <span class="icon">🎟️</span> Book Ticket
                    </a>
                    <a href="cancel_ticket.php" class="sidebar-btn">
                        <span class="icon">❌</span> Cancel Ticket
                    </a>
                    <a href="resend_ticket.php" class="sidebar-btn">
                        <span class="icon">📧</span> Resend Ticket
                    </a>
                    <a href="my_tickets.php" class="sidebar-btn">
                        <span class="icon">📋</span> My Tickets
                    </a>
                    <a href="api/logout.php" class="sidebar-btn">
                        <span class="icon">🚪</span> Exit
                    </a>
                </div>
            </div>

            <!-- Main: Currently Streaming -->
            <div class="main-content">
                <div class="currently-streaming">
                    <h2>🔥 Currently Streaming</h2>
                    <div id="movies-showcase" class="movies-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    async function loadMovies() {
        const container = document.getElementById('movies-showcase');
        container.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:40px;"><div class="spinner"></div></div>';
        try {
            const res = await fetch('api/movies.php?action=list');
            const movies = await res.json();
            if (movies.length === 0) {
                container.innerHTML = '<p style="grid-column:1/-1; text-align:center; color: var(--text-light); padding: 40px;">No movies currently streaming.</p>';
                return;
            }
            let html = '';
            movies.forEach((m, i) => {
                const poster = m.poster_url || 'https://via.placeholder.com/300x450/1a6fb5/ffffff?text=No+Poster';
                html += `
                <div class="movie-card" style="animation-delay: ${i * 0.1}s; cursor: pointer;" onclick="window.location='book_ticket.php?movie=${m.movie_id}'">
                    <div class="poster-wrapper">
                        <img src="${poster}" alt="${m.title}" class="poster" onerror="this.src='https://via.placeholder.com/300x450/1a6fb5/ffffff?text=No+Poster'">
                        <div class="poster-overlay">
                            <p style="font-size:0.85rem;">${m.description || ''}</p>
                            <p style="margin-top:8px; font-weight:700;">Click to Book →</p>
                        </div>
                    </div>
                    <div class="info">
                        <h3>${m.title} <span class="rating-badge">${m.rating || 'PG-13'}</span></h3>
                        <p class="genre">${m.genre || ''} • ${m.duration || ''}</p>
                        <p class="price">৳${parseFloat(m.price).toFixed(0)}</p>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<p style="grid-column:1/-1; text-align:center; color:var(--danger);">Error loading movies.</p>';
        }
    }
    loadMovies();
    </script>
<?php require_once 'includes/footer.php'; ?>
