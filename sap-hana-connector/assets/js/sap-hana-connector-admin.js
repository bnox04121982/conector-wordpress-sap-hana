jQuery(document).ready(function($) {
    // Manejar el test de conexión con AJAX
    $('form[name="sap_hana_test_connection"]').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('#test-connection-btn');
        var originalText = $button.text();
        var $spinner = $form.find('.spinner');
        
        // Mostrar spinner
        $button.text('Probando...').prop('disabled', true);
        $spinner.addClass('is-active');
        
        // Obtener valores del formulario principal
        var data = {
            action: 'sap_hana_test_connection',
            server: $('input[name="sap_hana_connector_settings[server]"]').val(),
            database: $('input[name="sap_hana_connector_settings[database]"]').val(),
            username: $('input[name="sap_hana_connector_settings[username]"]').val(),
            password: $('input[name="sap_hana_connector_settings[password]"]').val(),
            _wpnonce: sap_hana_connector_admin.nonce
        };
        
        // Enviar datos via AJAX
        $.post(sap_hana_connector_admin.ajax_url, data, function(response) {
            // Ocultar spinner
            $button.text(originalText).prop('disabled', false);
            $spinner.removeClass('is-active');
            
            // Mostrar mensaje
            var noticeClass = response.success ? 'notice-success' : 'notice-error';
            var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + response.data.message + '</p></div>');
            
            $('.wrap').prepend(notice);
            
            // Descartar mensajes después de 5 segundos
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
        }).fail(function(xhr) {
            $button.text(originalText).prop('disabled', false);
            $spinner.removeClass('is-active');
            
            var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? 
                xhr.responseJSON.data.message : 'Error desconocido al probar la conexión';
            
            $('.wrap').prepend(
                '<div class="notice notice-error is-dismissible"><p>' + errorMessage + '</p></div>'
            );
        });
    });
    
    // Mostrar/ocultar contraseña
    $('.toggle-password').on('click', function() {
        var $target = $($(this).data('target'));
        if ($target.attr('type') === 'password') {
            $target.attr('type', 'text');
            $(this).text('Ocultar');
        } else {
            $target.attr('type', 'password');
            $(this).text('Mostrar');
        }
    });
    
    // Ejecutar consulta SQL (opcional)
    $('#execute-query-btn').on('click', function() {
        var query = $('#sap_hana_query_editor').val();
        if (!query) return;
        
        var $button = $(this);
        var originalText = $button.text();
        var $results = $('#query-results');
        
        $button.text('Ejecutando...').prop('disabled', true);
        $results.html('<div class="spinner is-active"></div>');
        
        $.post(sap_hana_connector_admin.ajax_url, {
            action: 'sap_hana_execute_query',
            query: query,
            _wpnonce: sap_hana_connector_admin.nonce
        }, function(response) {
            $button.text(originalText).prop('disabled', false);
            
            if (response.success) {
                if (response.data.results && response.data.results.length > 0) {
                    // Crear tabla con resultados
                    var table = '<table class="sap-hana-table"><thead><tr>';
                    
                    // Encabezados
                    Object.keys(response.data.results[0]).forEach(function(key) {
                        table += '<th>' + key + '</th>';
                    });
                    
                    table += '</tr></thead><tbody>';
                    
                    // Filas
                    response.data.results.forEach(function(row) {
                        table += '<tr>';
                        Object.values(row).forEach(function(value) {
                            table += '<td>' + (value !== null ? value : 'NULL') + '</td>';
                        });
                        table += '</tr>';
                    });
                    
                    table += '</tbody></table>';
                    $results.html(table);
                } else {
                    $results.html('<p>La consulta se ejecutó correctamente pero no devolvió resultados.</p>');
                }
            } else {
                $results.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
            }
        }).fail(function(xhr) {
            $button.text(originalText).prop('disabled', false);
            $results.html('<div class="notice notice-error"><p>Error al ejecutar la consulta.</p></div>');
        });
    });
});