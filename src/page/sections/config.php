<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;

$respond_config = [];

if (isset($_POST['save']) && $_POST['save'] === 'wpfm_config') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('wpfm_config_save', 'wpfm_config_nonce');

    $api_enabled = isset($_POST['wpfm_api_enabled']);
    $api_key = sanitize_text_field($_POST['wpfm_api_key'] ?? '');

    $CONFIG['api_enabled'] = $api_enabled;
    if (!empty($api_key)) {
        $CONFIG['api_key'] = $api_key;
    }

    $GPAI_USE_DATA_CONFIG->set($CONFIG);
    $CONFIG = $GPAI_USE_DATA_CONFIG->get();
    $respond_config = ['status' => 'ok', 'message' => 'Configuración guardada.'];
}

$api_enabled = !empty($CONFIG['api_enabled']);
$api_key = $CONFIG['api_key'] ?? '';
$rest_url = rest_url(WPFM_KEY) . '/';
?>

<form method="post">
    <?php FWURespond::render($respond_config) ?>
    <input type="hidden" name="save" value="wpfm_config">
    <?php wp_nonce_field('wpfm_config_save', 'wpfm_config_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Habilitada", "Activa o desactiva la API REST para gestión de archivos. Cuando está desactivada, las peticiones externas serán rechazadas.") ?>
            </th>
            <td>
                <input
                    type="checkbox"
                    id="wpfm_api_enabled"
                    name="wpfm_api_enabled"
                    <?= esc_attr($api_enabled ? 'checked' : '') ?>
                    class="regular-text" />
                <label for="wpfm_api_enabled">
                    Activar API REST
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("API Key", "Clave secreta que deben enviar los clientes en el header X-WPFM-Key.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="password"
                        id="wpfm_api_key"
                        name="wpfm_api_key"
                        value="<?= esc_attr($api_key) ?>"
                        class="regular-text"
                        placeholder="Genera una API Key..." />
                    <button type="button" class="button" id="wpfm-generate-key">Generar</button>
                    <button type="button" class="button" id="wpfm-copy-key">Copiar</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Endpoint URL", "URL base de la API REST. Los endpoints son: /list, /get, /delete, /upload.") ?>
            </th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="text"
                        id="wpfm_endpoint_url"
                        value="<?= esc_url($rest_url) ?>"
                        class="regular-text"
                        readonly />
                    <button type="button" class="button" id="wpfm-copy-url">Copiar URL</button>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php FWUTooltip::render("Endpoints disponibles", "Lista de endpoints de la API REST.") ?>
            </th>
            <td>
                <table class="wp-list-table widefat fixed" >
                    <thead>
                        <tr>
                            <th style="width:20%;padding:15px 10px;">Método</th>
                            <th style="width:30%;padding:15px 10px;">Ruta</th>
                            <th style="width:50%;padding:15px 10px;">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code style="white-space: nowrap;">GET</code></td>
                            <td><code style="white-space: nowrap;">/wp-json/WPFM/list</code></td>
                            <td>Lista todos los archivos</td>
                        </tr>
                        <tr>
                            <td><code style="white-space: nowrap;">GET</code></td>
                            <td><code style="white-space: nowrap;">/wp-json/WPFM/get?name=x</code></td>
                            <td>Info o descarga de archivo</td>
                        </tr>
                        <tr>
                            <td><code style="white-space: nowrap;">DELETE</code></td>
                            <td><code style="white-space: nowrap;">/wp-json/WPFM/delete?name=x</code></td>
                            <td>Elimina un archivo</td>
                        </tr>
                        <tr>
                            <td><code style="white-space: nowrap;">POST</code></td>
                            <td><code style="white-space: nowrap;">/wp-json/WPFM/upload</code></td>
                            <td>Sube o reemplaza archivo</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="content-btn">
        <button
            type="submit"
            name="submit"
            value="Guardar"
            class="button button-primary">
            Guardar
        </button>
    </div>
</form>

<script>
jQuery(function($) {
    var generateBtn = document.getElementById('wpfm-generate-key');
    var copyKeyBtn = document.getElementById('wpfm-copy-key');
    var copyUrlBtn = document.getElementById('wpfm-copy-url');
    var keyInput = document.getElementById('wpfm_api_key');

    function generateApiKey() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = 'wpfm_';
        var array = new Uint8Array(48);
        crypto.getRandomValues(array);
        for (var i = 0; i < 48; i++) {
            result += chars[array[i] % chars.length];
        }
        return result;
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            keyInput.value = generateApiKey();
        });
    }

    if (copyKeyBtn) {
        copyKeyBtn.addEventListener('click', function() {
            keyInput.type = 'text';
            keyInput.select();
            document.execCommand('copy');
            keyInput.type = 'password';
            copyKeyBtn.textContent = '\u2713 Copiado';
            setTimeout(function() { copyKeyBtn.textContent = 'Copiar'; }, 2000);
        });
    }

    if (copyUrlBtn) {
        copyUrlBtn.addEventListener('click', function() {
            var urlInput = document.getElementById('wpfm_endpoint_url');
            urlInput.select();
            document.execCommand('copy');
            copyUrlBtn.textContent = '\u2713 Copiado';
            setTimeout(function() { copyUrlBtn.textContent = 'Copiar URL'; }, 2000);
        });
    }
});
</script>
