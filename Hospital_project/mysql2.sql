-- ---
-- Hospital Management System - Sample Data Insertion Script
-- **UPDATED to match the new database_schema.md**
-- Changes: Doctor, Staff, Bed, Prescription tables removed/consolidated.
-- All personnel are now in the 'users' table.
-- ---

-- Define the constant hash for 'password'
SET @password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; 

-- Helper function to generate 10-digit phone numbers starting with '077'
-- Note: Using LPAD(FLOOR(RAND() * 10000000), 7, 0) to ensure a 7-digit random number.
SET @rand_phone_suffix = CONCAT(LPAD(FLOOR(RAND() * 10000000), 7, 0));

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
-- 2. USERS (49 items: 20 Doctors, 25 Nurses, 2 Receptionists, 2 Accountants)
-- Combined Doctor and Staff data.
-- ---
-- Doctors (user_id 1 to 20)
INSERT INTO users (username, password_hash, role, shift, phone, specialization, is_active, department_id) VALUES
('DrJohnSmith', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Cardiologist', 1, 2),
('DrSarahJ', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Internist', 1, 1),
('DrMikeW', @password_hash, 'doctor', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Trauma Specialist', 1, 3),
('DrEmilyB', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'General Surgeon', 1, 4),
('DrDavidLee', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Pediatrician', 1, 5),
('DrChrisE', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Oncologist', 1, 6),
('DrJessicaA', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Neurologist', 1, 7),
('DrTomHardy', @password_hash, 'doctor', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Orthopedic Surgeon', 1, 8),
('DrMariaG', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Dermatologist', 1, 9),
('DrRobertD', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Gastroenterologist', 1, 1),
('DrLindaP', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'General Practitioner', 1, 1),
('DrKevinH', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Vascular Surgeon', 1, 4),
('DrSelenaK', @password_hash, 'doctor', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Pediatric Surgeon', 1, 5),
('DrBruceW', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Neurosurgeon', 1, 7),
('DrDianaP', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Radiation Oncologist', 1, 6),
('DrClarkK', @password_hash, 'doctor', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Emergency Physician', 1, 3),
('DrPeterP', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Sports Medicine', 1, 8),
('DrWandaM', @password_hash, 'doctor', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Electrophysiologist', 1, 2),
('DrStephenS', @password_hash, 'doctor', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Infectious Disease', 1, 1),
('DrTonyS', @password_hash, 'doctor', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), 'Cosmetic Surgeon', 1, 9);

-- Nurses (user_id 21 to 45)
INSERT INTO users (username, password_hash, role, shift, phone, specialization, is_active, department_id) VALUES
('NurseAlice', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 1),
('NurseBob', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 1),
('NurseCarol', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 2),
('NurseDan', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 2),
('NurseEve', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 3),
('NurseFrank', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 3),
('NurseGrace', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 4),
('NurseHarry', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 4),
('NurseIvy', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 5),
('NurseJack', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 5),
('NurseKelly', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 6),
('NurseLiam', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 6),
('NurseMia', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 7),
('NurseNoah', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 7),
('NurseOlivia', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 8),
('NursePeter', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 8),
('NurseQuinn', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 9),
('NurseRyan', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 9),
('NurseSam', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 1),
('NurseTina', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 2),
('NurseUma', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 3),
('NurseVictor', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 4),
('NurseWendy', @password_hash, 'nurse', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 5),
('NurseXander', @password_hash, 'nurse', 'Night', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 6),
('NurseYvonne', @password_hash, 'nurse', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 7);

-- Receptionists (user_id 46 to 47)
INSERT INTO users (username, password_hash, role, shift, phone, specialization, is_active, department_id) VALUES
('RecJen', @password_hash, 'receptionist', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 3),
('RecKen', @password_hash, 'receptionist', 'Evening', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 1);

-- Accountants (user_id 48 to 49)
INSERT INTO users (username, password_hash, role, shift, phone, specialization, is_active, department_id) VALUES
('AccLeo', @password_hash, 'accountant', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 10),
('AccMax', @password_hash, 'accountant', 'Morning', CONCAT('077', LPAD(FLOOR(RAND() * 10000000), 7, 0)), NULL, 1, 10);


-- ---
-- 3. ROOM (30 items)
-- Added 'bed_stock' column
-- ---
INSERT INTO room (room_type, room_status, daily_cost, bed_stock) VALUES
('General', 'Occupied', 100.00, 4), ('General', 'Occupied', 100.00, 4), ('General', 'Available', 100.00, 4),
('General', 'Available', 100.00, 4), ('General', 'Maintenance', 100.00, 4), ('General', 'Available', 100.00, 4),
('General', 'Occupied', 100.00, 4), ('General', 'Available', 100.00, 4), ('General', 'Available', 100.00, 4),
('General', 'Available', 100.00, 4),
('Private', 'Occupied', 250.00, 1), ( 'Private', 'Available', 250.00, 1), ('Private', 'Available', 250.00, 1),
('Private', 'Available', 250.00, 1), ('Private', 'Occupied', 250.00, 1), ('Private', 'Available', 250.00, 1),
('Private', 'Available', 250.00, 1), ('Private', 'Maintenance', 250.00, 1), ('Private', 'Available', 250.00, 1),
('Private', 'Available', 250.00, 1),
('ICU', 'Occupied', 500.00, 1), ('ICU', 'Available', 500.00, 1), ('ICU', 'Available', 500.00, 1),
('ICU', 'Available', 500.00, 1), ('ICU', 'Occupied', 500.00, 1), ('ICU', 'Available', 500.00, 1),
('ICU', 'Available', 500.00, 1), ('ICU', 'Available', 500.00, 1), ('ICU', 'Maintenance', 500.00, 1),
('ICU', 'Available', 500.00, 1);


-- ---
-- 4. PATIENT (50 items)
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
-- 5. ADMISSION (20 items)
-- Removed 'bed_id' column
-- ---
INSERT INTO admission (admission_date, discharge_date, patient_id, room_id) VALUES
(NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 5 DAY, 1, 1), -- Discharged
(NOW() - INTERVAL 7 DAY, NULL, 2, 2), -- Current
(NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 1 DAY, 3, 3), -- Discharged (room now available)
(NOW() - INTERVAL 2 DAY, NULL, 4, 4), -- Current
(NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 10 DAY, 5, 5), -- Discharged (room is Maintenance)
(NOW() - INTERVAL 1 DAY, NULL, 6, 6), -- Current
(NOW() - INTERVAL 12 DAY, NULL, 7, 7), -- Current
(NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 6 DAY, 8, 8), -- Discharged (room now available)
(NOW() - INTERVAL 3 DAY, NULL, 9, 9), -- Current
(NOW() - INTERVAL 14 DAY, NULL, 10, 10), -- Current
(NOW() - INTERVAL 5 DAY, NULL, 11, 11), -- Current
(NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 4 DAY, 12, 12), -- Discharged (room now available)
(NOW() - INTERVAL 6 DAY, NULL, 13, 13), -- Current
(NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 7 DAY, 14, 14), -- Discharged (room now available)
(NOW() - INTERVAL 4 DAY, NULL, 15, 15), -- Current
(NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 2 DAY, 16, 16), -- Discharged (room now available)
(NOW() - INTERVAL 13 DAY, NULL, 17, 17), -- Current
(NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 8 DAY, 18, 18), -- Discharged (room is Maintenance)
(NOW() - INTERVAL 25 DAY, NULL, 19, 19), -- Current
(NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 3 DAY, 20, 20); -- Discharged (room now available)


-- ---
-- 6. MEDICINE (30 items)
-- ---
INSERT INTO medicine (name, dosage, stock_quantity, medicine_price) VALUES
('Paracetamol', '500mg', 100, 5.00), ( 'Ibuprofen', '400mg', 80, 8.50), ('Amoxicillin', '250mg', 60, 12.00),
('Aspirin', '100mg', 120, 3.50), ('Vitamin D', '1000IU', 90, 15.00), ('Lisinopril', '10mg', 45, 20.00),
('Metformin', '500mg', 70, 18.00), ('Atorvastatin', '20mg', 55, 30.00), ('Omeprazole', '20mg', 110, 10.00),
('Zoloft', '50mg', 40, 55.00), ('Ventolin', '100mcg', 35, 70.00), ('Doxycycline', '100mg', 65, 14.50),
('Prednisone', '5mg', 50, 22.00), ('Furosemide', '40mg', 75, 9.00), ('Warfarin', '5mg', 30, 40.00),
('Tramadol', '50mg', 95, 11.00), ('Ciproflox', '500mg', 85, 16.00), ('Cetirizine', '10mg', 150, 4.00),
('Levothyrox', '75mcg', 40, 28.00), ('Insulin Glarg', '100U/ml', 25, 120.00), ('Losartan', '50mg', 60, 25.00),
('Gabapentin', '300mg', 70, 17.50), ('Oxycodone', '10mg', 20, 80.00), ('Pantoprazole', '40mg', 80, 13.00),
('Fluconazole', '150mg', 55, 21.00), ('Hydrochlor', '25mg', 90, 7.50), ('Clopidogrel', '75mg', 45, 35.00),
('Amlodipine', '5mg', 65, 19.00), ('Metoprolol', '50mg', 100, 16.50), ( 'Azithromycin', '250mg', 50, 15.50);


-- ---
-- 7. APPOINTMENT (30 items)
-- Linking to Patient IDs 1-30 and User IDs 1-20 (Doctors)
-- ---
INSERT INTO appointment (appointment_date, consultation_fee, user_id, appointment_status, patient_id) VALUES
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
-- 8. TREATMENT (30 items)
-- Linking to Patient IDs 1-30, User IDs 1-20 (Doctors), and Medicine IDs 1-30
-- This combines Treatment and Prescription data by linking directly to one medicine.
-- ---
INSERT INTO treatment (notes, treatment_date, treatment_fee, patient_id, user_id, medicine_id) VALUES
-- User IDs 1-20 are Doctors
('Prescribed Paracetamol (1 daily)', NOW() - INTERVAL 10 DAY, 100.00, 1, 1, 1),
('Prescribed Lisinopril (2 daily)', NOW() - INTERVAL 8 DAY, 75.00, 2, 2, 6),
('Suture removal; prescribed Tramadol', NOW() - INTERVAL 6 DAY, 120.00, 3, 3, 16),
('Physical therapy; prescribed Ibuprofen', NOW() - INTERVAL 4 DAY, 90.00, 4, 4, 2),
('Vaccination; prescribed Insulin Glargine', NOW() - INTERVAL 2 DAY, 50.00, 5, 5, 20),
('Chemo Session 1; prescribed Oxycodone', NOW() - INTERVAL 1 DAY, 1500.00, 6, 6, 23),
('MRI review; prescribed Metformin', NOW(), 180.00, 7, 7, 7),
('Cast applied; prescribed Atorvastatin', NOW() - INTERVAL 12 DAY, 300.00, 8, 8, 8),
('Skin biopsy; prescribed Omeprazole', NOW() - INTERVAL 11 DAY, 150.00, 9, 9, 9),
('Endoscopy; prescribed Zoloft', NOW() - INTERVAL 10 DAY, 450.00, 10, 10, 10),
('Routine check-up; prescribed Paracetamol', NOW() - INTERVAL 9 DAY, 60.00, 11, 1, 1),
('Minor incision; prescribed Lisinopril', NOW() - INTERVAL 7 DAY, 250.00, 12, 4, 6),
('Growth assessment; prescribed Tramadol', NOW() - INTERVAL 5 DAY, 70.00, 13, 5, 16),
('Pain consult; prescribed Ibuprofen', NOW() - INTERVAL 3 DAY, 95.00, 14, 8, 2),
('Diabetic exam; prescribed Insulin Glargine', NOW() - INTERVAL 1 DAY, 85.00, 15, 1, 20),
('Assessment; prescribed Oxycodone', NOW() - INTERVAL 10 DAY, 100.00, 16, 2, 23),
('BP check; prescribed Metformin', NOW() - INTERVAL 8 DAY, 75.00, 17, 3, 7),
('Suture removal; prescribed Atorvastatin', NOW() - INTERVAL 6 DAY, 120.00, 18, 4, 8),
('PT session; prescribed Omeprazole', NOW() - INTERVAL 4 DAY, 90.00, 19, 5, 9),
('Vaccination; prescribed Zoloft', NOW() - INTERVAL 2 DAY, 50.00, 20, 6, 10),
('Chemo Session 2; prescribed Paracetamol', NOW() - INTERVAL 1 DAY, 1500.00, 21, 7, 1),
('MRI review; prescribed Lisinopril', NOW(), 180.00, 22, 8, 6),
('Cast applied; prescribed Tramadol', NOW() - INTERVAL 12 DAY, 300.00, 23, 9, 16),
('Skin biopsy; prescribed Ibuprofen', NOW() - INTERVAL 11 DAY, 150.00, 24, 10, 2),
('Endoscopy; prescribed Insulin Glargine', NOW() - INTERVAL 10 DAY, 450.00, 25, 1, 20),
('Routine check-up; prescribed Oxycodone', NOW() - INTERVAL 9 DAY, 60.00, 26, 2, 23),
('Minor incision; prescribed Metformin', NOW() - INTERVAL 7 DAY, 250.00, 27, 3, 7),
('Growth assessment; prescribed Atorvastatin', NOW() - INTERVAL 5 DAY, 70.00, 28, 4, 8),
('Pain consult; prescribed Omeprazole', NOW() - INTERVAL 3 DAY, 95.00, 29, 5, 9),
('Diabetic exam; prescribed Zoloft', NOW() - INTERVAL 1 DAY, 85.00, 30, 6, 10);


-- ---
-- 9. LAB_TEST (30 items)
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
-- 10. PAYMENT (30 items)
-- ---
INSERT INTO payment (total_amount, payment_method, payment_status, payment_date, patient_id) VALUES
(150.00, 'Cash', 'Paid', NOW() - INTERVAL 9 DAY, 1),
(250.00, 'Card', 'Paid', NOW() - INTERVAL 7 DAY, 2),
(100.00, 'Insurance', 'Pending', NOW() - INTERVAL 5 DAY, 3),
(500.00, 'Card', 'Paid', NOW() - INTERVAL 3 DAY, 4),
(75.00, 'Cash', 'Declined', NOW() - INTERVAL 1 DAY, 5),
(2000.00, 'Insurance', 'Paid', NOW(), 6),
(300.00, 'Cash', 'Paid', NOW() - INTERVAL 11 DAY, 7),
(450.00, 'Card', 'Pending', NOW() - INTERVAL 10 DAY, 8),
(120.00, 'Insurance', 'Paid', NOW() - INTERVAL 8 DAY, 9),
(600.00, 'Cash', 'Paid', NOW() - INTERVAL 6 DAY, 10),
(80.00, 'Card', 'Declined', NOW() - INTERVAL 4 DAY, 11),
(350.00, 'Insurance', 'Paid', NOW() - INTERVAL 2 DAY, 12),
(90.00, 'Cash', 'Paid', NOW() - INTERVAL 1 DAY, 13),
(150.00, 'Card', 'Pending', NOW() - INTERVAL 9 HOUR, 14),
(25.00, 'Cash', 'Paid', NOW() - INTERVAL 7 HOUR, 15),
(150.00, 'Cash', 'Paid', NOW() - INTERVAL 5 HOUR, 16),
(250.00, 'Card', 'Paid', NOW() - INTERVAL 3 HOUR, 17),
(100.00, 'Insurance', 'Pending', NOW() - INTERVAL 1 HOUR, 18),
(500.00, 'Card', 'Paid', NOW() - INTERVAL 30 MINUTE, 19),
(75.00, 'Cash', 'Declined', NOW() - INTERVAL 10 MINUTE, 20),
(2000.00, 'Insurance', 'Paid', NOW() - INTERVAL 2 DAY, 21),
(300.00, 'Cash', 'Paid', NOW() - INTERVAL 3 DAY, 22),
(450.00, 'Card', 'Pending', NOW() - INTERVAL 4 DAY, 23),
(120.00, 'Insurance', 'Paid', NOW() - INTERVAL 5 DAY, 24),
(600.00, 'Cash', 'Paid', NOW() - INTERVAL 6 DAY, 25),
(80.00, 'Card', 'Declined', NOW() - INTERVAL 7 DAY, 26),
(350.00, 'Insurance', 'Paid', NOW() - INTERVAL 8 DAY, 27),
(90.00, 'Cash', 'Paid', NOW() - INTERVAL 9 DAY, 28),
(150.00, 'Card', 'Pending', NOW() - INTERVAL 10 DAY, 29),
(25.00, 'Cash', 'Paid', NOW() - INTERVAL 11 DAY, 30);