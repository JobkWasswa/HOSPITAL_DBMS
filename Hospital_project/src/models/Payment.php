<?php
/**
 * Payment Model
 * Hospital Management System
 */

class Payment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all payments with search and pagination
     */
    public function getAll($search = '', $status = '', $method = '', $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT p.*, pat.name as patient_name, pat.phone as patient_phone
                FROM payment p
                JOIN patient pat ON p.patient_id = pat.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND pat.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $status;
            }
            
            if (!empty($method)) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $method;
            }
            
            $sql .= " ORDER BY p.payment_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Payment getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of payments
     */
    public function getTotalCount($search = '', $status = '', $method = '') {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM payment p
                JOIN patient pat ON p.patient_id = pat.patient_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND pat.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $status;
            }
            
            if (!empty($method)) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $method;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Payment getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get payment by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, pat.name as patient_name, pat.phone as patient_phone, pat.address
                FROM payment p
                JOIN patient pat ON p.patient_id = pat.patient_id
                WHERE p.bill_id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Payment getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new payment
     */
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO payment (total_amount, payment_method, payment_status, payment_date, patient_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['total_amount'],
                $data['payment_method'],
                $data['payment_status'],
                $data['payment_date'],
                $data['patient_id']
            ]);
        } catch (PDOException $e) {
            error_log("Payment create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE payment 
                SET total_amount = ?, payment_method = ?, payment_status = ?, payment_date = ?, patient_id = ?
                WHERE bill_id = ?
            ");
            return $stmt->execute([
                $data['total_amount'],
                $data['payment_method'],
                $data['payment_status'],
                $data['payment_date'],
                $data['patient_id'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Payment update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment status
     */
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE payment 
                SET payment_status = ?
                WHERE bill_id = ?
            ");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Payment updateStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete payment
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM payment WHERE bill_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Payment delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payments for a patient
     */
    public function getByPatient($patientId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM payment 
                WHERE patient_id = ?
                ORDER BY payment_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Payment getByPatient error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending payments
     */
    public function getPendingPayments() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, pat.name as patient_name, pat.phone as patient_phone
                FROM payment p
                JOIN patient pat ON p.patient_id = pat.patient_id
                WHERE p.payment_status = 'Pending'
                ORDER BY p.payment_date ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Payment getPendingPayments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid,
                    SUM(CASE WHEN payment_status = 'Declined' THEN 1 ELSE 0 END) as declined,
                    SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN DATE(payment_date) = CURDATE() THEN total_amount ELSE 0 END) as today_revenue
                FROM payment
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Payment getStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'paid' => 0,
                'declined' => 0,
                'total_revenue' => 0,
                'today_revenue' => 0
            ];
        }
    }
    
    /**
     * Calculate patient total bill (mirrors provided SQL logic exactly)
     */
    public function calculatePatientBill($patientId) {
        try {
            // Treatments
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(treatment_fee), 0) AS total
                FROM treatment
                WHERE patient_id = ?
            ");
            $stmt->execute([$patientId]);
            $treatmentsTotal = (float)($stmt->fetch()['total'] ?? 0);
            
            // Lab tests
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(test_cost), 0) AS total
                FROM lab_test
                WHERE patient_id = ?
            ");
            $stmt->execute([$patientId]);
            $labTotal = (float)($stmt->fetch()['total'] ?? 0);
            
            // Prescriptions (via treatments)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(p.quantity * m.medicine_price), 0) AS total
                FROM prescription p
                JOIN treatment t ON p.treatment_id = t.treatment_id
                JOIN medicine m ON p.medicine_id = m.medicine_id
                WHERE t.patient_id = ?
            ");
            $stmt->execute([$patientId]);
            $prescriptionsTotal = (float)($stmt->fetch()['total'] ?? 0);
            
            // Admissions: prefer direct room_id, fallback via bed -> room
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(
                         (DATEDIFF(COALESCE(a.discharge_date, NOW()), a.admission_date) + 1)
                         * COALESCE(
                             CASE 
                               WHEN a.room_id IS NOT NULL THEN r1.daily_cost 
                               ELSE r2.daily_cost 
                             END, 0)
                       ), 0) AS total
                FROM admission a
                LEFT JOIN room r1 ON a.room_id = r1.room_id
                LEFT JOIN bed b ON a.bed_id = b.bed_id
                LEFT JOIN room r2 ON b.room_id = r2.room_id
                WHERE a.patient_id = ?
            ");
            $stmt->execute([$patientId]);
            $admissionsTotal = (float)($stmt->fetch()['total'] ?? 0);
            
            // Appointments (exclude Cancelled/No show; case-insensitive)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(consultation_fee), 0) AS total
                FROM appointment
                WHERE patient_id = ?
                  AND UPPER(appointment_status) NOT IN ('CANCELLED','NO SHOW')
            ");
            $stmt->execute([$patientId]);
            $appointmentsTotal = (float)($stmt->fetch()['total'] ?? 0);
            
            $total = $treatmentsTotal + $labTotal + $prescriptionsTotal + $admissionsTotal + $appointmentsTotal;
            return $total;
        } catch (PDOException $e) {
            error_log("Payment calculatePatientBill error: " . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Validate payment data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['total_amount'])) {
            $errors[] = 'Total amount is required';
        } elseif (!is_numeric($data['total_amount']) || $data['total_amount'] < 0) {
            $errors[] = 'Invalid total amount';
        }
        
        if (empty($data['payment_method'])) {
            $errors[] = 'Payment method is required';
        } elseif (!in_array($data['payment_method'], [PAYMENT_CASH, PAYMENT_CARD, PAYMENT_INSURANCE])) {
            $errors[] = 'Invalid payment method';
        }
        
        if (empty($data['payment_status'])) {
            $errors[] = 'Payment status is required';
        } elseif (!in_array($data['payment_status'], [PAYMENT_PENDING, PAYMENT_PAID, PAYMENT_DECLINED])) {
            $errors[] = 'Invalid payment status';
        }
        
        if (empty($data['patient_id'])) {
            $errors[] = 'Patient is required';
        }
        
        return $errors;
    }
}
?>
