-- Hospital Management System Database Setup
-- Run this script in phpMyAdmin or MySQL Workbench

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS hospital_db;
USE hospital_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('doctor', 'staff') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    doctor_id INT NULL,
    staff_id INT NULL
);

-- Create department table
CREATE TABLE IF NOT EXISTS department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL,
    location VARCHAR(45) NOT NULL
);

-- Create doctor table
CREATE TABLE IF NOT EXISTS doctor (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    specialization VARCHAR(20) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    department_id INT,
    user_id INT,
    FOREIGN KEY (department_id) REFERENCES department(department_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create staff table
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(45) NOT NULL,
    role ENUM('Nurse', 'Cleaner', 'Receptionist', 'Accountant') NOT NULL,
    shift ENUM('Morning', 'Evening', 'Night') NOT NULL,
    department_id INT,
    user_id INT,
    FOREIGN KEY (department_id) REFERENCES department(department_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create patient table
CREATE TABLE IF NOT EXISTS patient (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    DOB DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address VARCHAR(45),
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create room table
CREATE TABLE IF NOT EXISTS room (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_type ENUM('General', 'Private', 'ICU') NOT NULL,
    room_status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    daily_cost DECIMAL(10,2) NOT NULL
);

-- Create bed table
CREATE TABLE IF NOT EXISTS bed (
    bed_id INT AUTO_INCREMENT PRIMARY KEY,
    bed_no VARCHAR(5) NOT NULL,
    bed_type ENUM('Standard', 'ICU', 'Emergency') NOT NULL,
    bed_status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    room_id INT,
    FOREIGN KEY (room_id) REFERENCES room(room_id)
);

-- Create admission table
CREATE TABLE IF NOT EXISTS admission (
    admission_id INT AUTO_INCREMENT PRIMARY KEY,
    admission_date DATETIME NOT NULL,
    discharge_date DATETIME NULL,
    patient_id INT,
    bed_id INT,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
    FOREIGN KEY (bed_id) REFERENCES bed(bed_id)
);

-- Create appointment table
CREATE TABLE IF NOT EXISTS appointment (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_date DATETIME NOT NULL,
    consultation_fee DECIMAL(10,2) NOT NULL,
    doctor_id INT,
    appointment_status ENUM('Scheduled', 'Completed', 'Cancelled', 'No show') DEFAULT 'Scheduled',
    patient_id INT,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id),
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

-- Create treatment table
CREATE TABLE IF NOT EXISTS treatment (
    treatment_id INT AUTO_INCREMENT PRIMARY KEY,
    notes VARCHAR(45),
    treatment_date DATETIME NOT NULL,
    treatment_fee DECIMAL(10,2) NOT NULL,
    patient_id INT,
    doctor_id INT,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id)
);

-- Create medicine table
CREATE TABLE IF NOT EXISTS medicine (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    dosage VARCHAR(10) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    medicine_price DECIMAL(10,2) NOT NULL
);

-- Create prescription table
CREATE TABLE IF NOT EXISTS prescription (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    quantity INT NOT NULL,
    dosage_instructions VARCHAR(45) NOT NULL,
    treatment_id INT,
    medicine_id INT,
    FOREIGN KEY (treatment_id) REFERENCES treatment(treatment_id),
    FOREIGN KEY (medicine_id) REFERENCES medicine(medicine_id)
);

-- Create lab_test table
CREATE TABLE IF NOT EXISTS lab_test (
    test_id INT AUTO_INCREMENT PRIMARY KEY,
    test_type VARCHAR(45) NOT NULL,
    results VARCHAR(20),
    test_date DATETIME NOT NULL,
    test_cost DECIMAL(10,2) NOT NULL,
    notes VARCHAR(100),
    patient_id INT,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

-- Create payment table
CREATE TABLE IF NOT EXISTS payment (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Insurance') NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Declined') DEFAULT 'Pending',
    payment_date DATETIME NOT NULL,
    patient_id INT,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

-- Create medical_history table
CREATE TABLE IF NOT EXISTS medical_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    condition VARCHAR(45) NOT NULL,
    diagnosis_date DATETIME NOT NULL,
    notes VARCHAR(100),
    patient_id INT,
    FOREIGN KEY (patient_id) REFERENCES patient(patient_id)
);

-- Insert sample data

-- Insert departments
INSERT INTO department (name, location) VALUES
('General Medicine', 'First Floor'),
('Cardiology', 'Second Floor'),
('Emergency', 'Ground Floor'),
('Surgery', 'Third Floor'),
('Pediatrics', 'Second Floor');

-- Insert rooms
INSERT INTO room (room_type, room_status, daily_cost) VALUES
('General', 'Available', 100.00),
('General', 'Available', 100.00),
('Private', 'Available', 200.00),
('Private', 'Available', 200.00),
('ICU', 'Available', 500.00),
('ICU', 'Available', 500.00);

-- Insert beds
INSERT INTO bed (bed_no, bed_type, bed_status, room_id) VALUES
('A1', 'Standard', 'Available', 1),
('A2', 'Standard', 'Available', 1),
('B1', 'Standard', 'Available', 2),
('B2', 'Standard', 'Available', 2),
('C1', 'Standard', 'Available', 3),
('C2', 'Standard', 'Available', 3),
('D1', 'Standard', 'Available', 4),
('D2', 'Standard', 'Available', 4),
('E1', 'ICU', 'Available', 5),
('E2', 'ICU', 'Available', 5),
('F1', 'ICU', 'Available', 6),
('F2', 'ICU', 'Available', 6);

-- Insert doctors
INSERT INTO doctor (name, specialization, phone, department_id) VALUES
('Dr. John Smith', 'Cardiology', '555-0101', 2),
('Dr. Sarah Johnson', 'General Medicine', '555-0102', 1),
('Dr. Mike Wilson', 'Emergency Medicine', '555-0103', 3),
('Dr. Emily Brown', 'Surgery', '555-0104', 4),
('Dr. David Lee', 'Pediatrics', '555-0105', 5);

-- Insert staff
INSERT INTO staff (name, role, shift, department_id) VALUES
('Jane Doe', 'Receptionist', 'Morning', 1),
('Mike Johnson', 'Nurse', 'Evening', 1),
('Sarah Wilson', 'Accountant', 'Morning', 1),
('Tom Brown', 'Nurse', 'Night', 2),
('Lisa Davis', 'Cleaner', 'Morning', 1);

-- Insert users (password is 'password' for all)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_active, doctor_id, staff_id) VALUES
('doctor1', 'doctor@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'John', 'Smith', 1, 1, NULL),
('receptionist1', 'reception@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Jane', 'Doe', 1, NULL, 1),
('nurse1', 'nurse@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Mike', 'Johnson', 1, NULL, 2),
('accountant1', 'accounting@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Sarah', 'Wilson', 1, NULL, 3);

-- Insert sample patients
INSERT INTO patient (name, DOB, gender, address, phone) VALUES
('Alice Johnson', '1985-03-15', 'Female', '123 Main St, City', '555-1001'),
('Bob Smith', '1978-07-22', 'Male', '456 Oak Ave, City', '555-1002'),
('Carol Davis', '1992-11-08', 'Female', '789 Pine Rd, City', '555-1003'),
('David Wilson', '1965-05-30', 'Male', '321 Elm St, City', '555-1004'),
('Eva Brown', '1988-09-12', 'Female', '654 Maple Dr, City', '555-1005');

-- Insert sample medicines
INSERT INTO medicine (name, dosage, stock_quantity, medicine_price) VALUES
('Paracetamol', '500mg', 100, 5.00),
('Ibuprofen', '400mg', 80, 8.50),
('Amoxicillin', '250mg', 60, 12.00),
('Aspirin', '100mg', 120, 3.50),
('Vitamin D', '1000IU', 90, 15.00);

-- Insert sample appointments
INSERT INTO appointment (appointment_date, consultation_fee, doctor_id, appointment_status, patient_id) VALUES
('2024-01-15 09:00:00', 50.00, 1, 'Scheduled', 1),
('2024-01-15 10:30:00', 50.00, 2, 'Completed', 2),
('2024-01-15 14:00:00', 75.00, 1, 'Scheduled', 3),
('2024-01-16 11:00:00', 50.00, 2, 'Scheduled', 4),
('2024-01-16 15:30:00', 75.00, 1, 'Scheduled', 5);

-- Insert sample treatments
INSERT INTO treatment (notes, treatment_date, treatment_fee, patient_id, doctor_id) VALUES
('Regular checkup and consultation', '2024-01-15 10:30:00', 100.00, 2, 2),
('Blood pressure monitoring', '2024-01-15 11:00:00', 75.00, 1, 1),
('Follow-up examination', '2024-01-16 09:30:00', 120.00, 3, 1);

-- Insert sample lab tests
INSERT INTO lab_test (test_type, test_date, test_cost, patient_id) VALUES
('Blood Test', '2024-01-15 08:00:00', 25.00, 1),
('X-Ray', '2024-01-15 09:30:00', 50.00, 2),
('Urine Analysis', '2024-01-16 10:00:00', 15.00, 3);

-- Insert sample payments
INSERT INTO payment (total_amount, payment_method, payment_status, payment_date, patient_id) VALUES
(100.00, 'Cash', 'Paid', '2024-01-15 12:00:00', 2),
(75.00, 'Card', 'Pending', '2024-01-15 14:30:00', 1),
(120.00, 'Insurance', 'Paid', '2024-01-16 11:00:00', 3);

-- Insert sample medical history
INSERT INTO medical_history (condition, diagnosis_date, notes, patient_id) VALUES
('Hypertension', '2024-01-10 00:00:00', 'High blood pressure, medication prescribed', 1),
('Diabetes Type 2', '2024-01-08 00:00:00', 'Controlled with diet and medication', 2),
('Asthma', '2024-01-05 00:00:00', 'Mild asthma, inhaler prescribed', 3);

-- Update user references
UPDATE users SET doctor_id = 1 WHERE username = 'doctor1';
UPDATE users SET staff_id = 1 WHERE username = 'receptionist1';
UPDATE users SET staff_id = 2 WHERE username = 'nurse1';
UPDATE users SET staff_id = 3 WHERE username = 'accountant1';

-- Update doctor references
UPDATE doctor SET user_id = 1 WHERE doctor_id = 1;
UPDATE staff SET user_id = 2 WHERE staff_id = 1;
UPDATE staff SET user_id = 3 WHERE staff_id = 2;
UPDATE staff SET user_id = 4 WHERE staff_id = 3;

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_patient_name ON patient(name);
CREATE INDEX idx_appointment_date ON appointment(appointment_date);
CREATE INDEX idx_appointment_doctor ON appointment(doctor_id);
CREATE INDEX idx_appointment_patient ON appointment(patient_id);
CREATE INDEX idx_treatment_date ON treatment(treatment_date);
CREATE INDEX idx_admission_date ON admission(admission_date);
CREATE INDEX idx_payment_date ON payment(payment_date);

-- Display success message
SELECT 'Database setup completed successfully!' as message;
SELECT 'You can now access the application at: http://localhost/Hospital_project' as url;
SELECT 'Default login credentials:' as info;
SELECT 'Doctor: username=doctor1, password=password' as doctor_login;
SELECT 'Receptionist: username=receptionist1, password=password' as receptionist_login;
SELECT 'Nurse: username=nurse1, password=password' as nurse_login;
SELECT 'Accountant: username=accountant1, password=password' as accountant_login;






--admission procedure




-- ***************************************************************
-- 1. INPUT VARIABLES (EDIT THESE FOR EACH ADMISSION)
-- ***************************************************************
SET @target_patient_id = 1;       -- The patient being admitted (must exist)
SET @room_type_needed = 'General'; -- e.g., 'General', 'Private', 'ICU'
SET @admission_datetime = NOW();  -- The time of admission
;

-- Temporary variables to hold information retrieved during the process
SET @assigned_bed_id = NULL;
SET @assigned_room_id = NULL;
;

-- Start the transaction to ensure all updates happen atomically
START TRANSACTION;

-- ***************************************************************
-- 2. FIND AVAILABLE BED AND ROOM
-- ***************************************************************

-- Find the first available bed that belongs to the required room type.
-- We retrieve both the bed_id and room_id into variables.
SELECT b.bed_id, r.room_id
INTO @assigned_bed_id, @assigned_room_id
FROM bed b
JOIN room r ON b.room_id = r.room_id
WHERE b.bed_status = 'Available'
  AND r.room_type = @room_type_needed
LIMIT 1
FOR UPDATE; -- Locks the selected bed row
; -- Semicolon for termination

-- ***************************************************************
-- 3. ADMISSION LOGIC (Execute only if a bed was found)
-- ***************************************************************

-- FIX IS HERE: Add @assigned_room_id to the INSERT statement.
INSERT INTO admission (patient_id, bed_id, room_id, admission_date, discharge_date)
SELECT @target_patient_id, @assigned_bed_id, @assigned_room_id, @admission_datetime, NULL
WHERE @assigned_bed_id IS NOT NULL;
;

-- If the admission succeeded, LAST_INSERT_ID() will be non-zero.
-- We rely on the implicit behavior: if the INSERT failed (because @assigned_bed_id was NULL),
-- the following updates do not affect the database state.

-- Update the Bed status to 'Occupied' (This only affects the row if @assigned_bed_id is valid)
UPDATE bed
SET bed_status = 'Occupied'
WHERE bed_id = @assigned_bed_id
  AND @assigned_bed_id IS NOT NULL;
;

-- Update the associated Room status (This only affects the row if @assigned_room_id is valid)
UPDATE room
SET room_status = 'Occupied'
WHERE room_id = @assigned_room_id
  AND @assigned_room_id IS NOT NULL;
;

-- ***************************************************************
-- 4. FINALIZATION AND STATUS CHECK
-- ***************************************************************

-- If no INSERT happened, or a previous step failed, a rollback may occur implicitly
-- (depending on engine/configuration). The safest approach is to COMMIT here.
COMMIT;
;

-- Check the result of the admission
SELECT 'Admission Status' AS Title,
       CASE
           WHEN @assigned_bed_id IS NOT NULL THEN 'Success: Patient Admitted'
           ELSE 'Failure: No Available Bed Found'
       END AS Status,
       LAST_INSERT_ID() AS admission_id,
       @target_patient_id AS patient_id,
       @assigned_bed_id AS assigned_bed_id,
       @assigned_room_id AS assigned_room_id -- Show the room_id for confirmation
;