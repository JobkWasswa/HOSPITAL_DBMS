-- ---
-- Hospital Management System - Sample Data Insertion Script
-- This script ensures data integrity and volume requirements are met.
-- ---

-- Define the constant hash for 'password'
SET @password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; 

-- Helper function to generate 10-digit phone numbers starting with '077'
-- Note: Not all MySQL versions support stored functions easily in a single script.
-- For simplicity, we use the CONCAT and RAND() approach for the numbers.

-- ---
-- 1. DEPARTMENT (10 items)
-- ---
INSERT INTO department (name, location) VALUES
('General Medicine', 'First Floor, West Wing'),
('Cardiology', 'Second Floor, Central Block'),
('Emergency', 'Ground Floor, Main Entrance'),
('Surgery', 'Third Floor, East Wing'),
('Pediatrics', 'Second Floor, Children’s Area'),
('Oncology', 'Fourth Floor, Cancer Center'),
('Neurology', 'Fifth Floor, Brain Institute'),
('Orthopedics', 'Third Floor, Bone Unit'),
('Dermatology', 'First Floor, Skin Clinic'),
('Pharmacy', 'Ground Floor, Dispensary');


-- ---
-- 2. ROOM (30 items)
-- ---
-- 10 General, 10 Private, 10 ICU
INSERT INTO room (room_type, room_status, daily_cost) VALUES
('General', 'Available', 100.00), ('General', 'Occupied', 100.00), ('General', 'Available', 100.00),
('General', 'Available', 100.00), ('General', 'Maintenance', 100.00), ('General', 'Available', 100.00),
('General', 'Occupied', 100.00), ('General', 'Available', 100.00), ('General', 'Available', 100.00),
('General', 'Available', 100.00),
('Private', 'Available', 250.00), ('Private', 'Occupied', 250.00), ('Private', 'Available', 250.00),
('Private', 'Available', 250.00), ('Private', 'Occupied', 250.00), ('Private', 'Available', 250.00),
('Private', 'Available', 250.00), ('Private', 'Maintenance', 250.00), ('Private', 'Available', 250.00),
('Private', 'Available', 250.00),
('ICU', 'Available', 500.00), ('ICU', 'Occupied', 500.00), ('ICU', 'Available', 500.00),
('ICU', 'Available', 500.00), ('ICU', 'Occupied', 500.00), ('ICU', 'Available', 500.00),
('ICU', 'Available', 500.00), ('ICU', 'Available', 500.00), ('ICU', 'Maintenance', 500.00),
('ICU', 'Available', 500.00);


-- ---
-- 3. BED (30 items)
-- NOTE: Assuming each of the 30 rooms has a corresponding bed entry for simplicity, though real systems have multiple beds per room.
-- Bed IDs 1-30 linked to Room IDs 1-30.
-- ---
INSERT INTO bed (bed_no, bed_type, bed_status, room_id) VALUES
('G-101', 'Standard', 'Occupied', 1), ('G-102', 'Standard', 'Available', 2), ('G-103', 'Standard', 'Available', 3),
('G-104', 'Standard', 'Available', 4), ('G-105', 'Standard', 'Available', 5), ('G-106', 'Standard', 'Available', 6),
('G-107', 'Standard', 'Occupied', 7), ('G-108', 'Standard', 'Available', 8), ('G-109', 'Standard', 'Available', 9),
('G-110', 'Standard', 'Available', 10),
('P-201', 'Standard', 'Occupied', 11), ('P-202', 'Standard', 'Available', 12), ('P-203', 'Standard', 'Available', 13),
('P-204', 'Standard', 'Available', 14), ('P-205', 'Standard', 'Occupied', 15), ('P-206', 'Standard', 'Available', 16),
('P-207', 'Standard', 'Available', 17), ('P-208', 'Standard', 'Available', 18), ('P-209', 'Standard', 'Available', 19),
('P-210', 'Standard', 'Available', 20),
('I-501', 'ICU', 'Occupied', 21), ('I-502', 'ICU', 'Available', 22), ('I-503', 'ICU', 'Available', 23),
('I-504', 'ICU', 'Available', 24), ('I-505', 'ICU', 'Occupied', 25), ('I-506', 'ICU', 'Available', 26),
('I-507', 'ICU', 'Available', 27), ('I-508', 'ICU', 'Available', 28), ('I-509', 'ICU', 'Available', 29),
('I-510', 'ICU', 'Available', 30);


