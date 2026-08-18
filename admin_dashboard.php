<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
?>
<?php $pageTitle = 'Admin Dashboard'; require_once 'includes/header.php'; ?>
<div class="container">

        <h1 class="page-title" style="font-size: 2rem;">🛡️ ADMIN DASHBOARD</h1>

        <div class="dashboard-grid">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-menu">
                    <button class="sidebar-btn active" onclick="switchTab('movies')">
                        <span class="icon">🎬</span> View Movies
                    </button>
                    <button class="sidebar-btn" onclick="switchTab('add')">
                        <span class="icon">➕</span> Add New Movie
                    </button>
                    <button class="sidebar-btn" onclick="switchTab('shows')">
                        <span class="icon">🕐</span> Manage Shows
                    </button>
                </div>
                <div style="margin-top: 30px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/6195/6195699.png" alt="Admin" style="width:100%; max-width:200px; display:block; margin:0 auto; animation: float 4s ease-in-out infinite;">
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- VIEW MOVIES TAB -->
                <div id="tab-movies">
                    <div class="search-bar">
                        <input type="text" id="search-movie" class="search-input" placeholder="🔍 Search movies by title...">
                        <button class="btn btn-primary btn-sm" onclick="switchTab('add')">+ Add Movie</button>
                    </div>
                    <div id="movies-container" class="movies-grid">
                        <!-- Movies loaded here -->
                    </div>
                </div>

                <!-- ADD MOVIE TAB -->
                <div id="tab-add" style="display:none;">
                    <div class="card-flat" style="max-width: 600px;">
                        <h2 style="color: var(--primary-dark); margin-bottom: 25px;">➕ Add New Movie</h2>
                        <div id="add-alert"></div>
                        <form id="add-movie-form">
                            <div class="form-group">
                                <label>Movie Title *</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. The Matrix" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Genre</label>
                                    <input type="text" name="genre" class="form-control" placeholder="e.g. Action / Sci-Fi">
                                </div>
                                <div class="form-group">
                                    <label>Duration</label>
                                    <input type="text" name="duration" class="form-control" placeholder="e.g. 2h 30min">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" placeholder="Movie plot summary..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Poster Image URL</label>
                                <input type="url" name="poster_url" class="form-control" placeholder="https://image.tmdb.org/t/p/w500/...">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Ticket Price (BDT) *</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="350" required>
                                </div>
                                <div class="form-group">
                                    <label>Rating</label>
                                    <select name="rating" class="form-control">
                                        <option value="PG-13">PG-13</option>
                                        <option value="PG">PG</option>
                                        <option value="R">R</option>
                                        <option value="G">G</option>
                                    </select>
                                </div>
                            </div>
                            <h3 style="margin: 20px 0 15px; color: var(--primary-dark);">Show Schedule</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Show Date *</label>
                                    <input type="date" name="show_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Show Time *</label>
                                    <input type="time" name="show_time" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Hall</label>
                                    <select name="hall_name" class="form-control">
                                        <option>Hall 1</option>
                                        <option>Hall 2</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 10px;">🎬 SAVE MOVIE</button>
                        </form>
                    </div>
                </div>

                <!-- MANAGE SHOWS TAB -->
                <div id="tab-shows" style="display:none;">
                    <div class="card-flat">
                        <h2 style="color: var(--primary-dark); margin-bottom: 25px;">🕐 Add Showtime to Existing Movie</h2>
                        <div id="show-alert"></div>
                        <form id="add-show-form">
                            <div class="form-group">
                                <label>Select Movie</label>
                                <select name="movie_id" id="show-movie-select" class="form-control" required>
                                    <option value="">-- Select Movie --</option>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Show Date *</label>
                                    <input type="date" name="show_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Show Time *</label>
                                    <input type="time" name="show_time" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Hall</label>
                                    <select name="hall_name" class="form-control">
                                        <option>Hall 1</option>
                                        <option>Hall 2</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Showtime</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 400px; text-align: center;">
            <h3 style="color: var(--danger); margin-bottom: 15px;">⚠️ Delete Movie?</h3>
            <p style="margin-bottom: 25px; color: var(--text-mid);">This will permanently delete the movie and all its shows, seats, and bookings.</p>
            <input type="hidden" id="delete-movie-id">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                <button class="btn btn-outline" onclick="document.getElementById('deleteModal').classList.remove('active')">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    // Tab Switching
    function switchTab(tab) {
        document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
        document.getElementById('tab-' + tab).style.display = 'block';
        document.querySelectorAll('.sidebar-btn').forEach(b => b.classList.remove('active'));
        event.target.closest('.sidebar-btn')?.classList.add('active');
        if (tab === 'shows') loadMovieSelect();
    }

    // Load Movies
    async function loadMovies(search = '') {
        const container = document.getElementById('movies-container');
        container.innerHTML = '<div class="text-center" style="grid-column: 1/-1; padding: 40px;"><div class="spinner"></div></div>';
        try {
            const res = await fetch(`api/movies.php?action=list&search=${encodeURIComponent(search)}`);
            const movies = await res.json();
            if (movies.length === 0) {
                container.innerHTML = '<p style="grid-column:1/-1; text-align:center; padding:40px; color: var(--text-light);">No movies found. Add one!</p>';
                return;
            }
            let html = '';
            movies.forEach((m, i) => {
                const poster = m.poster_url || 'https://via.placeholder.com/300x450/1a6fb5/ffffff?text=No+Poster';
                html += `
                <div class="movie-card" style="animation-delay: ${i * 0.1}s;">
                    <div class="poster-wrapper">
                        <img src="${poster}" alt="${m.title}" class="poster" onerror="this.src='https://via.placeholder.com/300x450/1a6fb5/ffffff?text=No+Poster'">
                        <div class="poster-overlay">
                            <p style="font-size:0.85rem;">${m.description || 'No description available.'}</p>
                        </div>
                    </div>
                    <div class="info">
                        <h3>${m.title} <span class="rating-badge">${m.rating || 'PG-13'}</span></h3>
                        <p class="genre">${m.genre || 'N/A'} • ${m.duration || 'N/A'}</p>
                        <p class="price">৳${parseFloat(m.price).toFixed(2)}</p>
                        <p style="font-size:0.8rem; color: var(--text-light); margin-top: 5px;">Shows: ${m.show_count || 0}</p>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-danger btn-sm" onclick="showDeleteModal(${m.movie_id})" style="flex:1;">🗑️ Delete</button>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<p style="grid-column:1/-1; text-align:center; padding:40px; color:var(--danger);">Error loading movies. Check database connection.</p>';
        }
    }

    // Search
    let searchTimeout;
    document.getElementById('search-movie').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadMovies(e.target.value), 300);
    });

    // Add Movie
    document.getElementById('add-movie-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        const formData = new FormData(this);
        formData.append('action', 'add');
        try {
            const res = await fetch('api/movies.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('add-alert').innerHTML = '<div class="alert alert-success">✅ Movie added successfully!</div>';
                this.reset();
                loadMovies();
                setTimeout(() => document.getElementById('add-alert').innerHTML = '', 3000);
            } else {
                document.getElementById('add-alert').innerHTML = `<div class="alert alert-danger">⚠️ ${data.message}</div>`;
            }
        } catch (err) {
            document.getElementById('add-alert').innerHTML = '<div class="alert alert-danger">⚠️ Error saving movie</div>';
        }
        btn.disabled = false;
        btn.textContent = '🎬 SAVE MOVIE';
    });

    // Delete
    function showDeleteModal(id) {
        document.getElementById('delete-movie-id').value = id;
        document.getElementById('deleteModal').classList.add('active');
    }
    async function confirmDelete() {
        const id = document.getElementById('delete-movie-id').value;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('movie_id', id);
        try {
            const res = await fetch('api/movies.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('deleteModal').classList.remove('active');
                loadMovies();
            } else {
                alert(data.message);
            }
        } catch (e) { alert('Error deleting movie'); }
    }

    // Load movie select for shows tab
    async function loadMovieSelect() {
        const res = await fetch('api/movies.php?action=list');
        const movies = await res.json();
        const sel = document.getElementById('show-movie-select');
        sel.innerHTML = '<option value="">-- Select Movie --</option>';
        movies.forEach(m => sel.innerHTML += `<option value="${m.movie_id}">${m.title}</option>`);
    }

    // Add Show
    document.getElementById('add-show-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add_show');
        try {
            const res = await fetch('api/movies.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('show-alert').innerHTML = '<div class="alert alert-success">✅ Showtime added with seats!</div>';
                this.reset();
                loadMovies();
            } else {
                document.getElementById('show-alert').innerHTML = `<div class="alert alert-danger">⚠️ ${data.message}</div>`;
            }
        } catch (e) {
            document.getElementById('show-alert').innerHTML = '<div class="alert alert-danger">⚠️ Error</div>';
        }
    });

    // Init
    loadMovies();
    </script>
<?php require_once 'includes/footer.php'; ?>
