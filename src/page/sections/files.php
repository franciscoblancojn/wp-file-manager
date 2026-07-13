<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;
use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;

$respond_files = [];

if (isset($_POST['save']) && $_POST['save'] === 'wpfm_upload') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('wpfm_upload_file', 'wpfm_upload_nonce');

    if (empty($_FILES['wpfm_file']['name'])) {
        $respond_files = ['status' => 'error', 'message' => 'No se seleccionó ningún archivo.'];
    } else {
        $file = $_FILES['wpfm_file'];
        $name = sanitize_file_name($file['name']);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $respond_files = ['status' => 'error', 'message' => 'Error al subir el archivo (código: ' . $file['error'] . ').'];
        } else {
            $upload_dir = wp_upload_dir();
            $wpfm_dir = $upload_dir['basedir'] . '/WPFM';
            if (!is_dir($wpfm_dir)) {
                wp_mkdir_p($wpfm_dir);
            }
            $file_path = $wpfm_dir . '/' . $name;

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                FWUSystemLog::add(WPFM_KEY, [
                    'type' => 'ADMIN_FILE_UPLOAD',
                    'file' => $name,
                    'size' => filesize($file_path),
                ]);
                $respond_files = ['status' => 'ok', 'message' => 'Archivo "' . esc_html($name) . '" subido correctamente.'];
            } else {
                $respond_files = ['status' => 'error', 'message' => 'Error al guardar el archivo en el servidor.'];
            }
        }
    }
}

if (isset($_POST['save']) && $_POST['save'] === 'wpfm_delete') {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos.');
    }
    check_admin_referer('wpfm_delete_file', 'wpfm_delete_nonce');

    $name = sanitize_file_name($_POST['wpfm_delete_name'] ?? '');
    if (empty($name)) {
        $respond_files = ['status' => 'error', 'message' => 'Nombre de archivo no válido.'];
    } else {
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/WPFM/' . $name;

        if (file_exists($file_path) && is_file($file_path)) {
            if (unlink($file_path)) {
                FWUSystemLog::add(WPFM_KEY, [
                    'type' => 'ADMIN_FILE_DELETE',
                    'file' => $name,
                ]);
                $respond_files = ['status' => 'ok', 'message' => 'Archivo "' . esc_html($name) . '" eliminado.'];
            } else {
                $respond_files = ['status' => 'error', 'message' => 'Error al eliminar el archivo.'];
            }
        } else {
            $respond_files = ['status' => 'error', 'message' => 'Archivo no encontrado.'];
        }
    }
}

$upload_dir = wp_upload_dir();
$wpfm_dir = $upload_dir['basedir'] . '/WPFM';
$wpfm_files = [];

if (is_dir($wpfm_dir)) {
    $scan = scandir($wpfm_dir);
    if ($scan !== false) {
        foreach ($scan as $file) {
            if ($file === '.' || $file === '..') continue;
            $file_path = $wpfm_dir . '/' . $file;
            if (!is_file($file_path)) continue;
            $wpfm_files[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'size_human' => size_format(filesize($file_path)),
                'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                'url' => $upload_dir['baseurl'] . '/WPFM/' . $file,
            ];
        }
    }
}

usort($wpfm_files, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<?php FWURespond::render($respond_files) ?>

<?php FWUCollapse::render('Subir Archivo', '
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save" value="wpfm_upload">
        ' . wp_nonce_field('wpfm_upload_file', 'wpfm_upload_nonce', true, false) . '
        <table class="form-table">
            <tr>
                <th scope="row">
                    ' . FWUTooltip::html(
                        "Archivo",
                        "Selecciona un archivo para subir a la carpeta uploads/WPFM/. Si ya existe un archivo con el mismo nombre, será reemplazado."
                    ) . '
                </th>
                <td>
                    <input type="file" name="wpfm_file" required />
                </td>
            </tr>
        </table>
        <div class="content-btn">
            <button type="submit" name="submit" value="Subir" class="button button-primary">
                Subir Archivo
            </button>
        </div>
    </form>
', true) ?>

<h3>Archivos en uploads/WPFM/</h3>

<?php if (empty($wpfm_files)): ?>
    <p>No hay archivos en la carpeta WPFM.</p>
<?php else: ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:30%;">Nombre</th>
                <th style="width:15%;">Tamaño</th>
                <th style="width:20%;">Modificado</th>
                <th style="width:35%;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($wpfm_files as $f): ?>
                <tr>
                    <td>
                        <a href="<?= esc_url($f['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= esc_html($f['name']) ?>
                        </a>
                    </td>
                    <td><?= esc_html($f['size_human']) ?></td>
                    <td><?= esc_html($f['modified']) ?></td>
                    <td>
                        <a href="<?= esc_url($f['url']) ?>" class="button" target="_blank" rel="noopener noreferrer">
                            Descargar
                        </a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar archivo <?= esc_js($f['name']) ?>?');">
                            <input type="hidden" name="save" value="wpfm_delete">
                            <input type="hidden" name="wpfm_delete_name" value="<?= esc_attr($f['name']) ?>">
                            <?php wp_nonce_field('wpfm_delete_file', 'wpfm_delete_nonce'); ?>
                            <button type="submit" class="button" style="color:#d63638;">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
