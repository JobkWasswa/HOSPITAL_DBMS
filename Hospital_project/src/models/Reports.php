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
    // 2. CHART / GRAPH DATA METHODS
    // =================================================================

    public function getAppointmentsPerDay($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT DATE(appointment_date) AS day, COUNT(*) AS count
                FROM appointment
                WHERE DATE(appointment_date) BETWEEN ? AND ?
                GROUP BY DATE(appointment_date)
                ORDER BY DATE(appointment_date)
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Reports getAppointmentsPerDay error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAppointmentStatusShare($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT appointment_status AS status, COUNT(*) AS count
                FROM appointment
                WHERE DATE(appointment_date) BETWEEN ? AND ?
                GROUP BY appointment_status
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Reports getAppointmentStatusShare error: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopMedicinesByQuantity($limit = 10, $startDate = null, $endDate = null) {
        try {
            $params = [];
            $where = 'WHERE t.medicine_id IS NOT NULL';
            if ($startDate && $endDate) {
                $where .= ' AND DATE(t.treatment_date) BETWEEN ? AND ?';
                $params = [$startDate, $endDate];
            }
            $sql = "
                SELECT 
                    m.name AS medicine_name, 
                    COUNT(t.treatment_id) AS total_quantity
                FROM treatment t                    
                JOIN medicine m ON t.medicine_id = m.medicine_id
                $where
                GROUP BY m.medicine_id, m.name
                ORDER BY total_quantity DESC
                LIMIT " . intval($limit) . "
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Reports getTopMedicinesByQuantity error: ' . $e->getMessage());
            return [];
        }
    }

    // =================================================================
    // 3. DETAILED LIST / TABLE METHODS
    // =================================================================

    public function getPatientsList() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT patient_id, name, gender, DATE(created_at) as created_at
                FROM patient
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPatientsList error: " . $e->getMessage());
            return [];
        }
    }

    public function getAppointmentsList($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    a.appointment_id, a.appointment_date,
                    p.name as patient_name,
                    u.name as doctor_name,
                    a.appointment_status, a.consultation_fee
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN users u ON a.user_id = u.user_id AND u.role = 'doctor'
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

    // ✅ Updated getAdmissionsList without date filtering
    public function getAdmissionsList() {
        try {
            $sql = "
                SELECT 
                    a.admission_id,
                    a.admission_date,
                    a.discharge_date,
                    p.name AS patient_name,
                    r.room_type,
                    u.name AS doctor_name
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                LEFT JOIN users u ON a.doctor_id = u.user_id
                ORDER BY a.admission_date DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(); // no parameters
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAdmissionsList error: " . $e->getMessage());
            return [];
        }
    }

    public function getDoctorPerformance($startDate, $endDate) {
        try {
            $sql = "
                SELECT 
                    u.user_id,
                    u.name as doctor_name,
                    u.specialization,
                    COUNT(a.appointment_id) as total_appointments,
                    COALESCE(SUM(a.consultation_fee), 0) as appointment_revenue,
                    COUNT(t.treatment_id) as total_treatments,
                    COALESCE(SUM(t.treatment_fee), 0) as treatment_revenue,
                    (COUNT(DISTINCT a.patient_id) + COUNT(DISTINCT t.patient_id)) as unique_patients
                FROM users u
                LEFT JOIN appointment a ON u.user_id = a.user_id AND DATE(a.appointment_date) BETWEEN ? AND ?
                LEFT JOIN treatment t ON u.user_id = t.user_id AND DATE(t.treatment_date) BETWEEN ? AND ?
                WHERE u.role = 'doctor'
                GROUP BY u.user_id, u.name, u.specialization
                ORDER BY total_appointments DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getDoctorPerformance error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentsList() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.name as department_name,
                    d.location,
                    SUM(CASE WHEN u.role = 'doctor' THEN 1 ELSE 0 END) as total_doctors,
                    SUM(CASE WHEN u.role != 'doctor' THEN 1 ELSE 0 END) as total_staff,
                    COUNT(a.appointment_id) as total_appointments
                FROM department d
                LEFT JOIN users u ON d.department_id = u.department_id
                LEFT JOIN appointment a ON u.user_id = a.user_id AND u.role = 'doctor'
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

    public function getRoomUtilization($startDate = null, $endDate = null) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.room_id,
                    r.room_type,
                    r.daily_cost,
                    r.bed_stock as total_beds,
                    COUNT(a.admission_id) as occupied_beds,
                    (r.bed_stock - COUNT(a.admission_id)) as available_beds,
                    ROUND((COUNT(a.admission_id) / r.bed_stock) * 100, 2) as utilization_percentage
                FROM room r
                LEFT JOIN admission a ON r.room_id = a.room_id AND a.discharge_date IS NULL 
                GROUP BY r.room_id, r.room_type, r.daily_cost, r.bed_stock
                ORDER BY utilization_percentage DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getRoomUtilization error: " . $e->getMessage());
            return [];
        }
    }

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
                        WHEN stock_quantity <= 5 THEN 'Low Stock'
                        WHEN stock_quantity <= 20 THEN 'Moderate'
                        ELSE 'Sufficient'
                    END AS stock_status
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

    public function getPatientDemographics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    gender,
                    COUNT(*) AS count,
                    ROUND(COUNT(*) / (SELECT COUNT(*) FROM patient) * 100, 1) AS percentage
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
            $results[] = [ 'source' => 'Treatment Fee', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            // Admissions revenue
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM( (DATEDIFF(COALESCE(discharge_date, CURDATE()), admission_date) + 1) * r.daily_cost ), 0) as total_amount, COUNT(*) as cnt FROM admission a JOIN room r ON a.room_id = r.room_id WHERE DATE(a.admission_date) BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[] = [ 'source' => 'Admissions (Room)', 'total_amount' => (float)($row['total_amount'] ?? 0), 'count' => (int)($row['cnt'] ?? 0) ];

            return $results;
        } catch (PDOException $e) {
            error_log('Reports getRevenueBySource error: ' . $e->getMessage());
            return [];
        }
    }

    // ✅ New method for exporting payments (if needed)
    public function getPaymentsList($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.payment_id,
                    p.payment_date,
                    p.total_amount,
                    p.payment_status,
                    pt.name AS patient_name
                FROM payment p
                JOIN patient pt ON p.patient_id = pt.patient_id
                WHERE DATE(p.payment_date) BETWEEN ? AND ?
                ORDER BY p.payment_date DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getPaymentsList error: " . $e->getMessage());
            return [];
        }
    }
}
?>