-- ---
-- 4. DOCTOR (20 items)
-- ---
INSERT INTO doctor (name, specialization, phone, department_id) VALUES
('Dr. John Smith', 'Cardiologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 2),
('Dr. Sarah Johnson', 'Internist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 1),
('Dr. Mike Wilson', 'Trauma Specialist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 3),
('Dr. Emily Brown', 'General Surgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 4),
('Dr. David Lee', 'Pediatrician', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 5),
('Dr. Chris Evans', 'Oncologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 6),
('Dr. Jessica Alba', 'Neurologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 7),
('Dr. Tom Hardy', 'Orthopedic Surgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 8),
('Dr. Maria Gomez', 'Dermatologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 9),
('Dr. Robert Downey', 'Gastroenterologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 1),
('Dr. Linda Perry', 'General Practitioner', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 1),
('Dr. Kevin Hart', 'Vascular Surgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 4),
('Dr. Selena Kyle', 'Pediatric Surgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 5),
('Dr. Bruce Wayne', 'Neurosurgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 7),
('Dr. Diana Prince', 'Radiation Oncologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 6),
('Dr. Clark Kent', 'Emergency Physician', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 3),
('Dr. Peter Parker', 'Sports Medicine', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 8),
('Dr. Wanda Maximoff', 'Electrophysiologist', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 2),
('Dr. Stephen Strange', 'Infectious Disease', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 1),
('Dr. Tony Stark', 'Cosmetic Surgeon', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)), 9);


-- 5. STAFF (29 items: 25 Nurses, 2 Receptionists, 2 Accountants + 4 Cleaners = 33 total staff)
-- Added phone numbers using the 077xxxxxxx format
-- ---
-- 25 Nurses
INSERT INTO staff (name, role, shift, department_id, phone) VALUES
('Nurse Alice', 'Nurse', 'Morning', 1, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Bob', 'Nurse', 'Evening', 1, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Carol', 'Nurse', 'Night', 2, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Dan', 'Nurse', 'Morning', 2, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Eve', 'Nurse', 'Evening', 3, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Frank', 'Nurse', 'Night', 3, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Grace', 'Nurse', 'Morning', 4, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Harry', 'Nurse', 'Evening', 4, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Ivy', 'Nurse', 'Night', 5, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Jack', 'Nurse', 'Morning', 5, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Kelly', 'Nurse', 'Evening', 6, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Liam', 'Nurse', 'Night', 6, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Mia', 'Nurse', 'Morning', 7, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Noah', 'Nurse', 'Evening', 7, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Olivia', 'Nurse', 'Night', 8, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Peter', 'Nurse', 'Morning', 8, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Quinn', 'Nurse', 'Evening', 9, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Ryan', 'Nurse', 'Night', 9, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Sam', 'Nurse', 'Morning', 1, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Tina', 'Nurse', 'Evening', 2, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Uma', 'Nurse', 'Night', 3, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Victor', 'Nurse', 'Morning', 4, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Wendy', 'Nurse', 'Evening', 5, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Xander', 'Nurse', 'Night', 6, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Nurse Yvonne', 'Nurse', 'Morning', 7, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)));

-- 2 Receptionists
INSERT INTO staff (name, role, shift, department_id, phone) VALUES
('Receptionist Jen', 'Receptionist', 'Morning', 3, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Receptionist Ken', 'Receptionist', 'Evening', 1, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)));

-- 2 Accountants
INSERT INTO staff (name, role, shift, department_id, phone) VALUES
('Accountant Leo', 'Accountant', 'Morning', 10, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Accountant Max', 'Accountant', 'Morning', 10, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)));

-- 4 Cleaners
INSERT INTO staff (name, role, shift, department_id, phone) VALUES
('Cleaner Joy', 'Cleaner', 'Night', 1, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Cleaner Ben', 'Cleaner', 'Morning', 2, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Cleaner Cid', 'Cleaner', 'Evening', 3, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0))),
('Cleaner Dee', 'Cleaner', 'Night', 4, CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)));


-- ---
-- 6. USERS (20 Doctors + 29 Staff = 49 items)
-- Usernames: doc1 to doc20, staff1 to staff29. Password: 'password'
-- ---
-- Insert Doctors as Users (doc1 to doc20)
INSERT INTO users (username, password_hash, role, is_active, doctor_id, staff_id)
SELECT CONCAT('doc', doctor_id), @password_hash, 'doctor', 1, doctor_id, NULL FROM doctor;

-- Insert Staff as Users (staff1 to staff29)
INSERT INTO users (username, password_hash, role, is_active, doctor_id, staff_id)
SELECT CONCAT('staff', staff_id), @password_hash, 
       CASE WHEN role IN ('Nurse', 'Receptionist', 'Cleaner') THEN 'staff' ELSE 'accountant' END, 
       1, NULL, staff_id FROM staff;


-- ---
-- 7. PATIENT (50 items)
-- ---
INSERT INTO patient (name, DOB, gender, address, phone) VALUES
('Alice Johnson', '1995-03-15', 'Female', '123 Main St, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Bob Smith', '1978-07-22', 'Male', '456 Oak Ave, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Carol Davis', '1992-11-08', 'Female', '789 Pine Rd, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('David Wilson', '1965-05-30', 'Male', '321 Elm St, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Eva Brown', '1988-09-12', 'Female', '654 Maple Dr, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Frank White', '2001-01-01', 'Male', '987 Cedar Ln, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Grace Hall', '1955-12-25', 'Female', '111 Birch Pk, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Henry Green', '1980-02-14', 'Male', '222 Walnut Blv, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Ivy King', '2010-06-05', 'Female', '333 Aspen Cir, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Jack Miller', '1973-04-17', 'Male', '444 Spruce Way, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Kelly Jones', '1998-10-20', 'Female', '555 Willow Cr, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Leo Martin', '1960-08-01', 'Male', '666 Redwood P, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Mia Rodriguez', '2005-09-03', 'Female', '777 Poplar Sq, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Noah Taylor', '1983-01-28', 'Male', '888 Fir Dr, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Olivia Clark', '1970-11-11', 'Female', '999 Hemlock Rd, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Peter Varga', '1990-04-04', 'Male', '101 Pinecone Ln, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Quinn Foster', '1975-02-09', 'Female', '202 Willow Gr, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Ryan Harris', '2000-07-07', 'Male', '303 Cedar Hts, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Sophia King', '1968-03-19', 'Female', '404 Oak Bend, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Tom Lewis', '1982-12-06', 'Male', '505 Maple Pk, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Uma Nelson', '1996-05-21', 'Female', '606 Elm View, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Victor Perez', '1971-10-14', 'Male', '707 Birch Cir, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Willa Scott', '2003-08-27', 'Female', '808 Spruce Ter, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Xavier Ross', '1963-06-02', 'Male', '909 Poplar Ln, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Yara White', '1989-04-29', 'Female', '110 Pine Ave, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Zane Hall', '1976-01-18', 'Male', '220 Cedar Dr, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Amy Lee', '2008-03-03', 'Female', '330 Oak Blvd, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Ben King', '1969-09-09', 'Male', '440 Maple Ct, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Chloe Martin', '1984-12-12', 'Female', '550 Elm Way, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Dirk Chen', '1972-02-28', 'Male', '660 Birch Rd, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Ella Kim', '1999-07-01', 'Female', '770 Spruce St, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Finn Walker', '1967-05-16', 'Male', '880 Poplar Pk, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Gia Bell', '1981-11-23', 'Female', '990 Pine Ter, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Hugh Adams', '1974-04-06', 'Male', '100 Oakwood Dr, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Iris Young', '2004-01-13', 'Female', '200 Maple Ln, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Jake Carter', '1961-10-26', 'Male', '300 Elm St, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Kira Evans', '1993-08-09', 'Female', '400 Birch Ave, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Luke Flores', '1979-03-24', 'Male', '500 Spruce Dr, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Mina Hayes', '1966-12-07', 'Female', '600 Poplar Ct, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Niles Baker', '1986-09-19', 'Male', '700 Pine Way, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Oona Hill', '1991-05-02', 'Female', '800 Oak St, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Paul Kim', '1977-11-14', 'Male', '900 Maple Ter, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Ria Tran', '2002-06-29', 'Female', '1000 Elm Ln, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Sam Wu', '1964-01-08', 'Male', '1100 Birch St, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Tess Zhu', '1987-07-25', 'Female', '1200 Spruce Dr, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Uli Lee', '1970-03-01', 'Male', '1300 Poplar Ct, JINJA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Vera Fox', '1994-10-10', 'Female', '1400 Pine Way, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Will Chan', '1962-08-05', 'Male', '1500 Oak St, MBR', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Xena Ray', '1989-02-19', 'Female', '1600 Maple Ter, GULU', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000))),
('Yusuf Ali', '1975-06-11', 'Other', '1700 Elm Ln, KLA', CONCAT('077', FLOOR(RAND() * 9000000 + 1000000)));


-- ---
-- 8. ADMISSION (20 items)
-- Linking to Patient IDs 1-20 and Room IDs 1-20 (beds 1-20 are in these rooms)
-- ---
INSERT INTO admission (admission_date, discharge_date, patient_id, room_id, bed_id) VALUES
(NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 5 DAY, 1, 1, 1), -- Discharged
(NOW() - INTERVAL 7 DAY, NULL, 2, 2, 2), -- Current
(NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 1 DAY, 3, 3, 3), -- Discharged
(NOW() - INTERVAL 2 DAY, NULL, 4, 4, 4), -- Current
(NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 10 DAY, 5, 5, 5), -- Discharged
(NOW() - INTERVAL 1 DAY, NULL, 6, 6, 6), -- Current
(NOW() - INTERVAL 12 DAY, NULL, 7, 7, 7), -- Current
(NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 6 DAY, 8, 8, 8), -- Discharged
(NOW() - INTERVAL 3 DAY, NULL, 9, 9, 9), -- Current
(NOW() - INTERVAL 14 DAY, NULL, 10, 10, 10), -- Current
(NOW() - INTERVAL 5 DAY, NULL, 11, 11, 11), -- Current
(NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 4 DAY, 12, 12, 12), -- Discharged
(NOW() - INTERVAL 6 DAY, NULL, 13, 13, 13), -- Current
(NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 7 DAY, 14, 14, 14), -- Discharged
(NOW() - INTERVAL 4 DAY, NULL, 15, 15, 15), -- Current
(NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 2 DAY, 16, 16, 16), -- Discharged
(NOW() - INTERVAL 13 DAY, NULL, 17, 17, 17), -- Current
(NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 8 DAY, 18, 18, 18), -- Discharged
(NOW() - INTERVAL 25 DAY, NULL, 19, 19, 19), -- Current
(NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 3 DAY, 20, 20, 20); -- Discharged


-- ---
-- 9. MEDICINE (30 items)
-- ---
INSERT INTO medicine (name, dosage, stock_quantity, medicine_price) VALUES
('Paracetamol', '500mg', 100, 5.00), ('Ibuprofen', '400mg', 80, 8.50), ('Amoxicillin', '250mg', 60, 12.00),
('Aspirin', '100mg', 120, 3.50), ('Vitamin D', '1000IU', 90, 15.00), ('Lisinopril', '10mg', 45, 20.00),
('Metformin', '500mg', 70, 18.00), ('Atorvastatin', '20mg', 55, 30.00), ('Omeprazole', '20mg', 110, 10.00),
('Zoloft', '50mg', 40, 55.00), ('Ventolin Inhaler', '100mcg', 35, 70.00), ('Doxycycline', '100mg', 65, 14.50),
('Prednisone', '5mg', 50, 22.00), ('Furosemide', '40mg', 75, 9.00), ('Warfarin', '5mg', 30, 40.00),
('Tramadol', '50mg', 95, 11.00), ('Ciprofloxacin', '500mg', 85, 16.00), ('Cetirizine', '10mg', 150, 4.00),
('Levothyroxine', '75mcg', 40, 28.00), ('Insulin Glargine', '100U/ml', 25, 120.00), ('Losartan', '50mg', 60, 25.00),
('Gabapentin', '300mg', 70, 17.50), ('Oxycodone', '10mg', 20, 80.00), ('Pantoprazole', '40mg', 80, 13.00),
('Fluconazole', '150mg', 55, 21.00), ('Hydrochlorothiazide', '25mg', 90, 7.50), ('Clopidogrel', '75mg', 45, 35.00),
('Amlodipine', '5mg', 65, 19.00), ('Metoprolol', '50mg', 100, 16.50), ('Azithromycin', '250mg', 50, 15.50);


-- ---
-- 10. APPOINTMENT (30 items)
-- Linking to Patient IDs 1-30, Doctor IDs 1-10 (for variety)
-- ---
INSERT INTO appointment (appointment_date, consultation_fee, doctor_id, appointment_status, patient_id) VALUES
(NOW() - INTERVAL 10 DAY, 50.00, 1, 'Completed', 1), (NOW() - INTERVAL 9 DAY, 75.00, 2, 'Completed', 2),
(NOW() - INTERVAL 8 DAY, 50.00, 3, 'Scheduled', 3), (NOW() - INTERVAL 7 DAY, 100.00, 4, 'Cancelled', 4),
(NOW() - INTERVAL 6 DAY, 75.00, 5, 'Completed', 5), (NOW() - INTERVAL 5 DAY, 50.00, 6, 'No show', 6),
(NOW() - INTERVAL 4 DAY, 100.00, 7, 'Scheduled', 7), (NOW() - INTERVAL 3 DAY, 75.00, 8, 'Completed', 8),
(NOW() - INTERVAL 2 DAY, 50.00, 9, 'Scheduled', 9), (NOW() - INTERVAL 1 DAY, 100.00, 10, 'Completed', 10),
(NOW() - INTERVAL 10 HOUR, 75.00, 1, 'Scheduled', 11), (NOW() - INTERVAL 9 HOUR, 50.00, 2, 'Completed', 12),
(NOW() - INTERVAL 8 HOUR, 100.00, 3, 'Scheduled', 13), (NOW() - INTERVAL 7 HOUR, 50.00, 4, 'Completed', 14),
(NOW() - INTERVAL 6 HOUR, 75.00, 5, 'Scheduled', 15), (NOW() - INTERVAL 5 HOUR, 100.00, 6, 'Cancelled', 16),
(NOW() - INTERVAL 4 HOUR, 50.00, 7, 'Completed', 17), (NOW() - INTERVAL 3 HOUR, 75.00, 8, 'Scheduled', 18),
(NOW() - INTERVAL 2 HOUR, 100.00, 9, 'Completed', 19), (NOW() - INTERVAL 1 HOUR, 50.00, 10, 'Scheduled', 20),
(NOW() + INTERVAL 1 DAY, 75.00, 11, 'Scheduled', 21), (NOW() + INTERVAL 2 DAY, 50.00, 12, 'Scheduled', 22),
(NOW() + INTERVAL 3 DAY, 100.00, 13, 'Scheduled', 23), (NOW() + INTERVAL 4 DAY, 75.00, 14, 'Scheduled', 24),
(NOW() + INTERVAL 5 DAY, 50.00, 15, 'Scheduled', 25), (NOW() + INTERVAL 6 DAY, 100.00, 16, 'Scheduled', 26),
(NOW() + INTERVAL 7 DAY, 75.00, 17, 'Scheduled', 27), (NOW() + INTERVAL 8 DAY, 50.00, 18, 'Scheduled', 28),
(NOW() + INTERVAL 9 DAY, 100.00, 19, 'Scheduled', 29), (NOW() + INTERVAL 10 DAY, 75.00, 20, 'Scheduled', 30);


-- ---
-- 11. TREATMENT (30 items)
-- Linking to Patient IDs 1-30, Doctor IDs 1-10
-- ---
INSERT INTO treatment (notes, treatment_date, treatment_fee, patient_id, doctor_id) VALUES
('Initial assessment and plan', NOW() - INTERVAL 10 DAY, 100.00, 1, 1),
('Follow-up blood pressure check', NOW() - INTERVAL 8 DAY, 75.00, 2, 2),
('Suture removal', NOW() - INTERVAL 6 DAY, 120.00, 3, 3),
('Physical therapy session 1', NOW() - INTERVAL 4 DAY, 90.00, 4, 4),
('Vaccination administered', NOW() - INTERVAL 2 DAY, 50.00, 5, 5),
('Chemotherapy Session 1', NOW() - INTERVAL 1 DAY, 1500.00, 6, 6),
('MRI review and consultation', NOW(), 180.00, 7, 7),
('Cast applied', NOW() - INTERVAL 12 DAY, 300.00, 8, 8),
('Skin biopsy', NOW() - INTERVAL 11 DAY, 150.00, 9, 9),
('Endoscopy procedure', NOW() - INTERVAL 10 DAY, 450.00, 10, 10),
('Routine check-up', NOW() - INTERVAL 9 DAY, 60.00, 11, 1),
('Minor surgical incision', NOW() - INTERVAL 7 DAY, 250.00, 12, 4),
('Child growth assessment', NOW() - INTERVAL 5 DAY, 70.00, 13, 5),
('Pain management consultation', NOW() - INTERVAL 3 DAY, 95.00, 14, 8),
('Diabetic foot exam', NOW() - INTERVAL 1 DAY, 85.00, 15, 1),
('Initial assessment and plan', NOW() - INTERVAL 10 DAY, 100.00, 16, 2),
('Follow-up blood pressure check', NOW() - INTERVAL 8 DAY, 75.00, 17, 3),
('Suture removal', NOW() - INTERVAL 6 DAY, 120.00, 18, 4),
('Physical therapy session 1', NOW() - INTERVAL 4 DAY, 90.00, 19, 5),
('Vaccination administered', NOW() - INTERVAL 2 DAY, 50.00, 20, 6),
('Chemotherapy Session 1', NOW() - INTERVAL 1 DAY, 1500.00, 21, 7),
('MRI review and consultation', NOW(), 180.00, 22, 8),
('Cast applied', NOW() - INTERVAL 12 DAY, 300.00, 23, 9),
('Skin biopsy', NOW() - INTERVAL 11 DAY, 150.00, 24, 10),
('Endoscopy procedure', NOW() - INTERVAL 10 DAY, 450.00, 25, 1),
('Routine check-up', NOW() - INTERVAL 9 DAY, 60.00, 26, 2),
('Minor surgical incision', NOW() - INTERVAL 7 DAY, 250.00, 27, 3),
('Child growth assessment', NOW() - INTERVAL 5 DAY, 70.00, 28, 4),
('Pain management consultation', NOW() - INTERVAL 3 DAY, 95.00, 29, 5),
('Diabetic foot exam', NOW() - INTERVAL 1 DAY, 85.00, 30, 6);


-- ---
-- 12. LAB_TEST (30 items)
-- Linking to Patient IDs 1-30
-- ---
INSERT INTO lab_test (test_type, results, test_date, test_cost, patient_id) VALUES
('Blood Count', 'Normal', NOW() - INTERVAL 10 DAY, 25.00, 1),
('X-Ray Chest', 'Clear', NOW() - INTERVAL 8 DAY, 50.00, 2),
('Urine Analysis', 'Negative', NOW() - INTERVAL 6 DAY, 15.00, 3),
('Lipid Panel', 'High Cholesterol', NOW() - INTERVAL 4 DAY, 45.00, 4),
('Glucose Test', 'Normal', NOW() - INTERVAL 2 DAY, 20.00, 5),
('CT Scan', 'Lesion Detected', NOW() - INTERVAL 1 DAY, 300.00, 6),
('MRI Brain', 'Clear', NOW(), 450.00, 7),
('Bone Density', 'Low', NOW() - INTERVAL 12 DAY, 60.00, 8),
('Allergy Test', 'Positive', NOW() - INTERVAL 11 DAY, 80.00, 9),
('Endoscopy Prep', 'Completed', NOW() - INTERVAL 10 DAY, 30.00, 10),
('Blood Count', 'Low Iron', NOW() - INTERVAL 9 DAY, 25.00, 11),
('X-Ray Leg', 'Fracture', NOW() - INTERVAL 7 DAY, 50.00, 12),
('Urine Analysis', 'Normal', NOW() - INTERVAL 5 DAY, 15.00, 13),
('Lipid Panel', 'Normal', NOW() - INTERVAL 3 DAY, 45.00, 14),
('Glucose Test', 'High', NOW() - INTERVAL 1 DAY, 20.00, 15),
('Blood Count', 'Normal', NOW() - INTERVAL 10 DAY, 25.00, 16),
('X-Ray Chest', 'Clear', NOW() - INTERVAL 8 DAY, 50.00, 17),
('Urine Analysis', 'Negative', NOW() - INTERVAL 6 DAY, 15.00, 18),
('Lipid Panel', 'High Cholesterol', NOW() - INTERVAL 4 DAY, 45.00, 19),
('Glucose Test', 'Normal', NOW() - INTERVAL 2 DAY, 20.00, 20),
('CT Scan', 'Lesion Detected', NOW() - INTERVAL 1 DAY, 300.00, 21),
('MRI Brain', 'Clear', NOW(), 450.00, 22),
('Bone Density', 'Low', NOW() - INTERVAL 12 DAY, 60.00, 23),
('Allergy Test', 'Positive', NOW() - INTERVAL 11 DAY, 80.00, 24),
('Endoscopy Prep', 'Completed', NOW() - INTERVAL 10 DAY, 30.00, 25),
('Blood Count', 'Low Iron', NOW() - INTERVAL 9 DAY, 25.00, 26),
('X-Ray Leg', 'Fracture', NOW() - INTERVAL 7 DAY, 50.00, 27),
('Urine Analysis', 'Normal', NOW() - INTERVAL 5 DAY, 15.00, 28),
('Lipid Panel', 'Normal', NOW() - INTERVAL 3 DAY, 45.00, 29),
('Glucose Test', 'High', NOW() - INTERVAL 1 DAY, 20.00, 30);


-- ---
-- 13. PRESCRIPTION (30 items)
-- Linking to Treatment IDs 1-30, Medicine IDs 1-10
-- ---
INSERT INTO prescription (quantity, dosage_instructions, treatment_id, medicine_id) VALUES
(30, 'Take one tablet daily', 1, 1), (60, 'Take twice a day for 30 days', 2, 6),
(1, 'Apply directly to wound', 3, 16), (20, 'Take as needed for pain', 4, 2),
(1, 'Inject once', 5, 20), (50, 'Chemo cycle 1', 6, 23),
(30, 'Take one capsule daily', 7, 7), (10, 'Use before meals', 8, 8),
(45, 'Apply cream twice daily', 9, 9), (90, 'Take three times a day', 10, 10),
(30, 'Take one tablet daily', 11, 1), (60, 'Take twice a day for 30 days', 12, 6),
(1, 'Apply directly to wound', 13, 16), (20, 'Take as needed for pain', 14, 2),
(1, 'Inject once', 15, 20), (50, 'Chemo cycle 1', 16, 23),
(30, 'Take one capsule daily', 17, 7), (10, 'Use before meals', 18, 8),
(45, 'Apply cream twice daily', 19, 9), (90, 'Take three times a day', 20, 10),
(30, 'Take one tablet daily', 21, 1), (60, 'Take twice a day for 30 days', 22, 6),
(1, 'Apply directly to wound', 23, 16), (20, 'Take as needed for pain', 24, 2),
(1, 'Inject once', 25, 20), (50, 'Chemo cycle 1', 26, 23),
(30, 'Take one capsule daily', 27, 7), (10, 'Use before meals', 28, 8),
(45, 'Apply cream twice daily', 29, 9), (90, 'Take three times a day', 30, 10);


-- ---
-- 15. MEDICAL_HISTORY (30 items)
-- Linking to Patient IDs 1-30
-- ---
INSERT INTO medical_history (medical_condition, diagnosis_date, notes, patient_id) VALUES
('Hypertension', NOW() - INTERVAL 500 DAY, 'On Lisinopril', 1),
('Diabetes Type 2', NOW() - INTERVAL 700 DAY, 'Controlled with Metformin', 2),
('Asthma', NOW() - INTERVAL 1000 DAY, 'Uses Ventolin PRN', 3),
('High Cholesterol', NOW() - INTERVAL 300 DAY, 'Started on Atorvastatin', 4),
('Seasonal Allergies', NOW() - INTERVAL 150 DAY, 'Mild, prescribed Cetirizine', 5),
('Lung Cancer', NOW() - INTERVAL 50 DAY, 'Undergoing Chemotherapy', 6),
('Chronic Migraine', NOW() - INTERVAL 600 DAY, 'Follow-up Neurology', 7),
('Osteoarthritis', NOW() - INTERVAL 400 DAY, 'Requires surgery planning', 8),
('Eczema', NOW() - INTERVAL 200 DAY, 'Topical steroid use', 9),
('GERD', NOW() - INTERVAL 900 DAY, 'On Omeprazole', 10),
('Iron Deficiency Anemia', NOW() - INTERVAL 120 DAY, 'Supplementation needed', 11),
('Compound Fracture (Leg)', NOW() - INTERVAL 10 DAY, 'Recent surgery, long recovery', 12),
('Common Cold', NOW() - INTERVAL 5 DAY, 'Viral infection', 13),
('Lupus', NOW() - INTERVAL 360 DAY, 'Requires continuous monitoring', 14),
('Severe Diabetes', NOW() - INTERVAL 100 DAY, 'Requires Insulin', 15),
('Hypertension', NOW() - INTERVAL 550 DAY, 'On Lisinopril', 16),
('Diabetes Type 2', NOW() - INTERVAL 750 DAY, 'Controlled with Metformin', 17),
('Asthma', NOW() - INTERVAL 1050 DAY, 'Uses Ventolin PRN', 18),
('High Cholesterol', NOW() - INTERVAL 350 DAY, 'Started on Atorvastatin', 19),
('Seasonal Allergies', NOW() - INTERVAL 250 DAY, 'Mild, prescribed Cetirizine', 20),
('Lung Cancer', NOW() - INTERVAL 150 DAY, 'Undergoing Chemotherapy', 21),
('Chronic Migraine', NOW() - INTERVAL 650 DAY, 'Follow-up Neurology', 22),
('Osteoarthritis', NOW() - INTERVAL 450 DAY, 'Requires surgery planning', 23),
('Eczema', NOW() - INTERVAL 250 DAY, 'Topical steroid use', 24),
('GERD', NOW() - INTERVAL 950 DAY, 'On Omeprazole', 25),
('Iron Deficiency Anemia', NOW() - INTERVAL 170 DAY, 'Supplementation needed', 26),
('Compound Fracture (Arm)', NOW() - INTERVAL 15 DAY, 'Recent surgery, long recovery', 27),
('Flu', NOW() - INTERVAL 7 DAY, 'Viral infection', 28),
('Hepatitis B', NOW() - INTERVAL 400 DAY, 'Requires continuous monitoring', 29),
('Severe Diabetes', NOW() - INTERVAL 200 DAY, 'Requires Insulin', 30);