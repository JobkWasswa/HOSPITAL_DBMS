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
     * Handle reports dashboard
     */
    public function index() {
        // Accept both 'report' and 'type' parameters
        $paramReport = $_GET['report'] ?? null;
        $paramType = $_GET['type'] ?? null;
        $reportType = $paramReport ?: ($paramType ?: 'overview');
        if ($reportType === 'payments') { $reportType = 'financial'; }
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $data = [];
        
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
                $data = $this->reportsModel->getFinancialReport($startDate, $endDate);
                break;
            default:
                $data = [
                    'patients' => $this->reportsModel->getPatientStatistics(),
                    'appointments' => $this->reportsModel->getAppointmentStatistics(),
                    'admissions' => $this->reportsModel->getAdmissionStatistics(),
                    'financial' => $this->reportsModel->getFinancialReport($startDate, $endDate)
                ];
                break;
        }
        
        return [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'data' => $data
        ];
    }
    
    /**
     * Export report to CSV
     */
    public function export() {
        $reportType = $_GET['report'] ?? ($_GET['type'] ?? 'overview');
        if ($reportType === 'payments') { $reportType = 'financial'; }
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $format = $_GET['format'] ?? 'csv';
        
        // Force correct data based on normalized $reportType
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
                $data = $this->reportsModel->getFinancialReport($startDate, $endDate);
                break;
            default:
                $data = $this->reportsModel->getFinancialReport($startDate, $endDate);
                break;
        }
        
        if (($_GET['format'] ?? 'csv') === 'csv') {
            $this->exportToCSV($reportType, $data);
        } else {
            $this->exportToPDF($reportType, $data);
        }
    }
    
    /**
     * Export data to CSV
     */
    private function exportToCSV($reportType, $data) {
        $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (is_array($data) && !empty($data)) {
            if (isset($data[0]) && is_array($data[0])) {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) { fputcsv($output, $row); }
            } else {
                fputcsv($output, array_keys($data));
                fputcsv($output, array_values($data));
            }
        }
        
        fclose($output);
        exit();
    }
    
    private function exportToPDF($reportType, $data) {
        header('Location: reports.php?type=' . ($reportType === 'financial' ? 'payments' : $reportType));
        exit();
    }
}
?>
