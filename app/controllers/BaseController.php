<?php
require_once __DIR__ . '/../../config/database.php';
class BaseController {
    protected $db;
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
}
?>