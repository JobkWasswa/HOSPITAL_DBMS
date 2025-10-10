<?php
/**
 * Medicine Model
 * Hospital Management System
 */

class Medicine {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all medicines with search and pagination
     */
    public function getAll($search = '', $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT * FROM medicine";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE name LIKE ?";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY name LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Medicine getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of medicines
     */
    public function getTotalCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as count FROM medicine";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " WHERE name LIKE ?";
                $params[] = "%$search%";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Medicine getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get medicine by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM medicine WHERE medicine_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Medicine getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new medicine
     */
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO medicine (name, dosage, stock_quantity, medicine_price) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'],
                $data['dosage'],
                $data['stock_quantity'],
                $data['medicine_price']
            ]);
        } catch (PDOException $e) {
            error_log("Medicine create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update medicine
     */
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET name = ?, dosage = ?, stock_quantity = ?, medicine_price = ?
                WHERE medicine_id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $data['dosage'],
                $data['stock_quantity'],
                $data['medicine_price'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Medicine update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete medicine
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM medicine WHERE medicine_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Medicine delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($id, $quantity) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE medicine 
                SET stock_quantity = stock_quantity + ?
                WHERE medicine_id = ?
            ");
            return $stmt->execute([$quantity, $id]);
        } catch (PDOException $e) {
            error_log("Medicine updateStock error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get low stock medicines
     */
    public function getLowStock($threshold = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM medicine 
                WHERE stock_quantity <= ?
                ORDER BY stock_quantity ASC
            ");
            $stmt->execute([$threshold]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Medicine getLowStock error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search medicines by name
     */
    public function search($query) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM medicine 
                WHERE name LIKE ? AND stock_quantity > 0
                ORDER BY name
                LIMIT 10
            ");
            $stmt->execute(["%$query%"]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Medicine search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get medicine statistics
     */
    public function getStatistics() {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(stock_quantity) as total_stock,
                    SUM(medicine_price * stock_quantity) as total_value,
                    SUM(CASE WHEN stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock
                FROM medicine
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Medicine getStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'total_stock' => 0,
                'total_value' => 0,
                'low_stock' => 0
            ];
        }
    }
    
    /**
     * Validate medicine data
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Medicine name is required';
        }
        
        if (empty($data['dosage'])) {
            $errors[] = 'Dosage is required';
        }
        
        if (empty($data['stock_quantity'])) {
            $errors[] = 'Stock quantity is required';
        } elseif (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            $errors[] = 'Invalid stock quantity';
        }
        
        if (empty($data['medicine_price'])) {
            $errors[] = 'Medicine price is required';
        } elseif (!is_numeric($data['medicine_price']) || $data['medicine_price'] < 0) {
            $errors[] = 'Invalid medicine price';
        }
        
        return $errors;
    }
}
?>
