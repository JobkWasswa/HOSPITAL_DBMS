<?php
/**
 * Reports Model
 * Hospital Management System
 */

class Reports {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // =================================================================
    // 1. OVERVIEW / STATISTICS METHODS
    // =================================================================

    /** Get patient statistics (totals) */
    public function getPatientStatistics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_patients,
                    SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male_patients,
                    SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female_patients,
                    SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_patients_30_days
                FROM patient
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("Reports getPatientStatistics error: " . $e->getMessage());
            return [];
        }
    }

    /** Get appointment statistics within a date range */
    public function getAppointmentStatistics($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(consultation_fee) as expected_revenue
                FROM appointment
                WHERE DATE(appointment_date) BETWEEN ? AND ?
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAppointmentStatistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get admission statistics within a date range */
    public function getAdmissionStatistics($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_admissions,
                    SUM(CASE WHEN status = 'Current' THEN 1 ELSE 0 END) as current_admissions,
                    SUM(CASE WHEN status = 'Discharged' THEN 1 ELSE 0 END) as discharged_admissions,
                    SUM(total_cost) as total_billings
                FROM admission
                WHERE DATE(admission_date) BETWEEN ? AND ?
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAdmissionStatistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get payment status breakdown for overview */
    public function getPaymentStatusBreakdown() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid,
                    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending
                FROM payment
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPaymentStatusBreakdown error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get today's total revenue */
    public function getTodayRevenue() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COALESCE(SUM(amount), 0) as today_revenue
                FROM payment
                WHERE DATE(payment_date) = CURDATE()
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['today_revenue'] ?? 0;
        } catch (PDOException $e) {
            error_log("Reports getTodayRevenue error: " . $e->getMessage());
            return 0;
        }
    }

    // =================================================================
    // 2. DETAILED LIST / TABLE METHODS
    // =================================================================

    /** Get list of all patients */
    public function getPatientsList() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT patient_id, name, DOB, gender, address, phone
                FROM patient
                ORDER BY name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPatientsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get detailed list of appointments in a date range */
    public function getAppointmentsList($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    a.appointment_id,
                    a.appointment_date,
                    p.name as patient_n,
                    d.name as doctor_n,
                    a.status,
                    a.consultation_fee
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN doctor d ON a.doctor_id = d.doctor_id
                WHERE DATE(a.appointment_date) BETWEEN ? AND ?
                ORDER BY a.appointment_date DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAppointmentsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get detailed list of admissions in a date range */
    public function getAdmissionsList($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    adm.admission_id,
                    adm.admission_date,
                    p.name as patient_n
                    r.room_type,
                    adm.status,
                    
                FROM admission adm
                JOIN patient p ON adm.patient_id = p.patient_id
                JOIN room r ON adm.room_id = r.room_id
                WHERE DATE(adm.admission_date) BETWEEN ? AND ?
                ORDER BY adm.admission_date DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAdmissionsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get detailed list of payments/transactions in a date range */
    public function getPaymentsList($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    pay.payment_id,
                    pay.payment_date,
                    p.first_name as patient_fn,
                    p.last_name as patient_ln,
                    pay.amount,
                    pay.payment_method,
                    pay.payment_status
                FROM payment pay
                JOIN patient p ON pay.patient_id = p.patient_id
                WHERE DATE(pay.payment_date) BETWEEN ? AND ?
                ORDER BY pay.payment_date DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPaymentsList error: " . $e->getMessage());
            return [];
        }
    }

    /** Get doctor performance/appointment list in a date range */
    public function getDoctorPerformance($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    d.doctor_id,
                    d.first_name,
                    d.last_name,
                    d.specialty,
                    COUNT(a.appointment_id) as total_appointments,
                    SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed_appointments,
                    COALESCE(SUM(a.consultation_fee), 0) as total_revenue
                FROM doctor d
                LEFT JOIN appointment a ON d.doctor_id = a.doctor_id
                WHERE (a.appointment_id IS NULL) OR (DATE(a.appointment_date) BETWEEN ? AND ?)
                GROUP BY d.doctor_id, d.first_name, d.last_name, d.specialty
                ORDER BY total_appointments DESC
            ";
            
            // Default to 30 days if no dates are provided (for non-export calls)
            $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?: date('Y-m-d');

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getDoctorPerformance error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get department list/statistics */
    public function getDepartmentsList() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.name as department_name,
                    d.location,
                    COUNT(DISTINCT doc.doctor_id) as total_doctors,
                    COUNT(DISTINCT s.staff_id) as total_staff
                FROM department d
                LEFT JOIN doctor doc ON d.department_id = doc.department_id
                LEFT JOIN staff s ON d.department_id = s.department_id
                GROUP BY d.department_id, d.name, d.location
                ORDER BY total_doctors DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getDepartmentsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get room utilization report (list) */
    public function getRoomUtilization($startDate = null, $endDate = null) {
        // Using current utilization status for simplicity in this report
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.room_id,
                    r.room_type,
                    r.daily_cost,
                    COUNT(b.bed_id) as total_beds,
                    SUM(CASE WHEN b.bed_status = 'Occupied' THEN 1 ELSE 0 END) as occupied_beds,
                    ROUND((SUM(CASE WHEN b.bed_status = 'Occupied' THEN 1 ELSE 0 END) / COUNT(b.bed_id)) * 100, 2) as utilization_percentage
                FROM room r
                LEFT JOIN bed b ON r.room_id = b.room_id
                GROUP BY r.room_id, r.room_type, r.daily_cost
                ORDER BY utilization_percentage DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getRoomUtilization error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get medicine inventory report (list) */
    public function getInventoryReport() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    medicine_id,
                    name,
                    dosage,
                    stock_quantity,
                    medicine_price,
                    (stock_quantity * medicine_price) as total_value,
                    CASE 
                        WHEN stock_quantity <= 5 THEN 'Critical'
                        ELSE 'Adequate'
                    END as stock_status
                FROM medicine
                ORDER BY stock_quantity ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getInventoryReport error: " . $e->getMessage());
            return [];
        }
    }

    /** Get patient demographics report (age/gender breakdown) */
    public function getPatientDemographics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    gender,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) < 18 THEN 1 ELSE 0 END) as age_0_17,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) BETWEEN 18 AND 45 THEN 1 ELSE 0 END) as age_18_45,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, DOB, CURDATE()) > 45 THEN 1 ELSE 0 END) as age_45_plus,
                    COUNT(*) as total_count
                FROM patient
                GROUP BY gender
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPatientDemographics error: " . $e->getMessage());
            return [];
        }
    }
    
    /** Get monthly trends report (appointments/revenue over time) */
    public function getMonthlyTrends($startDate = null, $endDate = null) {
        try {
            // Default to current year if dates are not provided
            $startDate = $startDate ?: date('Y-01-01');
            $endDate = $endDate ?: date('Y-12-31');
            
            $sql = "
                SELECT 
                    DATE_FORMAT(appointment_date, '%Y-%m') as period,
                    COUNT(*) as appointments,
                    COALESCE(SUM(consultation_fee), 0) as revenue
                FROM appointment
                WHERE DATE(appointment_date) BETWEEN ? AND ?
                GROUP BY period
                ORDER BY period
            ";
            $params = [$startDate, $endDate];
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getMonthlyTrends error: " . $e->getMessage());
            return [];
        }
    }
}
?>