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
    
    /**
     * Get patient statistics report
     */
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
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Reports getPatientStatistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List: patients
     */
    public function getPatientsList() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT patient_id, name, gender, DOB, phone, created_at
                FROM patient
                ORDER BY created_at DESC
                LIMIT 200
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getPatientsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get appointment statistics report
     */
    public function getAppointmentStatistics($startDate = null, $endDate = null) {
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
                WHERE 1=1
            ";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(appointment_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(appointment_date) <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetch();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Reports getAppointmentStatistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recent appointments (for dashboard)
     */
    public function getRecentAppointments($limit = 5) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.appointment_id, a.appointment_date, a.appointment_status, 
                       p.name as patient_name, d.name as doctor_name
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN doctor d ON a.doctor_id = d.doctor_id
                ORDER BY a.appointment_date DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getRecentAppointments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List: appointments
     */
    public function getAppointmentsList($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT a.appointment_id, a.appointment_date, a.appointment_status, a.consultation_fee,
                       p.name as patient_name, d.name as doctor_name
                FROM appointment a
                JOIN patient p ON a.patient_id = p.patient_id
                JOIN doctor d ON a.doctor_id = d.doctor_id
                WHERE 1=1
            ";
            $params = [];
            if ($startDate) { $sql .= " AND DATE(a.appointment_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(a.appointment_date) <= ?"; $params[] = $endDate; }
            $sql .= " ORDER BY a.appointment_date DESC LIMIT 500";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getAppointmentsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get admission statistics report
     */
    public function getAdmissionStatistics($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_admissions,
                    SUM(CASE WHEN discharge_date IS NULL THEN 1 ELSE 0 END) as current_admissions,
                    SUM(CASE WHEN discharge_date IS NOT NULL THEN 1 ELSE 0 END) as discharged,
                    AVG(DATEDIFF(COALESCE(discharge_date, NOW()), admission_date)) as avg_length_of_stay
                FROM admission
                WHERE 1=1
            ";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(admission_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(admission_date) <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Reports getAdmissionStatistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List: admissions
     */
    public function getAdmissionsList($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT a.admission_id, a.admission_date, a.discharge_date,
                       p.name as patient_name, r.room_type
                FROM admission a
                JOIN patient p ON a.patient_id = p.patient_id
                LEFT JOIN room r ON a.room_id = r.room_id
                LEFT JOIN bed b ON a.bed_id = b.bed_id
                WHERE 1=1
            ";
            $params = [];
            if ($startDate) { $sql .= " AND DATE(a.admission_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(a.admission_date) <= ?"; $params[] = $endDate; }
            $sql .= " ORDER BY a.admission_date DESC LIMIT 500";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getAdmissionsList error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get financial report
     */
    public function getFinancialReport($startDate = null, $endDate = null) {
        try {
            $params = [];
            
            $sql = "
                SELECT 
                    'Appointments' as source,
                    COUNT(*) as count,
                    COALESCE(SUM(consultation_fee), 0) as total_amount
                FROM appointment
                WHERE 1=1
                  AND UPPER(appointment_status) NOT IN ('CANCELLED','NO SHOW')
            ";
            if ($startDate) { $sql .= " AND DATE(appointment_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(appointment_date) <= ?"; $params[] = $endDate; }

            $sql .= "
                UNION ALL
                SELECT 
                    'Lab Tests' as source,
                    COUNT(*) as count,
                    COALESCE(SUM(test_cost), 0) as total_amount
                FROM lab_test
                WHERE 1=1
            ";
            if ($startDate) { $sql .= " AND DATE(test_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(test_date) <= ?"; $params[] = $endDate; }

            $sql .= "
                UNION ALL
                SELECT 
                    'Prescriptions' as source,
                    COUNT(*) as count,
                    COALESCE(SUM(p.quantity * m.medicine_price), 0) as total_amount
                FROM prescription p
                JOIN medicine m ON p.medicine_id = m.medicine_id
                JOIN treatment t ON p.treatment_id = t.treatment_id
                WHERE 1=1
            ";
            if ($startDate) { $sql .= " AND DATE(t.treatment_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(t.treatment_date) <= ?"; $params[] = $endDate; }

            // Include Treatments revenue
            $sql .= "
                UNION ALL
                SELECT 
                    'Treatments' as source,
                    COUNT(*) as count,
                    COALESCE(SUM(treatment_fee), 0) as total_amount
                FROM treatment
                WHERE 1=1
            ";
            if ($startDate) { $sql .= " AND DATE(treatment_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(treatment_date) <= ?"; $params[] = $endDate; }

            // Include Admissions revenue (simplified - no room cost calculation)
            $sql .= "
                UNION ALL
                SELECT 
                    'Admissions' as source,
                    COUNT(*) as count,
                    0 as total_amount
                FROM admission
                WHERE 1=1
            ";
            if ($startDate) { $sql .= " AND DATE(admission_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(admission_date) <= ?"; $params[] = $endDate; }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getFinancialReport error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Payment status breakdown and today's revenue
     */
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
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Reports getPaymentStatusBreakdown error: " . $e->getMessage());
            return ['paid' => 0, 'pending' => 0, 'declined' => 0];
        }
    }

    public function getTodayRevenue() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as today_revenue
                FROM payment
                WHERE payment_status = 'Paid'
                  AND DATE(payment_date) = CURDATE()
            ");
            $stmt->execute();
            $row = $stmt->fetch();
            return $row['today_revenue'] ?? 0;
        } catch (PDOException $e) {
            error_log("Reports getTodayRevenue error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Department stats (existing)
     */
    public function getDoctorPerformance($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT 
                    d.doctor_id,
                    d.name as doctor_name,
                    d.specialization,
                    COUNT(DISTINCT a.appointment_id) as total_appointments,
                    COUNT(DISTINCT t.treatment_id) as total_treatments,
                    COUNT(DISTINCT p.patient_id) as unique_patients,
                    SUM(a.consultation_fee) as appointment_revenue,
                    SUM(t.treatment_fee) as treatment_revenue
                FROM doctor d
                LEFT JOIN appointment a ON d.doctor_id = a.doctor_id
                LEFT JOIN treatment t ON d.doctor_id = t.doctor_id
                LEFT JOIN patient p ON (a.patient_id = p.patient_id OR t.patient_id = p.patient_id)
                WHERE 1=1
            ";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND (DATE(a.appointment_date) >= ? OR DATE(t.treatment_date) >= ?)";
                $params[] = $startDate;
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND (DATE(a.appointment_date) <= ? OR DATE(t.treatment_date) <= ?)";
                $params[] = $endDate;
                $params[] = $endDate;
            }
            
            $sql .= " GROUP BY d.doctor_id, d.name, d.specialization ORDER BY total_appointments DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getDoctorPerformance error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get department statistics report
     */
    public function getDepartmentStatistics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.department_id,
                    d.name as department_name,
                    d.location,
                    COUNT(DISTINCT doc.doctor_id) as total_doctors,
                    COUNT(DISTINCT s.staff_id) as total_staff,
                    COUNT(DISTINCT a.appointment_id) as total_appointments
                FROM department d
                LEFT JOIN doctor doc ON d.department_id = doc.department_id
                LEFT JOIN staff s ON d.department_id = s.department_id
                LEFT JOIN appointment a ON doc.doctor_id = a.doctor_id
                GROUP BY d.department_id, d.name, d.location
                ORDER BY total_appointments DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getDepartmentStatistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get room utilization report
     */
    public function getRoomUtilization() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.room_id,
                    r.room_type,
                    r.daily_cost,
                    COUNT(b.bed_id) as total_beds,
                    SUM(CASE WHEN b.bed_status = 'Occupied' THEN 1 ELSE 0 END) as occupied_beds,
                    SUM(CASE WHEN b.bed_status = 'Available' THEN 1 ELSE 0 END) as available_beds,
                    ROUND((SUM(CASE WHEN b.bed_status = 'Occupied' THEN 1 ELSE 0 END) / COUNT(b.bed_id)) * 100, 2) as utilization_percentage
                FROM room r
                LEFT JOIN bed b ON r.room_id = b.room_id
                GROUP BY r.room_id, r.room_type, r.daily_cost
                ORDER BY utilization_percentage DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getRoomUtilization error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get medicine inventory report
     */
    public function getMedicineInventory() {
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
                        WHEN stock_quantity <= 10 THEN 'Low'
                        ELSE 'Adequate'
                    END as stock_status
                FROM medicine
                ORDER BY stock_quantity ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getMedicineInventory error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get patient demographics report
     */
    public function getPatientDemographics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    gender,
                    COUNT(*) as count,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM patient)), 2) as percentage
                FROM patient
                GROUP BY gender
                ORDER BY count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getPatientDemographics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get monthly trends report
     */
    public function getMonthlyTrends($year = null) {
        try {
            $year = $year ?: date('Y');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    MONTH(appointment_date) as month,
                    MONTHNAME(appointment_date) as month_name,
                    COUNT(*) as appointments,
                    SUM(consultation_fee) as revenue
                FROM appointment
                WHERE YEAR(appointment_date) = ?
                GROUP BY MONTH(appointment_date), MONTHNAME(appointment_date)
                ORDER BY month
            ");
            $stmt->execute([$year]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getMonthlyTrends error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List: payments
     */
    public function getPaymentsList($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT p.bill_id, p.total_amount, p.payment_method, p.payment_status, p.payment_date,
                       pt.name as patient_name
                FROM payment p
                JOIN patient pt ON p.patient_id = pt.patient_id
                WHERE 1=1
            ";
            $params = [];
            if ($startDate) { $sql .= " AND DATE(p.payment_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(p.payment_date) <= ?"; $params[] = $endDate; }
            $sql .= " ORDER BY p.payment_date DESC LIMIT 500";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getPaymentsList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List: lab tests
     */
    public function getLabTestsList($startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT lt.test_id, lt.test_type, lt.results, lt.test_date, lt.test_cost,
                       p.name as patient_name
                FROM lab_test lt
                JOIN patient p ON lt.patient_id = p.patient_id
                WHERE 1=1
            ";
            $params = [];
            if ($startDate) { $sql .= " AND DATE(lt.test_date) >= ?"; $params[] = $startDate; }
            if ($endDate)   { $sql .= " AND DATE(lt.test_date) <= ?"; $params[] = $endDate; }
            $sql .= " ORDER BY lt.test_date DESC LIMIT 500";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Reports getLabTestsList error: " . $e->getMessage());
            return [];
        }
    }
}
?>
