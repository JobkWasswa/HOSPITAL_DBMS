-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema hospital_db
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `hospital_db` ;

-- -----------------------------------------------------
-- Schema hospital_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `hospital_db` DEFAULT CHARACTER SET utf8 ;
USE `hospital_db` ;

-- -----------------------------------------------------
-- Table `hospital_db`.`patient`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`patient` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`patient` (
  `patient_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `DOB` DATE NOT NULL,
  `gender` ENUM('MALE', 'FEMALE') NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`patient_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`department`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`department` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`department` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `location` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`department_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`doctor`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`doctor` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`doctor` (
  `doctor_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `specialization` VARCHAR(20) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `department_id` INT NOT NULL,
  PRIMARY KEY (`doctor_id`),
  INDEX `fk_doctor_department_idx` (`department_id` ASC) VISIBLE,
  CONSTRAINT `fk_doctor_department`
    FOREIGN KEY (`department_id`)
    REFERENCES `hospital_db`.`department` (`department_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`staff`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`staff` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`staff` (
  `staff_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `role` VARCHAR(15) NOT NULL,
  `shift` ENUM('morning', 'evening', 'night') NOT NULL,
  `department_id` INT NULL,
  `phone` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`staff_id`),
  INDEX `fk_staff_department1_idx` (`department_id` ASC) VISIBLE,
  UNIQUE INDEX `staff_id_UNIQUE` (`staff_id` ASC) VISIBLE,
  CONSTRAINT `fk_staff_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES `hospital_db`.`department` (`department_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`appointment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`appointment` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`appointment` (
  `appointment_id` INT NOT NULL AUTO_INCREMENT,
  `appointment_date` DATETIME NOT NULL,
  `consultation_fee` DECIMAL NOT NULL DEFAULT 0,
  `appointment_status` ENUM('Scheduled', 'Completed', 'Cancelled', 'No-Show') NOT NULL,
  `doctor_id` INT NOT NULL,
  `patient_id` INT NOT NULL,
  PRIMARY KEY (`appointment_id`),
  INDEX `fk_appointments_doctor1_idx` (`doctor_id` ASC) VISIBLE,
  INDEX `fk_appointments_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_appointments_doctor1`
    FOREIGN KEY (`doctor_id`)
    REFERENCES `hospital_db`.`doctor` (`doctor_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_appointments_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`treatment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`treatment` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`treatment` (
  `treatment_id` INT NOT NULL AUTO_INCREMENT,
  `notes` VARCHAR(45) NOT NULL,
  `treatment_date` DATETIME NOT NULL,
  `treatment_fee` DECIMAL NOT NULL DEFAULT 0,
  `patient_id` INT NULL,
  `doctor_id` INT NULL,
  PRIMARY KEY (`treatment_id`),
  INDEX `fk_treatment_patient1_idx` (`patient_id` ASC) VISIBLE,
  INDEX `fk_treatment_doctor1_idx` (`doctor_id` ASC) VISIBLE,
  CONSTRAINT `fk_treatment_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_treatment_doctor1`
    FOREIGN KEY (`doctor_id`)
    REFERENCES `hospital_db`.`doctor` (`doctor_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`lab_test`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`lab_test` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`lab_test` (
  `test_id` INT NOT NULL AUTO_INCREMENT,
  `test_type` VARCHAR(45) NOT NULL,
  `results` VARCHAR(20) NOT NULL,
  `test_date` DATETIME NOT NULL,
  `test_cost` DECIMAL NOT NULL DEFAULT 0,
  `patient_id` INT NULL,
  PRIMARY KEY (`test_id`),
  INDEX `fk_lab_test_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_lab_test_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`medicine`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`medicine` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`medicine` (
  `medicine_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `dosage` VARCHAR(10) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `medicine_price` DECIMAL NOT NULL DEFAULT 0,
  PRIMARY KEY (`medicine_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`prescription`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`prescription` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`prescription` (
  `prescription_id` INT NOT NULL AUTO_INCREMENT,
  `quantity` INT NOT NULL,
  `dosage_instructions` VARCHAR(45) NOT NULL,
  `treatment_id` INT NOT NULL,
  `medicine_id` INT NOT NULL,
  PRIMARY KEY (`prescription_id`),
  INDEX `fk_prescriptions_treatment1_idx` (`treatment_id` ASC) VISIBLE,
  INDEX `fk_prescriptions_medicine1_idx` (`medicine_id` ASC) VISIBLE,
  CONSTRAINT `fk_prescriptions_treatment1`
    FOREIGN KEY (`treatment_id`)
    REFERENCES `hospital_db`.`treatment` (`treatment_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_prescriptions_medicine1`
    FOREIGN KEY (`medicine_id`)
    REFERENCES `hospital_db`.`medicine` (`medicine_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`payment` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`payment` (
  `bill_id` INT NOT NULL AUTO_INCREMENT,
  `total_amount` DECIMAL NOT NULL,
  `payment_method` ENUM('Cash', 'Card', 'Insurance', 'Bank Transfer') NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Partial', 'Overdue') NOT NULL,
  `payment_date` DATETIME NOT NULL,
  `patient_id` INT NULL,
  PRIMARY KEY (`bill_id`),
  INDEX `fk_payment_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_payment_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`medical_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`medical_history` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`medical_history` (
  `history_id` INT NOT NULL AUTO_INCREMENT,
  `medical_condition` VARCHAR(45) NOT NULL,
  `diagnosis_date` DATETIME NOT NULL,
  `notes` VARCHAR(100) NULL,
  `patient_id` INT NULL,
  PRIMARY KEY (`history_id`),
  INDEX `fk_medical history_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_medical history_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`room`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`room` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`room` (
  `room_id` INT NOT NULL AUTO_INCREMENT,
  `room_type` ENUM('ICU', 'Private', 'General', 'Emergency') NOT NULL,
  `room_status` ENUM('Available', 'Occupied', 'Maintenance') NOT NULL,
  `daily_cost` DECIMAL NOT NULL DEFAULT 0,
  PRIMARY KEY (`room_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`bed`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`bed` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`bed` (
  `bed_id` INT NOT NULL AUTO_INCREMENT,
  `bed_no` VARCHAR(5) NOT NULL,
  `bed_type` ENUM('Standard', 'Electric', 'ICU') NOT NULL,
  `bed_status` ENUM('Available', 'Occupied', 'Maintenance') NOT NULL,
  `room_id` INT NULL,
  PRIMARY KEY (`bed_id`),
  INDEX `fk_bed_ward1_idx` (`room_id` ASC) VISIBLE,
  CONSTRAINT `fk_bed_ward1`
    FOREIGN KEY (`room_id`)
    REFERENCES `hospital_db`.`room` (`room_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`admission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`admission` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`admission` (
  `admission_id` INT NOT NULL AUTO_INCREMENT,
  `admission_date` DATETIME NOT NULL,
  `discharge_date` DATETIME NULL,
  `patient_id` INT NULL,
  `room_id` INT NULL,
  `bed_id` INT NULL,
  PRIMARY KEY (`admission_id`),
  INDEX `fk_admission_patient1_idx` (`patient_id` ASC) VISIBLE,
  INDEX `fk_admission_ward1_idx` (`room_id` ASC) VISIBLE,
  INDEX `fk_admission_bed1_idx` (`bed_id` ASC) VISIBLE,
  CONSTRAINT `fk_admission_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_admission_ward1`
    FOREIGN KEY (`room_id`)
    REFERENCES `hospital_db`.`room` (`room_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_admission_bed1`
    FOREIGN KEY (`bed_id`)
    REFERENCES `hospital_db`.`bed` (`bed_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db`.`users` ;

CREATE TABLE IF NOT EXISTS `hospital_db`.`users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(225) NOT NULL,
  `role` ENUM('doctor', 'staff') NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `staff_id` INT NULL,
  `doctor_id` INT NULL,
  PRIMARY KEY (`user_id`),
  INDEX `fk_users_staff1_idx` (`staff_id` ASC) VISIBLE,
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC) VISIBLE,
  INDEX `fk_users_doctor1_idx` (`doctor_id` ASC) VISIBLE,
  CONSTRAINT `fk_users_staff1`
    FOREIGN KEY (`staff_id`)
    REFERENCES `hospital_db`.`staff` (`staff_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_doctor1`
    FOREIGN KEY (`doctor_id`)
    REFERENCES `hospital_db`.`doctor` (`doctor_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;








//table2





-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema hospital_db2
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `hospital_db2` ;

-- -----------------------------------------------------
-- Schema hospital_db2
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `hospital_db2` DEFAULT CHARACTER SET utf8 ;
USE `hospital_db2` ;

-- -----------------------------------------------------
-- Table `hospital_db2`.`patient`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`patient` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`patient` (
  `patient_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `DOB` DATE NOT NULL,
  `gender` ENUM('MALE', 'FEMALE') NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`patient_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`department`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`department` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`department` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `location` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`department_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`users` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(225) NOT NULL,
  `role` ENUM('doctor', 'nurse', 'accountant', 'receptionist') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `specialization` VARCHAR(30) NULL,
  `shift` ENUM('Morning', 'Evening', 'Night') NULL,
  `is_active` TINYINT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `department_id` INT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC) VISIBLE,
  INDEX `fk_users_department1_idx` (`department_id` ASC) VISIBLE,
  CONSTRAINT `fk_users_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES `hospital_db2`.`department` (`department_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`appointment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`appointment` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`appointment` (
  `appointment_id` INT NOT NULL AUTO_INCREMENT,
  `appointment_date` DATETIME NOT NULL,
  `consultation_fee` DECIMAL NOT NULL DEFAULT 0,
  `appointment_status` ENUM('Scheduled', 'Completed', 'Cancelled', 'No-Show') NOT NULL,
  `patient_id` INT NULL,
  `user_id` INT NULL,
  PRIMARY KEY (`appointment_id`),
  INDEX `fk_appointments_patient1_idx` (`patient_id` ASC) VISIBLE,
  INDEX `fk_appointment_users1_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_appointments_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db2`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_appointment_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `hospital_db2`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`medicine`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`medicine` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`medicine` (
  `medicine_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `dosage` VARCHAR(10) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `medicine_price` DECIMAL NOT NULL DEFAULT 0,
  PRIMARY KEY (`medicine_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`treatment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`treatment` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`treatment` (
  `treatment_id` INT NOT NULL AUTO_INCREMENT,
  `notes` VARCHAR(45) NOT NULL,
  `treatment_date` DATETIME NOT NULL,
  `treatment_fee` DECIMAL NOT NULL DEFAULT 0,
  `patient_id` INT NOT NULL,
  `user_id` INT NULL,
  `medicine_id` INT NULL,
  PRIMARY KEY (`treatment_id`),
  INDEX `fk_treatment_patient1_idx` (`patient_id` ASC) VISIBLE,
  INDEX `fk_treatment_users1_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_treatment_medicine1_idx` (`medicine_id` ASC) VISIBLE,
  CONSTRAINT `fk_treatment_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db2`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_treatment_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `hospital_db2`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_treatment_medicine1`
    FOREIGN KEY (`medicine_id`)
    REFERENCES `hospital_db2`.`medicine` (`medicine_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`lab_test`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`lab_test` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`lab_test` (
  `test_id` INT NOT NULL AUTO_INCREMENT,
  `test_type` VARCHAR(45) NOT NULL,
  `results` VARCHAR(20) NOT NULL,
  `test_date` DATETIME NOT NULL,
  `test_cost` DECIMAL NOT NULL DEFAULT 0,
  `patient_id` INT NOT NULL,
  PRIMARY KEY (`test_id`),
  INDEX `fk_lab_test_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_lab_test_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db2`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`payment` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`payment` (
  `bill_id` INT NOT NULL AUTO_INCREMENT,
  `total_amount` DECIMAL NOT NULL,
  `payment_method` ENUM('Cash', 'Card', 'Insurance', 'Bank Transfer') NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Partial', 'Overdue') NOT NULL,
  `payment_date` DATETIME NOT NULL,
  `patient_id` INT NULL,
  PRIMARY KEY (`bill_id`),
  INDEX `fk_payment_patient1_idx` (`patient_id` ASC) VISIBLE,
  CONSTRAINT `fk_payment_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db2`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`room`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`room` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`room` (
  `room_id` INT NOT NULL AUTO_INCREMENT,
  `room_type` ENUM('ICU', 'Private', 'General', 'Emergency') NOT NULL,
  `room_status` ENUM('Available', 'Occupied', 'Maintenance') NOT NULL DEFAULT 'Available',
  `daily_cost` DECIMAL NOT NULL DEFAULT 0,
  `bed_stock` INT NOT NULL DEFAULT 10,
  PRIMARY KEY (`room_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`admission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`admission` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`admission` (
  `admission_id` INT NOT NULL AUTO_INCREMENT,
  `admission_date` DATETIME NOT NULL,
  `discharge_date` DATETIME NULL,
  `patient_id` INT NULL,
  `room_id` INT NULL,
  PRIMARY KEY (`admission_id`),
  INDEX `fk_admission_patient1_idx` (`patient_id` ASC) VISIBLE,
  INDEX `fk_admission_room1_idx` (`room_id` ASC) VISIBLE,
  CONSTRAINT `fk_admission_patient1`
    FOREIGN KEY (`patient_id`)
    REFERENCES `hospital_db2`.`patient` (`patient_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_admission_room1`
    FOREIGN KEY (`room_id`)
    REFERENCES `hospital_db2`.`room` (`room_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hospital_db2`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hospital_db2`.`user` ;

CREATE TABLE IF NOT EXISTS `hospital_db2`.`user` (
  `username` VARCHAR(16) NOT NULL,
  `email` VARCHAR(255) NULL,
  `password` VARCHAR(32) NOT NULL,
  `create_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
