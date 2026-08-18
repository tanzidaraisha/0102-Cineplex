CREATE DATABASE IF NOT EXISTS cineplex_ticketing_db;
USE cineplex_ticketing_db;

DROP TABLE IF EXISTS ticket_seats;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS shows;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    duration VARCHAR(50),
    description TEXT,
    poster_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL DEFAULT 250.00,
    rating VARCHAR(10) DEFAULT 'PG-13',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE shows (
    show_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    show_date DATE NOT NULL,
    show_time TIME NOT NULL,
    hall_name VARCHAR(50) DEFAULT 'Hall 1',
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    row_label CHAR(1) NOT NULL,
    seat_number INT NOT NULL,
    seat_type ENUM('standard','premium','vip') DEFAULT 'standard',
    status ENUM('available','booked') DEFAULT 'available',
    FOREIGN KEY (show_id) REFERENCES shows(show_id) ON DELETE CASCADE,
    UNIQUE KEY unique_seat (show_id, row_label, seat_number)
);

CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    show_id INT NOT NULL,
    quantity INT NOT NULL,
    seat_details TEXT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('booked','cancelled') DEFAULT 'booked',
    payment_method VARCHAR(50) DEFAULT 'card',
    qr_code TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (show_id) REFERENCES shows(show_id)
);

CREATE TABLE ticket_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    seat_id INT NOT NULL,
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE
);

-- Default admin (password: admin123)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$ENp1FcF9JbjQUHFkWK3QJu4e2F8dPvu32hN4aXftGOSdJAg1ZKiXC');

-- Sample movies
INSERT INTO movies (title, genre, duration, description, poster_url, price, rating) VALUES
('Inception', 'Sci-Fi / Thriller', '2h 28min', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 'https://image.tmdb.org/t/p/w500/ljsZTbVsrQSqZgWeep2B1QiDKuh.jpg', 350.00, 'PG-13'),
('The Dark Knight', 'Action / Crime', '2h 32min', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological tests of his ability to fight injustice.', 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911kpUpYEGJYLKW.jpg', 400.00, 'PG-13'),
('Interstellar', 'Sci-Fi / Adventure', '2h 49min', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanitys survival.', 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 350.00, 'PG-13'),
('Avengers: Endgame', 'Action / Sci-Fi', '3h 1min', 'After the devastating events of Infinity War, the universe is in ruins. With the help of remaining allies, the Avengers assemble once more to reverse Thanos actions.', 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg', 450.00, 'PG-13'),
('Joker', 'Crime / Drama', '2h 2min', 'In Gotham City, mentally troubled comedian Arthur Fleck is disregarded and mistreated by society. He then embarks on a downward spiral of revolution and bloody crime.', 'https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg', 300.00, 'R');

-- Shows for each movie (next 3 days, multiple times)
INSERT INTO shows (movie_id, show_date, show_time, hall_name) VALUES
(1, CURDATE(), '10:00:00', 'Hall 1'),
(1, CURDATE(), '14:00:00', 'Hall 1'),
(1, CURDATE(), '18:00:00', 'Hall 2'),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', 'Hall 1'),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', 'Hall 2'),
(2, CURDATE(), '11:00:00', 'Hall 2'),
(2, CURDATE(), '15:00:00', 'Hall 1'),
(2, CURDATE(), '19:00:00', 'Hall 2'),
(2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', 'Hall 1'),
(3, CURDATE(), '10:30:00', 'Hall 1'),
(3, CURDATE(), '14:30:00', 'Hall 2'),
(3, CURDATE(), '20:00:00', 'Hall 1'),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', 'Hall 2'),
(4, CURDATE(), '12:00:00', 'Hall 2'),
(4, CURDATE(), '16:00:00', 'Hall 1'),
(4, CURDATE(), '21:00:00', 'Hall 2'),
(4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'Hall 1'),
(5, CURDATE(), '13:00:00', 'Hall 1'),
(5, CURDATE(), '17:00:00', 'Hall 2'),
(5, CURDATE(), '21:30:00', 'Hall 1'),
(5, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', 'Hall 2');

-- Generate seats for each show (Rows A-H, 12 seats each. A-B = VIP, C-D = Premium, E-H = Standard)
DELIMITER //
CREATE PROCEDURE generate_seats()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE s_id INT;
    DECLARE cur CURSOR FOR SELECT show_id FROM shows;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO s_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- VIP rows
        INSERT INTO seats (show_id, row_label, seat_number, seat_type) 
        SELECT s_id, r, n, 'vip' FROM 
            (SELECT 'A' AS r UNION SELECT 'B') rows_t,
            (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) nums;
        
        -- Premium rows
        INSERT INTO seats (show_id, row_label, seat_number, seat_type) 
        SELECT s_id, r, n, 'premium' FROM 
            (SELECT 'C' AS r UNION SELECT 'D') rows_t,
            (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) nums;
        
        -- Standard rows
        INSERT INTO seats (show_id, row_label, seat_number, seat_type) 
        SELECT s_id, r, n, 'standard' FROM 
            (SELECT 'E' AS r UNION SELECT 'F' UNION SELECT 'G' UNION SELECT 'H') rows_t,
            (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) nums;
    END LOOP;
    CLOSE cur;
END //
DELIMITER ;

CALL generate_seats();
DROP PROCEDURE generate_seats;
