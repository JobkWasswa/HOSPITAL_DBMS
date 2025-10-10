# Hospital Management System - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Start WAMP Server
1. Launch WAMP Server
2. Wait for green icon in system tray
3. Ensure Apache and MySQL are running

### Step 2: Setup Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose file: `setup_database.sql` (from this project folder)
4. Click "Go" to import

### Step 3: Access Application
1. Open browser
2. Go to: http://localhost/Hospital_project
3. You should see the login page

### Step 4: Login with Test Accounts
Use these credentials to test different roles:

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Doctor** | `doctor1` | `password` | Full system access |
| **Receptionist** | `receptionist1` | `password` | Patient registration, appointments |
| **Nurse** | `nurse1` | `password` | Patient admissions, care |
| **Accountant** | `accountant1` | `password` | Billing, payments |

## 🎯 What You Can Test

### Doctor Features
- ✅ View dashboard with statistics
- ✅ Manage patients (add, edit, view)
- ✅ Schedule appointments
- ✅ View reports and analytics
- ✅ Access all modules

### Staff Features (Role-based)
- ✅ **Receptionist**: Patient registration, appointment scheduling
- ✅ **Nurse**: Patient admissions, bed management
- ✅ **Accountant**: Billing, payment processing
- ✅ View role-specific dashboards

### System Features
- ✅ User authentication and authorization
- ✅ Role-based access control
- ✅ Responsive design (mobile-friendly)
- ✅ Search and pagination
- ✅ Form validation
- ✅ Error handling

## 🔧 Troubleshooting

### Common Issues

**❌ "Database connection failed"**
- Check if WAMP is running (green icon)
- Verify database `hospital_db` exists
- Check `config/database.php` settings

**❌ "Page not found" (404)**
- Ensure project is in `C:\wamp64\www\Hospital_project\`
- Check if Apache is running
- Try: http://localhost/Hospital_project/public/

**❌ "Access denied"**
- Check file permissions
- Ensure PHP is enabled
- Check WAMP error logs

**❌ Login not working**
- Verify user accounts exist in database
- Check password (should be "password")
- Ensure users are linked to staff/doctor records

### Quick Fixes

1. **Restart WAMP Server**
2. **Clear browser cache**
3. **Check WAMP error logs**: `C:\wamp64\logs\`
4. **Verify database**: http://localhost/phpmyadmin

## 📱 Mobile Testing

The system is responsive and works on:
- ✅ Desktop browsers
- ✅ Tablets
- ✅ Mobile phones
- ✅ Different screen sizes

## 🎨 Customization

### Change Colors/Theme
Edit: `public/css/style.css`

### Modify Layout
Edit: `src/views/layouts/header.php` and `footer.php`

### Add Features
- Models: `src/models/`
- Controllers: `src/controllers/`
- Views: `src/views/`

## 📊 Sample Data

The setup script includes:
- 5 sample patients
- 5 doctors with specializations
- 5 staff members with different roles
- Sample appointments, treatments, and payments
- Medicine inventory
- Room and bed assignments

## 🔐 Security Notes

**For Development Only:**
- Default password is "password" for all accounts
- Change passwords for production use
- Enable HTTPS for production
- Set proper file permissions

## 📞 Support

If you encounter issues:
1. Check the full `SETUP_GUIDE.md`
2. Review error logs in WAMP
3. Verify database setup
4. Check file permissions

## 🎉 Success!

If everything works, you should see:
- ✅ Login page loads
- ✅ Can login with test accounts
- ✅ Dashboards show correctly
- ✅ Can navigate between modules
- ✅ Forms work properly

**Congratulations! Your Hospital Management System is ready to use!** 🏥
