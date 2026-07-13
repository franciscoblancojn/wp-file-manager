<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class WPFM_API
{
    public static function init()
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function getUploadDir()
    {
        $upload_dir = wp_upload_dir();
        $wpfm_dir = $upload_dir['basedir'] . '/WPFM';
        if (!is_dir($wpfm_dir)) {
            wp_mkdir_p($wpfm_dir);
        }
        return $wpfm_dir;
    }

    public static function registerRoutes()
    {
        register_rest_route(WPFM_KEY, '/list', [
            'methods' => 'GET',
            'callback' => [self::class, 'handleList'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);

        register_rest_route(WPFM_KEY, '/get', [
            'methods' => 'GET',
            'callback' => [self::class, 'handleGet'],
            'permission_callback' => [self::class, 'checkPermission'],
            'args' => [
                'name' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_file_name',
                ],
            ],
        ]);

        register_rest_route(WPFM_KEY, '/delete', [
            'methods' => 'DELETE',
            'callback' => [self::class, 'handleDelete'],
            'permission_callback' => [self::class, 'checkPermission'],
            'args' => [
                'name' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_file_name',
                ],
            ],
        ]);

        register_rest_route(WPFM_KEY, '/upload', [
            'methods' => 'POST',
            'callback' => [self::class, 'handleUpload'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);

        register_rest_route(WPFM_KEY, '/upload/base64', [
            'methods' => 'POST',
            'callback' => [self::class, 'handleUploadBase64'],
            'permission_callback' => [self::class, 'checkPermission'],
        ]);
    }

    public static function checkPermission($request)
    {
        $config = new WPFM_USE_DATA_CONFIG();
        $data = $config->get();

        if (empty($data['api_enabled'])) {
            return new WP_Error('api_disabled', 'API deshabilitada.', ['status' => 403]);
        }

        $headerKey = $request->get_header('X-WPFM-Key');
        if (!$headerKey) {
            return new WP_Error('missing_key', 'API Key requerida en header X-WPFM-Key.', ['status' => 401]);
        }

        if (!hash_equals($data['api_key'], $headerKey)) {
            return new WP_Error('invalid_key', 'API Key inválida.', ['status' => 401]);
        }

        return true;
    }

    public static function sanitizeFileName($name)
    {
        $name = sanitize_file_name($name);
        $name = str_replace('..', '', $name);
        $name = preg_replace('/[\/\\\\]/', '', $name);
        return $name;
    }

    public static function isPathSafe($file_path, $base_dir)
    {
        $real_base = realpath($base_dir);
        $real_file = realpath($file_path);
        if ($real_base === false || $real_file === false) {
            return false;
        }
        return strpos($real_file, $real_base) === 0;
    }

    public static function handleList($request)
    {
        $dir = self::getUploadDir();

        if (!is_dir($dir)) {
            return new WP_REST_Response([
                'success' => true,
                'data' => [],
            ], 200);
        }

        $files = [];
        $scan = scandir($dir);
        if ($scan !== false) {
            foreach ($scan as $file) {
                if ($file === '.' || $file === '..') continue;
                $file_path = $dir . '/' . $file;
                if (!is_file($file_path)) continue;

                $files[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'size_human' => size_format(filesize($file_path)),
                    'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                    'url' => wp_upload_dir()['baseurl'] . '/WPFM/' . $file,
                    'mime' => wp_check_filetype($file)['type'] ?? 'application/octet-stream',
                ];
            }
        }

        usort($files, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return new WP_REST_Response([
            'success' => true,
            'data' => $files,
        ], 200);
    }

    public static function handleGet($request)
    {
        $name = self::sanitizeFileName($request->get_param('name'));

        if (empty($name)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de archivo requerido.',
            ], 400);
        }

        $dir = self::getUploadDir();
        $file_path = $dir . '/' . $name;

        if (!self::isPathSafe($file_path, $dir)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Ruta no válida.',
            ], 400);
        }

        if (!file_exists($file_path) || !is_file($file_path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        if ($request->get_param('download')) {
            $mime = wp_check_filetype($name)['type'] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'name' => $name,
                'size' => filesize($file_path),
                'size_human' => size_format(filesize($file_path)),
                'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                'url' => wp_upload_dir()['baseurl'] . '/WPFM/' . $name,
                'mime' => wp_check_filetype($name)['type'] ?? 'application/octet-stream',
            ],
        ], 200);
    }

    public static function handleDelete($request)
    {
        $name = self::sanitizeFileName($request->get_param('name'));

        if (empty($name)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de archivo requerido.',
            ], 400);
        }

        $dir = self::getUploadDir();
        $file_path = $dir . '/' . $name;

        if (!self::isPathSafe($file_path, $dir)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Ruta no válida.',
            ], 400);
        }

        if (!file_exists($file_path) || !is_file($file_path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        if (!unlink($file_path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error al eliminar el archivo.',
            ], 500);
        }

        FWUSystemLog::add(WPFM_KEY, [
            'type' => 'API_FILE_DELETE',
            'file' => $name,
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Archivo eliminado correctamente.',
            'data' => ['name' => $name],
        ], 200);
    }

    public static function handleUpload($request)
    {
        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se envió ningún archivo.',
            ], 400);
        }

        $file = $files['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario.',
                UPLOAD_ERR_PARTIAL => 'El archivo fue subido parcialmente.',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal del servidor.',
                UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo.',
                UPLOAD_ERR_EXTENSION => 'Una extensión del servidor interrumpió la subida.',
            ];
            $msg = $error_messages[$file['error']] ?? 'Error desconocido al subir archivo.';
            return new WP_REST_Response([
                'success' => false,
                'message' => $msg,
            ], 400);
        }

        $name = sanitize_file_name($file['name']);
        $custom_name = $request->get_param('name');
        if (!empty($custom_name)) {
            $name = self::sanitizeFileName($custom_name);
        }

        if (empty($name)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de archivo no válido.',
            ], 400);
        }

        $dir = self::getUploadDir();
        $file_path = $dir . '/' . $name;

        if (!self::isPathSafe($file_path, $dir)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Ruta no válida.',
            ], 400);
        }

        $is_replace = file_exists($file_path);

        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error al guardar el archivo.',
            ], 500);
        }

        FWUSystemLog::add(WPFM_KEY, [
            'type' => $is_replace ? 'API_FILE_REPLACE' : 'API_FILE_UPLOAD',
            'file' => $name,
            'size' => filesize($file_path),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => $is_replace ? 'Archivo reemplazado correctamente.' : 'Archivo subido correctamente.',
            'data' => [
                'name' => $name,
                'size' => filesize($file_path),
                'size_human' => size_format(filesize($file_path)),
                'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                'url' => wp_upload_dir()['baseurl'] . '/WPFM/' . $name,
                'mime' => wp_check_filetype($name)['type'] ?? 'application/octet-stream',
                'replaced' => $is_replace,
            ],
        ], 200);
    }

    public static function handleUploadBase64($request)
    {
        $params = $request->get_json_params();

        if (empty($params['name'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de archivo requerido.',
            ], 400);
        }

        if (empty($params['file'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Contenido del archivo (base64) requerido.',
            ], 400);
        }

        $name = self::sanitizeFileName($params['name']);
        if (empty($name)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de archivo no válido.',
            ], 400);
        }

        $base64 = $params['file'];

        $base64 = preg_replace('/^data:[^;]+;base64,/', '', $base64);

        if (!preg_match('/^[A-Za-z0-9+\/=\s]+$/', $base64)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'El contenido no es un base64 válido.',
            ], 400);
        }

        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error al decodificar el base64.',
            ], 400);
        }

        $dir = self::getUploadDir();
        $file_path = $dir . '/' . $name;

        if (!self::isPathSafe($file_path, $dir)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Ruta no válida.',
            ], 400);
        }

        $is_replace = file_exists($file_path);

        if (file_put_contents($file_path, $decoded) === false) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Error al guardar el archivo.',
            ], 500);
        }

        $mime = !empty($params['mimetype'])
            ? sanitize_text_field($params['mimetype'])
            : (wp_check_filetype($name)['type'] ?? 'application/octet-stream');

        FWUSystemLog::add(WPFM_KEY, [
            'type' => $is_replace ? 'API_FILE_REPLACE_BASE64' : 'API_FILE_UPLOAD_BASE64',
            'file' => $name,
            'size' => filesize($file_path),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => $is_replace ? 'Archivo reemplazado correctamente.' : 'Archivo subido correctamente.',
            'data' => [
                'name' => $name,
                'size' => filesize($file_path),
                'size_human' => size_format(filesize($file_path)),
                'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                'url' => wp_upload_dir()['baseurl'] . '/WPFM/' . $name,
                'mime' => $mime,
                'replaced' => $is_replace,
            ],
        ], 200);
    }
}
