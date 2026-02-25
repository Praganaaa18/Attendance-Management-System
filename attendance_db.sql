-- Attendance Tracking System Database Schema
-- Run this SQL in phpMyAdmin or MySQL to create the required tables

-- Create database for attendance tracking
CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;

-- Attendance table: stores daily attendance records
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    attendance_date DATE NOT NULL,
    attendance_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevent duplicate entries for same user on same day
    UNIQUE KEY unique_daily_attendance (username, attendance_date)
);

-- Sample query to view attendance records
-- SELECT * FROM attendance ORDER BY attendance_date DESC, attendance_time DESC;
