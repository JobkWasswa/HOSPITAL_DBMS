# Medical System Data Dictionary

## Database Schema Overview

This document outlines the complete database schema for the Hospital Management System, including all tables, fields, data types, and relationships.

---

## 1. medical_history
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| history_id | INT | PK | Primary key for medical history records |
| medical_condition | VARCHAR(45) | | Medical condition or diagnosis |
| diagnosis_date | DATETIME | | Date when condition was diagnosed |
| notes | VARCHAR(100) | | Additional notes about the condition |

---

## 2. lab_test
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| test_id | INT | PK | Primary key for lab tests |
| test_type | VARCHAR(45) | | Type of laboratory test performed |
| results | VARCHAR(20) | | Test results |
| test_date | DATETIME | | Date when test was performed |
| test_cost | DECIMAL | | Cost of the lab test |
| patient_id | INT | FK | Foreign key referencing patient table |

---

## 3. payment
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| bill_id | INT | PK | Primary key for payment records |
| total_amount | DECIMAL | | Total amount to be paid |
| payment_method | ENUM("Cash", "Card", "Insurance") | | Method of payment |
| payment_status | ENUM("Pending", "Paid", "Declined") | | Current payment status |
| payment_date | DATETIME | | Date of payment |
| patient_id | INT | FK | Foreign key referencing patient table |

---

## 4. staff
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| staff_id | INT | PK | Primary key for staff members |
| name | VARCHAR(45) | | Full name of staff member |
| role | ENUM("Nurse", "Cleaner", "Receptionist", "Accountant") | | Staff role/position |
| shift | ENUM("Morning", "Evening", "Night") | | Work shift |
| department_id | INT | FK | Foreign key referencing department table |

---

## 5. department
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| department_id | INT | PK | Primary key for departments |
| name | VARCHAR(45) | | Department name |
| location | VARCHAR(45) | | Physical location of department |

---

## 6. patient
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| patient_id | INT | PK | Primary key for patients |
| name | VARCHAR(30) | | Patient's full name |
| DOB | DATE | | Date of birth |
| gender | ENUM("Male", "Female", "Other") | | Patient's gender |
| address | VARCHAR(45) | | Patient's address |
| phone | VARCHAR(15) | | Contact phone number |

---

## 7. room
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| room_id | INT | PK | Primary key for rooms |
| room_type | ENUM("General", "Private", "ICU") | | Type of room |
| room_status | ENUM("Available", "Occupied", "Maintenance") | | Current room status |
| daily_cost | DECIMAL | | Daily cost for room occupancy |

---

## 8. admission
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| admission_id | INT | PK | Primary key for admissions |
| admission_date | DATETIME | | Date and time of admission |
| discharge_date | DATETIME | | Date and time of discharge |
| patient_id | INT | FK | Foreign key referencing patient table |
| room_id | INT | FK | Foreign key referencing room table |
| bed_id | INT | FK | Foreign key referencing bed table (optional) |

---

## 9. doctor
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| doctor_id | INT | PK | Primary key for doctors |
| name | VARCHAR(20) | | Doctor's name |
| specialization | VARCHAR(20) | | Medical specialization |
| phone | VARCHAR(15) | | Contact phone number |
| department_id | INT | FK | Foreign key referencing department table |

---

## 10. bed
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| bed_id | INT | PK | Primary key for beds |
| bed_no | VARCHAR(5) | | Bed number/identifier |
| bed_type | ENUM("Standard", "ICU", "Emergency") | | Type of bed |
| bed_status | ENUM("Available", "Occupied", "Maintenance") | | Current bed status |
| room_id | INT | FK | Foreign key referencing room table |

---

## 11. medicine
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| medicine_id | INT | PK | Primary key for medicines |
| name | VARCHAR(20) | | Medicine name |
| dosage | VARCHAR(10) | | Standard dosage |
| stock_quantity | INT | | Current stock quantity |
| medicine_price | DECIMAL | | Price per unit |

---

## 12. treatment
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| treatment_id | INT | PK | Primary key for treatments |
| notes | VARCHAR(45) | | Treatment notes |
| treatment_date | DATETIME | | Date of treatment |
| treatment_fee | DECIMAL | | Cost of treatment |
| patient_id | INT | FK | Foreign key referencing patient table |
| doctor_id | INT | FK | Foreign key referencing doctor table |

---

## 13. prescription
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| prescription_id | INT | PK | Primary key for prescriptions |
| quantity | INT | | Quantity of medicine prescribed |
| dosage_instructions | VARCHAR(45) | | Instructions for taking medicine |
| treatment_id | INT | FK | Foreign key referencing treatment table |
| medicine_id | INT | FK | Foreign key referencing medicine table |

---

## 14. appointment
| Field Name | Data Type | Key | Description |
|------------|-----------|-----|-------------|
| appointment_id | INT | PK | Primary key for appointments |
| appointment_date | DATETIME | | Date and time of appointment |
| consultation_fee | DECIMAL | | Fee for consultation |
| doctor_id | INT | FK | Foreign key referencing doctor table |
| appointment_status | ENUM("Scheduled", "Completed", "Cancelled", "No show") | | Current appointment status |
| patient_id | INT | FK | Foreign key referencing patient table |

---

## Entity Relationships

### Core Entities:
- **Patient** - Central entity connected to most other tables
- **Doctor** - Medical professionals providing care
- **Staff** - Hospital employees (nurses, receptionists, etc.)
- **Department** - Hospital organizational units

### Medical Care Flow:
1. **Patient** → **Appointment** → **Doctor**
2. **Patient** → **Treatment** → **Doctor**
3. **Treatment** → **Prescription** → **Medicine**
4. **Patient** → **Lab Test**
5. **Patient** → **Admission** → **Room** (→ optional **Bed**)

### Administrative Flow:
1. **Staff** → **Department**
2. **Doctor** → **Department**
3. **Patient** → **Payment**
4. **Patient** → **Medical History**

---

## User Interface Implications

Based on this schema, the following user interfaces are needed:

### For Doctors:
- Patient management and medical history
- Appointment scheduling and management
- Treatment and prescription management
- Lab test ordering and results review

### For Nurses:
- Patient monitoring and care
- Medication administration
- Room and bed management
- Admission coordination

### For Receptionists:
- Patient registration
- Appointment booking
- Payment processing
- General administrative tasks

### For Pharmacists:
- Medicine inventory management
- Prescription fulfillment
- Stock monitoring

### For Lab Technicians:
- Lab test management
- Results entry and reporting

### For Administrators:
- System oversight
- Financial reporting
- Staff management
- Department coordination

---

*Last Updated: January 2025*
*Schema Version: 1.0*
