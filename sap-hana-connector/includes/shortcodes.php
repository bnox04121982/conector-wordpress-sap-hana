<?php
if (!defined('ABSPATH')) exit;

class SAP_HANA_Shortcodes {
    public static function init() {
        add_shortcode('sap_hana_query', array(__CLASS__, 'render_query_shortcode'));
    }
    
    public static function render_query_shortcode($atts) {
        $atts = shortcode_atts(array(
            'query' => '',
            'format' => 'table', // table, list, json
            'cache' => 3600 // tiempo de caché en segundos
        ), $atts, 'sap_hana_query');
        
        if (empty($atts['query'])) {
            return '<p class="sap-hana-error">Error: No se especificó una consulta.</p>';
        }
        
        // Verificar caché
        $cache_key = 'sap_hana_query_' . md5($atts['query']);
        $cached_result = get_transient($cache_key);
        
        if ($cached_result !== false) {
            $results = $cached_result;
        } else {
            $db = SAP_HANA_DB_Connector::get_instance();
            $results = $db->query($atts['query']);
            
            if ($results === false) {
                return '<p class="sap-hana-error">Error en la consulta: ' . esc_html($db->get_last_error()) . '</p>';
            }
            
            // Almacenar en caché
            set_transient($cache_key, $results, $atts['cache']);
        }
        
        // Formatear resultados
        switch ($atts['format']) {
            case 'list':
                return self::format_as_list($results);
            case 'json':
                return '<pre>' . json_encode($results, JSON_PRETTY_PRINT) . '</pre>';
            case 'table':
            default:
                return self::format_as_table($results);
        }
    }
    
    private static function format_as_table($data) {
        if (empty($data)) return '<p>No se encontraron resultados.</p>';
        
        $output = '<table class="sap-hana-table"><thead><tr>';
        
        // Encabezados
        foreach (array_keys($data[0]) as $column) {
            $output .= '<th>' . esc_html($column) . '</th>';
        }
        
        $output .= '</tr></thead><tbody>';
        
        // Filas
        foreach ($data as $row) {
            $output .= '<tr>';
            foreach ($row as $cell) {
                $output .= '<td>' . esc_html($cell) . '</td>';
            }
            $output .= '</tr>';
        }
        
        $output .= '</tbody></table>';
        
        return $output;
    }
    
    private static function format_as_list($data) {
        if (empty($data)) return '<p>No se encontraron resultados.</p>';
        
        $output = '<ul class="sap-hana-list">';
        
        foreach ($data as $row) {
            $output .= '<li>';
            $first = true;
            foreach ($row as $key => $value) {
                if (!$first) $output .= ' | ';
                $output .= '<strong>' . esc_html($key) . ':</strong> ' . esc_html($value);
                $first = false;
            }
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        
        return $output;
    }
}