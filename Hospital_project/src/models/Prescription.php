<?php
/**
 * Medicine Dispense Logic Model
 * Hospital Management System
 * * NOTE: This class holds the core logic for inventory management and validation 
 * previously found in the Prescription model, as the 'prescription' table 
 * has been eliminated. The methods here must be called from the Controller 
 * or the new Treatment model when a medicine record is created or deleted.
 */

class Prescription {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // ------------------------------------------------------------------
    // CORE LOGIC FUNCTIONS (Moved from old Prescription Model)
    // ------------------------------------------------------------------

    /**
     * Handles the stock deduction and checks for sufficient stock.
     * This method must be called when creating a new medicine record (e.g., in Treatment model).
     * * @param int $medicineId ID of the medicine
     * @param int $quantity Quantity to deduct
     * @return bool True on success, false otherwise
     */
    public function deductStock($medicineId, $quantity) {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Check if medicine has enough stock
            $stmt = $this->pdo->prepare("SELECT stock_quantity FROM medicine WHERE medicine_id = ?");
            $stmt->execute([$medicineId]);
            $medicine = $stmt->fetch();
            
            if (!$medicine || $medicine['stock_quantity'] < $quantity) {
                $this->pdo->rollBack();
                throw new Exception("Insufficient stock for this medicine.");
            }
            
            // 2. Update medicine stock
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET stock_quantity = stock_quantity - ?
                WHERE medicine_id = ?
            ");
            $stmt->execute([$quantity, $medicineId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("MedicineDispenseLogic deductStock error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handles the stock restoration.
     * This method must be called before deleting or updating a medicine record.
     * * @param int $medicineId ID of the medicine
     * @param int $quantity Quantity to restore
     * @return bool True on success, false otherwise
     */
    public function restoreStock($medicineId, $quantity) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET stock_quantity = stock_quantity + ?
                WHERE medicine_id = ?
            ");
            return $stmt->execute([$quantity, $medicineId]);
        } catch (PDOException $e) {
            error_log("MedicineDispenseLogic restoreStock error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate medicine dispense data (quantity, instructions, IDs).
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['quantity'])) {
            $errors[] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            $errors[] = 'Invalid quantity';
        }
        
        // Assuming dosage instructions is now associated with the medicine/treatment record
        if (empty($data['dosage_instructions'])) {
            $errors[] = 'Dosage instructions are required';
        }
        
        if (empty($data['treatment_id'])) {
            $errors[] = 'Treatment is required';
        }
        
        if (empty($data['medicine_id'])) {
            $errors[] = 'Medicine is required';
        }
        
        return $errors;
    }

    // ------------------------------------------------------------------
    // NOTE: All previous methods (getAll, getById, create, update, delete, 
    // getByTreatment, getByPatient, calculateCost) have been REMOVED 
    // as they relied on the deleted 'prescription' table.
    // ------------------------------------------------------------------
}
?>