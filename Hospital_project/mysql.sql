

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
INSERT INTO staff (name, role, shift, department_id, phone) VALUES
('Jane Doe', 'Receptionist', 'Morning', 1, '5551001'),
('Mike Johnson', 'Nurse', 'Evening', 1, '5551002'),
('Sarah Wilson', 'Accountant', 'Morning', 1, '5551003'),
('Tom Brown', 'Nurse', 'Night', 2, '5551004'),
('Lisa Davis', 'Cleaner', 'Morning', 1, '5551005');

-- Insert users (password is 'password' for all)
INSERT INTO users (username, password_hash, role, is_active, doctor_id, staff_id) VALUES
('atumathiasd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 1, 1, NULL),
('atumathiasr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',  1, NULL, 1),
('atumathiasn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1, NULL, 2),
('atumathiasa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1, NULL, 3);

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
INSERT INTO medical_history (medical_condition, diagnosis_date, notes, patient_id) VALUES
('Hypertension', '2024-01-10 00:00:00', 'High blood pressure, medication prescribed', 1),
('Diabetes Type 2', '2024-01-08 00:00:00', 'Controlled with diet and medication', 2),
('Asthma', '2024-01-05 00:00:00', 'Mild asthma, inhaler prescribed', 3);
