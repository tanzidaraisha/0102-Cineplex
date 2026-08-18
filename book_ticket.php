<?php
session_start();
if (!isset($_SESSION['customer_id'])) { header("Location: customer_login.php"); exit; }
$preselect_movie = $_GET['movie'] ?? '';
?>
<?php $pageTitle = 'Book Ticket'; require_once 'includes/header.php'; ?>
<div class="container">
    </a>
            <a href="customer_dashboard.php" class="btn btn-outline btn-sm">← Dashboard</a>
        </div>

        <h1 class="page-title" style="font-size: 2rem;">🎟️ BOOK YOUR TICKETS</h1>

        <!-- Step Indicator -->
        <div class="steps" id="step-indicator">
            <div class="step active" id="step-1"><span class="step-num">1</span> Select Movie</div>
            <div class="step-line" id="line-1"></div>
            <div class="step" id="step-2"><span class="step-num">2</span> Choose Seats</div>
            <div class="step-line" id="line-2"></div>
            <div class="step" id="step-3"><span class="step-num">3</span> Payment</div>
            <div class="step-line" id="line-3"></div>
            <div class="step" id="step-4"><span class="step-num">4</span> Confirmed</div>
        </div>

        <!-- STEP 1: SELECT MOVIE & SHOWTIME -->
        <div id="section-1" class="card-flat animate-fade-up" style="max-width: 700px; margin: 0 auto;">
            <h2 style="color: var(--primary-dark); margin-bottom: 20px;">🎬 Select Movie & Showtime</h2>
            <div class="form-group">
                <label>Movie</label>
                <select id="movie-select" class="form-control">
                    <option value="">-- Choose a Movie --</option>
                </select>
            </div>
            <div id="movie-info" style="display:none; margin-bottom: 20px;"></div>
            <div class="form-group" id="showtime-group" style="display:none;">
                <label>Available Showtimes</label>
                <div id="showtimes-container" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
            </div>
            <input type="hidden" id="selected-show-id">
            <button class="btn btn-primary" id="btn-to-step2" style="width:100%; display:none;" onclick="goToStep(2)">Continue to Seat Selection →</button>
        </div>

        <!-- STEP 2: SEAT SELECTION -->
        <div id="section-2" style="display:none;" class="card-flat animate-fade-up" style="max-width: 800px; margin: 0 auto;">
            <h2 style="color: var(--primary-dark); margin-bottom: 10px; text-align:center;">🪑 Select Your Seats</h2>
            <p style="text-align:center; color:var(--text-light); margin-bottom: 20px;" id="show-info-display"></p>
            
            <!-- Cinema Screen -->
            <div class="cinema-screen">
                <div class="screen-display"></div>
                <div class="screen-label">Screen</div>
            </div>

            <!-- Seat Legend -->
            <div class="seat-legend">
                <div class="legend-item"><div class="legend-box" style="background:#e74c3c;"></div> VIP (৳Extra)</div>
                <div class="legend-item"><div class="legend-box" style="background:#f39c12;"></div> Premium</div>
                <div class="legend-item"><div class="legend-box" style="background:#4a9eda;"></div> Standard</div>
                <div class="legend-item"><div class="legend-box" style="background:#555; opacity:0.5;"></div> Booked</div>
                <div class="legend-item"><div class="legend-box" style="background:#2ed573;"></div> Selected</div>
            </div>

            <!-- Seat Grid -->
            <div class="seat-map" id="seat-grid"></div>

            <div class="booking-summary" id="booking-summary" style="display:none;">
                <h3 style="margin-bottom: 15px; color: var(--primary-dark);">📋 Booking Summary</h3>
                <div class="summary-row"><span class="label">Selected Seats</span><span class="value" id="sum-seats">-</span></div>
                <div class="summary-row"><span class="label">Quantity</span><span class="value" id="sum-qty">0</span></div>
                <div class="summary-row"><span class="label">Price per Ticket</span><span class="value" id="sum-price">-</span></div>
                <div class="summary-row"><span class="label summary-total">Total</span><span class="value summary-total" id="sum-total">৳0</span></div>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button class="btn btn-outline" onclick="goToStep(1)" style="flex:1;">← Back</button>
                <button class="btn btn-primary" id="btn-to-step3" disabled onclick="goToStep(3)" style="flex:2;">Proceed to Payment →</button>
            </div>
        </div>

        <!-- STEP 3: PAYMENT -->
        <div id="section-3" style="display:none;" class="card-flat animate-fade-up" style="max-width: 600px; margin: 0 auto;">
            <h2 style="color: var(--primary-dark); margin-bottom: 20px; text-align:center;">💳 Payment</h2>
            
            <div class="booking-summary" style="margin-bottom: 25px;">
                <div class="summary-row"><span class="label">Movie</span><span class="value" id="pay-movie">-</span></div>
                <div class="summary-row"><span class="label">Seats</span><span class="value" id="pay-seats">-</span></div>
                <div class="summary-row"><span class="label summary-total">Total Amount</span><span class="value summary-total" id="pay-total">৳0</span></div>
            </div>

            <!-- Payment Options Info -->
            <div style="background: rgba(26,111,181,0.05); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid var(--primary); text-align: center;">
                <h3 style="color: var(--primary-dark); margin-bottom: 10px;">🔒 Secure Checkout</h3>
                <p style="color: var(--text-mid); font-size: 0.95rem; margin-bottom: 15px;">You will be redirected to the <strong>SSLCommerz Secure Payment Gateway</strong>. You can pay using Cards, bKash, Nagad, Upay, or Net Banking.</p>
                <img src="https://securepay.sslcommerz.com/public/image/SSLCommerz-Pay-With-logo-All-Size-03.png" alt="SSLCommerz" style="max-width: 100%; height: auto; max-height: 50px;">
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button class="btn btn-outline" onclick="goToStep(2)" style="flex:1;">← Back</button>
                <button class="btn btn-success" id="btn-pay" onclick="processPayment()" style="flex:2;">🔒 Pay with SSLCommerz</button>
            </div>
        </div>

        <!-- STEP 4: CONFIRMATION -->
        <div id="section-4" style="display:none;" class="animate-scale">
            <div class="ticket-display">
                <div class="ticket-header">
                    <h3>🎬 0102 CINEPLEX</h3>
                    <p>Booking Confirmed!</p>
                </div>
                <div class="ticket-body">
                    <div class="ticket-row"><span style="color:#888;">Ticket ID</span><strong id="conf-id">#-</strong></div>
                    <div class="ticket-row"><span style="color:#888;">Movie</span><strong id="conf-movie">-</strong></div>
                    <div class="ticket-row"><span style="color:#888;">Showtime</span><strong id="conf-show">-</strong></div>
                    <div class="ticket-row"><span style="color:#888;">Seats</span><strong id="conf-seats">-</strong></div>
                    <div class="ticket-row"><span style="color:#888;">Total Paid</span><strong id="conf-total" style="color:var(--primary);">-</strong></div>
                    
                    <div class="ticket-divider">
                        <div class="circle circle-left"></div>
                        <div class="circle circle-right"></div>
                    </div>

                    <div class="ticket-qr">
                        <p style="font-size:0.85rem; color:#888; margin-bottom: 10px;">Show this QR code at entrance</p>
                        <img id="conf-qr" src="" alt="QR Code">
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 25px;">
                <p style="color: var(--success); font-weight: 600; margin-bottom: 15px;">✅ E-ticket has been sent to your email!</p>
                <a href="customer_dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                <a href="book_ticket.php" class="btn btn-outline" style="margin-left: 10px;">Book Another</a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading" style="display:none;">
        <div class="spinner" style="width:60px; height:60px; border-width:5px;"></div>
        <p style="font-weight: 600; color: var(--primary);" id="loading-text">Processing...</p>
    </div>

    <script>
    let selectedSeats = [];
    let moviePrice = 0;
    let movieTitle = '';
    let showDisplay = '';

    // Check for success/fail URL params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('fail')) {
        alert("Payment failed or was cancelled. Please try booking again.");
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (urlParams.has('success_ticket_id')) {
        const ticketId = urlParams.get('success_ticket_id');
        // Fetch ticket details
        fetch(`api/tickets.php?action=ticket_detail&ticket_id=${ticketId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('conf-id').textContent = '#' + data.ticket.ticket_id;
                    document.getElementById('conf-movie').textContent = data.ticket.title;
                    document.getElementById('conf-show').textContent = data.ticket.show_date_display + ' at ' + data.ticket.show_time_display;
                    document.getElementById('conf-seats').textContent = data.ticket.seat_details;
                    document.getElementById('conf-total').textContent = '৳' + parseFloat(data.ticket.total_price).toFixed(0);
                    document.getElementById('conf-qr').src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(data.ticket.qr_code);
                    goToStep(4);
                }
            });
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Load movies
    async function loadMovies() {
        const res = await fetch('api/movies.php?action=list');
        const movies = await res.json();
        const sel = document.getElementById('movie-select');
        movies.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.movie_id;
            opt.textContent = `${m.title} — ৳${parseFloat(m.price).toFixed(0)}`;
            opt.dataset.price = m.price;
            opt.dataset.title = m.title;
            opt.dataset.poster = m.poster_url || '';
            opt.dataset.genre = m.genre || '';
            opt.dataset.duration = m.duration || '';
            sel.appendChild(opt);
        });
        // Auto-select if from dashboard
        const preselect = '<?php echo $preselect_movie; ?>';
        if (preselect) {
            sel.value = preselect;
            sel.dispatchEvent(new Event('change'));
        }
    }
    loadMovies();

    // Movie Selection Change
    document.getElementById('movie-select').addEventListener('change', async function() {
        const movieId = this.value;
        const opt = this.options[this.selectedIndex];
        const infoBox = document.getElementById('movie-info');
        const showGroup = document.getElementById('showtime-group');
        document.getElementById('btn-to-step2').style.display = 'none';
        document.getElementById('selected-show-id').value = '';

        if (!movieId) {
            infoBox.style.display = 'none';
            showGroup.style.display = 'none';
            return;
        }

        moviePrice = parseFloat(opt.dataset.price);
        movieTitle = opt.dataset.title;

        // Show movie info
        const poster = opt.dataset.poster || 'https://via.placeholder.com/120x180/1a6fb5/ffffff?text=Poster';
        infoBox.innerHTML = `
            <div style="display:flex; gap:15px; align-items:center; background: linear-gradient(135deg, #f8fbff, #edf5fc); padding:15px; border-radius:12px;">
                <img src="${poster}" alt="${movieTitle}" style="width:80px; height:120px; object-fit:cover; border-radius:8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);" onerror="this.src='https://via.placeholder.com/80x120/1a6fb5/ffffff?text=Poster'">
                <div>
                    <h3 style="margin-bottom:4px;">${movieTitle}</h3>
                    <p style="color:var(--text-light); font-size:0.9rem;">${opt.dataset.genre} • ${opt.dataset.duration}</p>
                    <p style="color:var(--primary); font-weight:800; font-size:1.2rem; margin-top:5px;">৳${moviePrice.toFixed(0)} / ticket</p>
                </div>
            </div>`;
        infoBox.style.display = 'block';

        // Load showtimes
        const res = await fetch(`api/movies.php?action=get_shows&movie_id=${movieId}`);
        const shows = await res.json();
        const container = document.getElementById('showtimes-container');

        if (shows.length === 0) {
            container.innerHTML = '<p style="color: var(--text-light);">No available showtimes.</p>';
        } else {
            let html = '';
            shows.forEach(s => {
                html += `
                <div class="payment-option" data-id="${s.show_id}" data-display="${s.date_display} at ${s.time_display} (${s.hall_name})" onclick="selectShowtime(this)">
                    <strong style="font-size:0.9rem;">${s.date_display}</strong><br>
                    <span style="color:var(--primary); font-weight:700;">${s.time_display}</span><br>
                    <span style="font-size:0.8rem; color:var(--text-light);">${s.hall_name}</span>
                </div>`;
            });
            container.innerHTML = html;
        }
        showGroup.style.display = 'block';
    });

    // Select Showtime
    function selectShowtime(el) {
        document.querySelectorAll('#showtimes-container .payment-option').forEach(o => o.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('selected-show-id').value = el.dataset.id;
        showDisplay = el.dataset.display;
        document.getElementById('btn-to-step2').style.display = 'block';
    }

    // Step navigation
    function goToStep(step) {
        // Hide all sections
        for (let i = 1; i <= 4; i++) {
            document.getElementById('section-' + i).style.display = 'none';
        }
        // Update step indicator
        for (let i = 1; i <= 4; i++) {
            const s = document.getElementById('step-' + i);
            s.classList.remove('active', 'done');
            if (i < step) s.classList.add('done');
            if (i === step) s.classList.add('active');
        }
        for (let i = 1; i <= 3; i++) {
            const l = document.getElementById('line-' + i);
            l.classList.toggle('done', i < step);
        }
        // Show section
        const section = document.getElementById('section-' + step);
        section.style.display = 'block';
        section.classList.add('animate-fade-up');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Load seats when going to step 2
        if (step === 2) loadSeats();
        // Update payment summary
        if (step === 3) updatePaymentSummary();
    }

    // Load Seats
    async function loadSeats() {
        const showId = document.getElementById('selected-show-id').value;
        document.getElementById('show-info-display').textContent = `${movieTitle} — ${showDisplay}`;
        selectedSeats = [];
        updateSeatSummary();

        const res = await fetch(`api/movies.php?action=get_seats&show_id=${showId}`);
        const seats = await res.json();

        // Group by row
        const rows = {};
        seats.forEach(s => {
            if (!rows[s.row_label]) rows[s.row_label] = [];
            rows[s.row_label].push(s);
        });

        let html = '';
        for (const [row, seatList] of Object.entries(rows)) {
            html += `<div class="seat-row"><span class="row-label">${row}</span>`;
            seatList.forEach((s, idx) => {
                // Add gap in middle (after seat 6)
                if (idx === 6) html += '<div class="seat-gap"></div>';
                const bookedClass = s.status === 'booked' ? 'booked' : '';
                html += `<div class="seat ${s.seat_type} ${bookedClass}" data-id="${s.seat_id}" data-row="${s.row_label}" data-num="${s.seat_number}" onclick="toggleSeat(this)">${s.seat_number}</div>`;
            });
            html += `<span class="row-label">${row}</span></div>`;
        }
        document.getElementById('seat-grid').innerHTML = html;
    }

    // Toggle Seat
    function toggleSeat(el) {
        if (el.classList.contains('booked')) return;
        const id = parseInt(el.dataset.id);
        const label = el.dataset.row + el.dataset.num;

        if (el.classList.contains('selected')) {
            el.classList.remove('selected');
            selectedSeats = selectedSeats.filter(s => s.id !== id);
        } else {
            el.classList.add('selected');
            // Determine dynamic price
            let type = 'standard';
            let price = moviePrice;
            if (el.classList.contains('premium')) { type = 'premium'; price = moviePrice + 100; }
            if (el.classList.contains('vip')) { type = 'vip'; price = moviePrice + 300; }
            
            selectedSeats.push({ id, label, type, price });
        }
        updateSeatSummary();
    }

    function updateSeatSummary() {
        const qty = selectedSeats.length;
        const total = selectedSeats.reduce((sum, s) => sum + s.price, 0);
        const labels = selectedSeats.map(s => s.label).join(', ') || '-';

        document.getElementById('sum-seats').textContent = labels;
        document.getElementById('sum-qty').textContent = qty;
        
        if (qty > 0) {
            // Show dynamic range or exact total if multiple types
            document.getElementById('sum-price').textContent = 'Dynamic';
        } else {
            document.getElementById('sum-price').textContent = '৳' + moviePrice.toFixed(0);
        }
        
        document.getElementById('sum-total').textContent = '৳' + total.toFixed(0);
        document.getElementById('booking-summary').style.display = qty > 0 ? 'block' : 'none';
        document.getElementById('btn-to-step3').disabled = qty === 0;
    }

    function updatePaymentSummary() {
        const total = selectedSeats.reduce((sum, s) => sum + s.price, 0);
        document.getElementById('pay-movie').textContent = movieTitle;
        document.getElementById('pay-seats').textContent = selectedSeats.map(s => s.label).join(', ');
        document.getElementById('pay-total').textContent = '৳' + total.toFixed(0);
    }

    // Process Payment
    async function processPayment() {
        const showId = document.getElementById('selected-show-id').value;
        if (selectedSeats.length === 0 || !showId) return;

        // Show loading
        const loading = document.getElementById('loading');
        loading.style.display = 'flex';
        document.getElementById('loading-text').textContent = 'Connecting to Payment Gateway...';

        const formData = new FormData();
        formData.append('action', 'book');
        formData.append('show_id', showId);
        selectedSeats.forEach(s => formData.append('seat_ids[]', s.id));

        try {
            const res = await fetch('api/tickets.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success' && data.gateway_url) {
                // Redirect to SSLCommerz
                window.location.href = data.gateway_url;
            } else {
                loading.style.display = 'none';
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            loading.style.display = 'none';
            alert('Failed to initiate payment. Please try again.');
        }
    }
    </script>
<?php require_once 'includes/footer.php'; ?>
