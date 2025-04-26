<?php
if (!defined('ABSPATH')) exit;

class SAP_HANA_DB_Connector {
    private static $instance = null;
    private $connection = null;
    private $last_error = '';
    
    private function __construct() {
        // Obtener configuraciones de la base de datos
        $this->settings = get_option('sap_hana_connector_settings', array());
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function connect() {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        try {
            $dsn = "odbc:Driver={HDBODBC};ServerNode={$this->settings['server']};Database={$this->settings['database']}";
            
            $this->connection = new PDO($dsn, $this->settings['username'], $this->settings['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $this->connection;
        } catch (PDOException $e) {
            $this->last_error = $e->getMessage();
            error_log("SAP HANA Connection Error: " . $this->last_error);
            return false;
        }
    }
    
    public function disconnect() {
        if ($this->connection !== null) {
            $this->connection = null;
        }
    }
    
    public function query($sql, $params = array()) {
        try {
            $conn = $this->connect();
            if (!$conn) return false;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->last_error = $e->getMessage();
            error_log("SAP HANA Query Error: " . $this->last_error);
            return false;
        }
    }
    
    public function get_last_error() {
        return $this->last_error;
    }
    
    public function test_connection() {
        try {
            $conn = $this->connect();
            if (!$conn) return false;
            
            // Consulta simple para probar la conexión
            $result = $conn->query("SELECT CURRENT_USER FROM DUMMY");
            return ($result !== false);
        } catch (PDOException $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
    }
}