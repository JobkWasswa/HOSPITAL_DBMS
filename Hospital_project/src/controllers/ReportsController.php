<?php
/**
 * Reports Controller
 * Hospital Management System
 */

require_once __DIR__ . '/../models/Reports.php';

class ReportsController {
    private $reportsModel;
    private $auth;
    
    public function __construct($pdo, $auth) {
        $this->reportsModel = new Reports($pdo);
        $this->auth = $auth;
    }
    
    /**
     * Handle reports dashboard and main report view logic
     */
    public function index() {
        // --- 1. Get and Sanitize Input ---
        $paramReport = $_GET['report'] ?? null;
        $paramType = $_GET['type'] ?? null;
        $reportType = $paramReport ?: ($paramType ?: 'overview');
        
        // Normalize 'payments' to 'financial'
        if ($reportType === 'payments') { $reportType = 'financial'; }
        
        // Use default date range (last 30 days)
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data = [];
        
        // --- 2. Route to the Correct Model Method ---
        switch ($reportType) {
            case 'patients':
                // List of patients for the table
                $data = $this->reportsModel->getPatientsList(); 
                break;
            case 'appointments':
                // List of appointments for the table
                $data = $this->reportsModel->getAppointmentsList($startDate, $endDate);
                break;
            case 'admissions':
                // List of admissions for the table
                $data = $this->reportsModel->getAdmissionsList($startDate, $endDate);
                break;
            case 'financial':
                // Accountant chart expects aggregated revenue by source
                $data = $this->reportsModel->getRevenueBySource($startDate, $endDate);
                break;
            case 'doctors':
                // Doctor performance/list
                $data = $this->reportsModel->getDoctorPerformance($startDate, $endDate);
                break;
            case 'departments':
                // Department list/stats
                $data = $this->reportsModel->getDepartmentsList(); 
                break;
            case 'rooms':
                // Room utilization list
                $data = $this->reportsModel->getRoomUtilization($startDate, $endDate);
                break;
            case 'inventory':
                // Inventory list
                $data = $this->reportsModel->getInventoryReport();
                break;
            case 'demographics':
                // Demographics list
                $data = $this->reportsModel->getPatientDemographics(); 
                break;

            case 'charts_doctor':
                // Compose datasets needed for doctor charts
                $data = [
                    'appointments_per_day' => $this->reportsModel->getAppointmentsPerDay($startDate, $endDate),
                    'appointment_status_share' => $this->reportsModel->getAppointmentStatusShare($startDate, $endDate),
                    'top_medicines' => $this->reportsModel->getTopMedicinesByQuantity(10, $startDate, $endDate)
                ];
                break;

            case 'overview':
            default:
                // Default is the overview, which pulls summary stats
                $data = [
                    'patients' => $this->reportsModel->getPatientStatistics(),
                    'appointments' => $this->reportsModel->getAppointmentStatistics($startDate, $endDate),
                    'admissions' => $this->reportsModel->getAdmissionStatistics($startDate, $endDate),
                    'financial' => [
                        'payment_stats' => $this->reportsModel->getPaymentStatusBreakdown(),
                        'total_revenue' => $this->reportsModel->getTodayRevenue()
                    ],
                ];
                $reportType = 'overview';
                break;
        }
        
        // --- 3. Return Final Result to the View ---
        return [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'data' => $data
        ];
    }
    public function getAdmissionsList($startDate, $endDate) {
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
                WHERE DATE(a.admission_date) BETWEEN ? AND ?
                ORDER BY a.admission_date DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports getAdmissionsList error: " . $e->getMessage());
            return [];
        }
    }

    
    /**
     * Export report to CSV
     */
    public function export() {
        $reportType = $_GET['report'] ?? ($_GET['type'] ?? 'overview');
        if ($reportType === 'payments') { $reportType = 'financial'; }
        
        // Export should always use the dates from the query, not default to 30 days
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $data = [];
        
        // Export always requires the detailed list/table data.
        switch ($reportType) {
            case 'patients':
                $data = $this->reportsModel->getPatientsList(); 
                break;
            case 'appointments':
                $data = $this->reportsModel->getAppointmentsList($startDate, $endDate);
                break;
            case 'admissions':
                $data = $this->reportsModel->getAdmissionsList($startDate, $endDate);
                break;
            case 'financial':
                $data = $this->reportsModel->getPaymentsList($startDate, $endDate); 
                break;
            case 'doctors':
                $data = $this->reportsModel->getDoctorPerformance($startDate, $endDate);
                break;
            case 'departments':
                $data = $this->reportsModel->getDepartmentsList(); 
                break;
            case 'rooms':
                $data = $this->reportsModel->getRoomUtilization($startDate, $endDate);
                break;
            case 'inventory':
                $data = $this->reportsModel->getInventoryReport();
                break;
            case 'demographics':
                $data = $this->reportsModel->getPatientDemographics(); 
                break;
            default:
                $data = $this->reportsModel->getAppointmentsList($startDate, $endDate); 
                break;
        }
        
        if (($_GET['format'] ?? 'csv') === 'csv') {
            $this->exportToCSV($reportType, $data);
        } else {
            // Placeholder for PDF
            header('Location: reports.php?type=' . $reportType . '&error=pdf_not_implemented');
            exit();
        }
    }
    
    // ... (exportToCSV and exportToPDF helper methods) ...
    private function exportToCSV($reportType, $data) {
        $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (is_array($data) && !empty($data)) {
            // Check if it's a list of arrays (most reports)
            if (isset($data[0]) && is_array($data[0])) {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) { fputcsv($output, $row); }
            } else {
                // Assume it's an associative array of statistics
                fputcsv($output, array_keys($data));
                fputcsv($output, array_values($data));
            }
        }
        
        fclose($output);
        exit();
    }
    
    private function exportToPDF($reportType, $data) {
        // Placeholder
        header('Location: reports.php?type=' . $reportType . '&error=pdf_not_implemented');
        exit();
    }
}
?>