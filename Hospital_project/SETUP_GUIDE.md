# Hospital Management System - Setup Guide

## Prerequisites

Before running the Hospital Management System, ensure you have the following installed:

### Required Software
1. **WAMP Server** (Windows, Apache, MySQL, PHP)
   - Download from: https://www.wampserver.com/
   - Includes: Apache 2.4.x, MySQL 8.0.x, PHP 8.1.x

2. **Web Browser**
   - Chrome, Firefox, Edge, or Safari (latest version)

3. **Text Editor** (Optional)
   - VS Code, Sublime Text, or Notepad++

## Installation Steps

### Step 1: Start WAMP Server
1. Launch WAMP Server from your desktop or start menu
2. Wait for all services to start (green icon in system tray)
3. Ensure Apache and MySQL services are running

### Step 2: Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `hospital_db`
3. Import your database schema (if you have a .sql file)
4. Or run the SQL commands from your MySQL Workbench project

### Step 3: Project Configuration
1. Navigate to your WAMP `www` directory: `C:\wamp64\www\`
2. Place the `Hospital_project` folder in the `www` directory
3. Your project path should be: `C:\wamp64\www\Hospital_project\`

### Step 4: Database Connection
Edit `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Leave empty for WAMP default
```

### Step 5: Application URL
Edit `config/config.php` if needed:
```php
define('APP_URL', 'http://localhost/Hospital_project');
```

## Running the Application

### Step 1: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/Hospital_project`
3. You should see the login page

### Step 2: Create User Accounts
Since this is a new installation, you'll need to create user accounts in the database.

#### Option A: Using phpMyAdmin
1. Go to: http://localhost/phpmyadmin
2. Select `hospital_db` database
3. Go to `users` table
4. Insert sample users:

```sql
-- Doctor Account
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_active) 
VALUES ('doctor1', 'doctor@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'John', 'Smith', 1);

-- Staff Account (Receptionist)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_active) 
VALUES ('receptionist1', 'reception@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Jane', 'Doe', 1);

-- Staff Account (Nurse)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_active) 
VALUES ('nurse1', 'nurse@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Mike', 'Johnson', 1);

-- Staff Account (Accountant)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_active) 
VALUES ('accountant1', 'accounting@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Sarah', 'Wilson', 1);
```

**Note:** The password hash above is for the password "password" (for testing only)

#### Option B: Using MySQL Workbench
1. Connect to your local MySQL server
2. Select `hospital_db` database
3. Run the INSERT statements above

### Step 3: Create Supporting Records
You'll also need to create records in related tables:

```sql
-- Create a doctor record
INSERT INTO doctor (name, specialization, phone, department_id) 
VALUES ('Dr. John Smith', 'Cardiology', '555-0101', 1);

-- Create staff records
INSERT INTO staff (name, role, shift, department_id) 
VALUES ('Jane Doe', 'Receptionist', 'Morning', 1);

INSERT INTO staff (name, role, shift, department_id) 
VALUES ('Mike Johnson', 'Nurse', 'Evening', 1);

INSERT INTO staff (name, role, shift, department_id) 
VALUES ('Sarah Wilson', 'Accountant', 'Morning', 1);

-- Create a department
INSERT INTO department (name, location) 
VALUES ('General Medicine', 'First Floor');

-- Create rooms and beds
INSERT INTO room (room_type, room_status, daily_cost) 
VALUES ('General', 'Available', 100.00);

INSERT INTO bed (bed_no, bed_type, bed_status, room_id) 
VALUES ('A1', 'Standard', 'Available', 1);
```

### Step 4: Link Users to Staff/Doctor Records
```sql
-- Link doctor user to doctor record
UPDATE users SET doctor_id = 1 WHERE username = 'doctor1';

-- Link staff users to staff records
UPDATE users SET staff_id = 1 WHERE username = 'receptionist1';
UPDATE users SET staff_id = 2 WHERE username = 'nurse1';
UPDATE users SET staff_id = 3 WHERE username = 'accountant1';
```

## Testing the Application

### Step 1: Login Test
1. Go to: http://localhost/Hospital_project
2. Try logging in with:
   - **Doctor**: username: `doctor1`, password: `password`
   - **Receptionist**: username: `receptionist1`, password: `password`
   - **Nurse**: username: `nurse1`, password: `password`
   - **Accountant**: username: `accountant1`, password: `password`

### Step 2: Feature Testing
1. **Doctor Dashboard**: Should show doctor-specific features
2. **Staff Dashboard**: Should show role-based features
3. **Patient Management**: Test CRUD operations
4. **Appointment Scheduling**: Test appointment creation
5. **Reports**: Test various report types

## Troubleshooting

### Common Issues

#### 1. "Database connection failed"
- Check if WAMP Server is running
- Verify database credentials in `config/database.php`
- Ensure `hospital_db` database exists

#### 2. "Page not found" or 404 errors
- Check if Apache is running in WAMP
- Verify project is in `C:\wamp64\www\Hospital_project\`
- Check URL: `http://localhost/Hospital_project`

#### 3. "Access denied" errors
- Check file permissions
- Ensure PHP is running
- Check error logs in WAMP

#### 4. Login not working
- Verify user accounts exist in database
- Check password hashes
- Ensure users are linked to staff/doctor records

#### 5. CSS/JS not loading
- Check if files exist in `public/css/` and `public/js/`
- Verify file permissions
- Check browser console for errors

### Error Logs
- WAMP Error Logs: `C:\wamp64\logs\`
- PHP Error Logs: `C:\wamp64\logs\php_error.log`
- Apache Error Logs: `C:\wamp64\logs\apache_error.log`

### Performance Optimization
1. **Enable PHP OPcache** (in php.ini)
2. **Optimize MySQL** settings
3. **Use HTTPS** in production
4. **Enable Gzip** compression

## Security Considerations

### For Production Use
1. **Change default passwords**
2. **Use strong password hashing**
3. **Enable HTTPS**
4. **Set proper file permissions**
5. **Regular security updates**
6. **Database backup strategy**

### File Permissions
- PHP files: 644
- Directories: 755
- Config files: 600 (restrictive)

## Backup and Maintenance

### Database Backup
```bash
mysqldump -u root -p hospital_db > hospital_backup.sql
```

### File Backup
- Backup the entire `Hospital_project` folder
- Include database backups
- Test restore procedures

## Support and Documentation

### Project Structure
- See `PROJECT_STRUCTURE.md` for detailed file organization
- See `database_schema.md` for database structure

### Development
- Use version control (Git)
- Follow coding standards
- Document changes
- Test thoroughly

## Next Steps

1. **Customize** the system for your specific needs
2. **Add** additional features as required
3. **Train** users on the system
4. **Plan** for production deployment
5. **Implement** backup and monitoring

---

**Note**: This is a development setup. For production use, additional security measures and optimizations are required.
