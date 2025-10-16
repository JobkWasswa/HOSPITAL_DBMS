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
                    SUM(CASE WHEN gender = 'Other' THEN 1 ELSE 0 END) as other_patients,
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
                    SUM(CASE WHEN appointment_status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN appointment_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN appointment_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN appointment_status = 'No show' THEN 1 ELSE 0 END) as no_show,
                    SUM(consultation_fee) as total_revenue
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
                    SUM(CASE WHEN discharge_date IS NULL THEN 1 ELSE 0 END) as current_admissions,
                    SUM(CASE WHEN discharge_date IS NOT NULL THEN 1 ELSE 0 END) as discharged,
                    ROUND(AVG(DATEDIFF(COALESCE(discharge_date, CURDATE()), admission_date) + 1), 1) as avg_length_of_stay
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
                    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN payment_status = 'Declined' THEN 1 ELSE 0 END) as declined
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
                    COALESCE(SUM(total_amount), 0) as today_revenue
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
                    p.name as patient_name,
                    d.name as doctor_name,
                    a.appointment_status,
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
                    p.name as patient_name,
                    r.room_type,
                    adm.discharge_date
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
                    pay.bill_id,
                    pay.payment_date,
                    p.name as patient_name,
                    pay.total_amount,
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
                    d.name as doctor_name,
                    d.specialization,
                    COUNT(a.appointment_id) as total_appointments,
                    COALESCE(SUM(a.consultation_fee), 0) as appointment_revenue,
                    COUNT(t.treatment_id) as total_treatments,
                    COALESCE(SUM(t.treatment_fee), 0) as treatment_revenue,
                    (COUNT(DISTINCT a.patient_id) + COUNT(DISTINCT t.patient_id)) as unique_patients
                FROM doctor d
                LEFT JOIN appointment a ON d.doctor_id = a.doctor_id AND DATE(a.appointment_date) BETWEEN ? AND ?
                LEFT JOIN treatment t ON d.doctor_id = t.doctor_id AND DATE(t.treatment_date) BETWEEN ? AND ?
                GROUP BY d.doctor_id, d.name, d.specialization
                ORDER BY total_appointments DESC
            ";
            
            // Default to 30 days if no dates are provided (for non-export calls)
            $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?: date('Y-m-d');

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
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
                    COUNT(DISTINCT s.staff_id) as total_staff,
                    COUNT(a.appointment_id) as total_appointments
                FROM department d
                LEFT JOIN doctor doc ON d.department_id = doc.department_id
                LEFT JOIN staff s ON d.department_id = s.department_id
                LEFT JOIN appointment a ON doc.doctor_id = a.doctor_id
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
                    (COUNT(b.bed_id) - SUM(CASE WHEN b.bed_status = 'Occupied' THEN 1 ELSE 0 END)) as available_beds,
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
            $total = (int)$this->pdo->query("SELECT COUNT(*) AS c FROM patient")->fetch(PDO::FETCH_ASSOC)['c'];
            if ($total === 0) { return []; }
            $stmt = $this->pdo->prepare("SELECT gender, COUNT(*) AS count FROM patient GROUP BY gender");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['percentage'] = round(($r['count'] / $total) * 100, 2);
            }
            return $rows;
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
                    DATE_FORMAT(appointment_date, '%b %Y') as month_name,
                    COUNT(*) as appointments,
                    COALESCE(SUM(consultation_fee), 0) as revenue
                FROM appointment
                WHERE DATE(appointment_date) BETWEEN ? AND ?
                GROUP BY month_name
                ORDER BY STR_TO_DATE(CONCAT('01 ', month_name), '%d %b %Y')
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

    /**
     * Get revenue aggregated by source for accountant chart
     */
    public function getRevenueBySource($startDate, $endDate) {
        try {
            $results = [];

            // Appointments revenue
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(consultation_fee), 0) as total_amount, COUNT(*) as cnt FROM appointment WHERE DATE(appointment_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Appointments', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            // Lab tests revenue
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(test_cost), 0) as total_amount, COUNT(*) as cnt FROM lab_test WHERE DATE(test_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Lab Tests', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            // Treatments revenue
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(treatment_fee), 0) as total_amount, COUNT(*) as cnt FROM treatment WHERE DATE(treatment_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Treatments', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            // Prescriptions revenue (quantity * medicine_price)
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(pr.quantity * m.medicine_price), 0) as total_amount, COUNT(*) as cnt FROM prescription pr JOIN medicine m ON pr.medicine_id = m.medicine_id JOIN treatment t ON pr.treatment_id = t.treatment_id WHERE DATE(t.treatment_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Prescriptions', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            // Admissions revenue (days * room.daily_cost)
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM( (DATEDIFF(COALESCE(discharge_date, CURDATE()), admission_date) + 1) * r.daily_cost ), 0) as total_amount, COUNT(*) as cnt FROM admission a JOIN room r ON a.room_id = r.room_id WHERE DATE(a.admission_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Admissions', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            return $results;
        } catch (PDOException $e) {
            error_log('Reports getRevenueBySource error: ' . $e->getMessage());
            return [];
        }
    }
}
?>