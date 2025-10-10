# Hospital Management System - Project Structure

## New Organized Project Structure

```
Hospital_project/
├── config/
│   ├── database.php          # Database connection
│   ├── config.php            # General configuration
│   └── constants.php         # System constants
├── public/
│   ├── index.php             # Main entry point
│   ├── login.php             # Login page
│   ├── logout.php            # Logout handler
│   ├── css/
│   │   ├── style.css         # Main stylesheet
│   │   ├── login.css         # Login page styles
│   │   └── dashboard.css     # Dashboard styles
│   ├── js/
│   │   ├── main.js           # Main JavaScript
│   │   ├── login.js          # Login functionality
│   │   └── dashboard.js      # Dashboard functionality
│   └── images/
│       ├── logo.png          # Hospital logo
│       └── icons/            # System icons
├── src/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── PatientController.php
│   │   ├── DoctorController.php
│   │   ├── StaffController.php
│   │   ├── AppointmentController.php
│   │   ├── AdmissionController.php
│   │   ├── PharmacyController.php
│   │   ├── LabController.php
│   │   └── ReportController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Patient.php
│   │   ├── Doctor.php
│   │   ├── Staff.php
│   │   ├── Appointment.php
│   │   ├── Admission.php
│   │   ├── Medicine.php
│   │   ├── LabTest.php
│   │   └── Report.php
│   ├── views/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── logout.php
│   │   ├── doctor/
│   │   │   ├── dashboard.php
│   │   │   ├── patients.php
│   │   │   ├── appointments.php
│   │   │   ├── treatments.php
│   │   │   └── reports.php
│   │   ├── staff/
│   │   │   ├── dashboard.php
│   │   │   ├── patients.php
│   │   │   ├── appointments.php
│   │   │   ├── admissions.php
│   │   │   ├── pharmacy.php
│   │   │   ├── lab.php
│   │   │   ├── billing.php
│   │   │   └── reports.php
│   │   └── layouts/
│   │       ├── header.php
│   │       ├── footer.php
│   │       ├── sidebar.php
│   │       └── navigation.php
│   ├── helpers/
│   │   ├── Auth.php
│   │   ├── Database.php
│   │   ├── Validation.php
│   │   ├── Security.php
│   │   └── Utils.php
│   └── includes/
│       ├── functions.php
│       ├── queries.php
│       └── errors.php
├── assets/
│   ├── uploads/
│   │   ├── patients/
│   │   ├── documents/
│   │   └── reports/
│   └── temp/
├── logs/
│   ├── error.log
│   ├── access.log
│   └── debug.log
├── docs/
│   ├── database_schema.md
│   ├── API.md
│   └── README.md
└── tests/
    ├── unit/
    ├── integration/
    └── fixtures/
```

## Key Improvements

### 1. **Clear Separation of Concerns**
- **Controllers**: Handle business logic and user requests
- **Models**: Handle database operations
- **Views**: Handle presentation and user interface
- **Helpers**: Reusable utility functions

### 2. **Easy Debugging Structure**
- **Centralized error handling** in `src/includes/errors.php`
- **Logging system** in `logs/` directory
- **Clear file naming** conventions
- **Modular structure** for easy testing

### 3. **Security Enhancements**
- **Centralized authentication** in `src/helpers/Auth.php`
- **Security utilities** in `src/helpers/Security.php`
- **Input validation** in `src/helpers/Validation.php`

### 4. **Maintainable Code**
- **Single responsibility** principle
- **DRY (Don't Repeat Yourself)** approach
- **Consistent naming** conventions
- **Documentation** in `docs/` directory

### 5. **Scalable Architecture**
- **Easy to add new features**
- **Modular design** for team development
- **Clear dependencies** and relationships
- **Testing framework** ready

## File Responsibilities

### **Configuration Files**
- `config/database.php`: Database connection settings
- `config/config.php`: General system configuration
- `config/constants.php`: System constants and enums

### **Controllers**
- Handle HTTP requests
- Process business logic
- Call appropriate models
- Return responses to views

### **Models**
- Database operations (CRUD)
- Data validation
- Business rules
- Data relationships

### **Views**
- User interface presentation
- Form handling
- Data display
- User interaction

### **Helpers**
- Authentication and authorization
- Database utilities
- Input validation
- Security functions
- Common utilities

## Benefits of This Structure

1. **Easy to Navigate**: Clear folder structure
2. **Easy to Debug**: Centralized error handling
3. **Easy to Maintain**: Modular design
4. **Easy to Scale**: Add new features easily
5. **Easy to Test**: Separated concerns
6. **Easy to Deploy**: Clear dependencies
7. **Easy to Collaborate**: Team-friendly structure

## Next Steps

1. **Create the new folder structure**
2. **Move existing files** to appropriate locations
3. **Update file paths** and includes
4. **Create new controller files**
5. **Implement the new authentication system**
6. **Test the new structure**
