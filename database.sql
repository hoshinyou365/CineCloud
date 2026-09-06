-- CineCloud Movie Ticket Booking Database
-- MySQL / Amazon RDS MySQL compatible

CREATE DATABASE IF NOT EXISTS cinecloud
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cinecloud;

CREATE TABLE IF NOT EXISTS movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    duration_minutes INT NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    movie_id INT NOT NULL,
    show_date DATE NOT NULL,
    show_time VARCHAR(20) NOT NULL,
    ticket_quantity INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_status VARCHAR(20) NOT NULL DEFAULT 'Confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_movie
        FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

INSERT INTO movies (title, genre, duration_minutes, ticket_price, image_url)
SELECT * FROM (
    SELECT 'Interstellar','Sci-Fi',169,18.00,'images/interstellar.jpg'
    UNION ALL SELECT 'The Batman','Action',176,16.00,'images/batman.jpg'
    UNION ALL SELECT 'Inside Out 2','Animation',96,14.00,'images/insideout2.jpg'
    UNION ALL SELECT 'Top Gun: Maverick','Action',130,17.00,'images/topgun.jpg'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM movies LIMIT 1);

-- Useful verification:
-- SELECT * FROM movies;
-- SELECT b.booking_id,b.customer_name,b.customer_email,m.title,b.show_date,
--        b.show_time,b.ticket_quantity,b.total_amount,b.booking_status,b.created_at
-- FROM bookings b JOIN movies m ON b.movie_id=m.movie_id
-- ORDER BY b.created_at DESC;


CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Demo accounts.
-- Passwords are plain text only for this classroom/demo project.
-- Change these after testing if the project is used beyond the assignment.
INSERT INTO users (full_name,email,password_hash,role)
SELECT 'Demo Customer','customer@cinecloud.com','Customer123!','Customer'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='customer@cinecloud.com');

INSERT INTO users (full_name,email,password_hash,role)
SELECT 'Demo Admin','admin@cinecloud.com','Admin123!','Admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='admin@cinecloud.com');
