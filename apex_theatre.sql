/* For the creation of the database and the tables just import this script to phpMyAdmin*/

CREATE DATABASE IF NOT EXISTS apex_theatre;
USE apex_theatre;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE shows (
    show_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    price DECIMAL(8,2) NOT NULL
);

CREATE TABLE performances (
    perf_id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    perf_date DATE NOT NULL,
    perf_time TIME NOT NULL
);

CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    perf_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    status ENUM('available', 'booked') DEFAULT 'available'
);

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    perf_id INT NOT NULL,
    seats_booked VARCHAR(100) NOT NULL,
    total_price DECIMAL(8,2) NOT NULL
);