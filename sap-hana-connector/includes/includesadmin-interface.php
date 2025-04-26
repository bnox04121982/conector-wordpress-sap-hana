<?php
if (!defined('ABSPATH')) exit;

class SAP_HANA_Admin_Interface {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }
    
    public static function add_admin_menu() {
        add_menu_page(
            'SAP HANA Connector',
            'SAP HANA',
            'manage_options',
            'sap-hana-connector',
            array(__CLASS__, 'render_admin_page'),
            'dashicons-database'
        );
    }
    
    public static function register_settings() {
        register_setting('sap_hana_connector_settings_group', 'sap_hana_connector_settings');
        
        add_settings_section(
            'sap_hana_connector_settings_section',
            'Configuración de Conexión',
            array(__CLASS__, 'render_settings_section'),
            'sap-hana-connector'
        );
        
        add_settings_field(
            'server',
            'Servidor',
            array(__CLASS__, 'render_server_field'),
            'sap-hana-connector',
            'sap_hana_connector_settings_section'
        );
        
        add_settings_field(
            'database',
            'Base de Datos',
            array(__CLASS__, 'render_database_field'),
            'sap-hana-connector',
            'sap_hana_connector_settings_section'
        );
        
        add_settings_field(
            'username',
            'Usuario',
            array(__CLASS__, 'render_username_field'),
            'sap-hana-connector',
            'sap_hana_connector_settings_section'
        );
        
        add_settings_field(
            'password',
            'Contraseña',
            array(__CLASS__, 'render_password_field'),
            'sap-hana-connector',
            'sap_hana_connector_settings_section'
        );
    }
    
    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes para acceder a esta página.'));
        }
        
        // Probar conexión si se ha enviado el formulario
        if (isset($_POST['test_connection'])) {
            $db = SAP_HANA_DB_Connector::get_instance();
            $success = $db->test_connection();
            
            if ($success) {
                add_settings_error(
                    'sap_hana_connector_messages',
                    'sap_hana_connector_message',
                    __('Conexión exitosa a SAP HANA', 'sap-hana-connector'),
                    'success'
                );
            } else {
                add_settings_error(
                    'sap_hana_connector_messages',
                    'sap_hana_connector_message',
                    __('Error de conexión: ', 'sap-hana-connector') . $db->get_last_error(),
                    'error'
                );
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('sap_hana_connector_messages'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('sap_hana_connector_settings_group');
                do_settings_sections('sap-hana-connector');
                submit_button('Guardar Configuración');
                ?>
            </form>
            
            <form method="post">
                <input type="submit" name="test_connection" class="button button-secondary" value="Probar Conexión">
            </form>
        </div>
        <?php
    }
    
    public static function render_settings_section() {
        echo '<p>Introduce los detalles de conexión a tu instancia de SAP HANA.</p>';
    }
    
    public static function render_server_field() {
        $settings = get_option('sap_hana_connector_settings');
        ?>
        <input type="text" name="sap_hana_connector_settings[server]" value="<?php echo esc_attr($settings['server'] ?? ''); ?>" class="regular-text">
        <p class="description">Ejemplo: myhanaserver:30015</p>
        <?php
    }
    
    public static function render_database_field() {
        $settings = get_option('sap_hana_connector_settings');
        ?>
        <input type="text" name="sap_hana_connector_settings[database]" value="<?php echo esc_attr($settings['database'] ?? ''); ?>" class="regular-text">
        <?php
    }
    
    public static function render_username_field() {
        $settings = get_option('sap_hana_connector_settings');
        ?>
        <input type="text" name="sap_hana_connector_settings[username]" value="<?php echo esc_attr($settings['username'] ?? ''); ?>" class="regular-text">
        <?php
    }
    
    public static function render_password_field() {
        $settings = get_option('sap_hana_connector_settings');
        ?>
        <input type="password" name="sap_hana_connector_settings[password]" value="<?php echo esc_attr($settings['password'] ?? ''); ?>" class="regular-text">
        <?php
    }
}