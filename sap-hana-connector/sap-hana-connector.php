<?php
/**
 * Plugin Name: SAP HANA Connector
 * Plugin URI: https://tu-sitio.com
 * Description: Conecta WordPress con SAP HANA para consumir datos.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tu-sitio.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Evitar acceso directo
defined('ABSPATH') || exit;

// Definir constantes del plugin
define('SAP_HANA_CONNECTOR_VERSION', '1.0.0');
define('SAP_HANA_CONNECTOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SAP_HANA_CONNECTOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar archivos necesarios
require_once SAP_HANA_CONNECTOR_PLUGIN_DIR . 'includes/class-database-connector.php';
require_once SAP_HANA_CONNECTOR_PLUGIN_DIR . 'includes/admin-interface.php';
require_once SAP_HANA_CONNECTOR_PLUGIN_DIR . 'includes/shortcodes.php';

// Inicializar el plugin
function sap_hana_connector_init() {
    // Verificar si SAP HANA está disponible en el servidor
    if (!class_exists('PDO') || !in_array('odbc', PDO::getAvailableDrivers())) {
        add_action('admin_notices', 'sap_hana_connector_missing_dependencies');
        return;
    }
    
    // Inicializar componentes
    SAP_HANA_Admin_Interface::init();
    SAP_HANA_Shortcodes::init();
}
add_action('plugins_loaded', 'sap_hana_connector_init');

// Mostrar aviso si faltan dependencias
function sap_hana_connector_missing_dependencies() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('SAP HANA Connector requiere la extensión PDO_ODBC de PHP. Por favor, instálala antes de usar este plugin.', 'sap-hana-connector'); ?></p>
    </div>
    <?php
}

// Activación del plugin
register_activation_hook(__FILE__, 'sap_hana_connector_activate');
function sap_hana_connector_activate() {
    // Crear tablas o configuraciones necesarias al activar
}

// Desactivación del plugin
register_deactivation_hook(__FILE__, 'sap_hana_connector_deactivate');
function sap_hana_connector_deactivate() {
    // Limpiar configuraciones al desactivar
}