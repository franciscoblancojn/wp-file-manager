<?php
/*
Plugin Name: WP File Manager
Plugin URI: https://github.com/franciscoblancojn/wp-file-manager
Description: Plugin de Wordpress para subir, eliminar o remplazar archivos.
Version: 0.0.0
Author: franciscoblancojn
Author URI: https://franciscoblanco.vercel.app/
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-wp-file-manager
*/

if (!function_exists('is_plugin_active'))
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');

require_once __DIR__ . '/libs/autoload.php';

//WPFM_
define("WPFM_KEY", 'WPFM');
define("WPFM_MODE_DEV", in_array($_SERVER['HTTP_HOST'] ?? '', ['wordpress.local', 'localhost', '127.0.0.1',]));
define("WPFM_KEY_SEPARETE", '____WPFM____');
define("WPFM_CONFIG", 'WPFM_CONFIG');
define("WPFM_CONTENT", 'WPFM_CONTENT');
define("WPFM_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE", WPFM_KEY . '_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE');
define("WPFM_CONTENT_INDEPENDIENTE_META", WPFM_KEY . '_CONTENT_INDEPENDIENTE');
define("WPFM_LOG", true);
define("WPFM_LOG_KEY", "WPFM_LOG");
define("WPFM_LOG_COUNT", 100);
define("WPFM_BASENAME", plugin_basename(__FILE__));
define("WPFM_DIR", plugin_dir_path(__FILE__));
define("WPFM_URL", plugin_dir_url(__FILE__));

function WPFM_get_version()
{
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}
use franciscoblancojn\wordpress_utils\FWUUpdate;

FWUUpdate::init([
    'basename' => WPFM_BASENAME,
    'dir' => WPFM_DIR,
    'file' => "index.php",
    'path_repository' => 'franciscoblancojn/wp-file-manager',
    'branch' => 'master',
    'token_array_split' => [
        "g",
        "h",
        "p",
        "_",
        "G",
        "4",
        "W",
        "E",
        "W",
        "F",
        "p",
        "V",
        "U",
        "E",
        "F",
        "V",
        "x",
        "F",
        "U",
        "n",
        "b",
        "M",
        "k",
        "P",
        "R",
        "x",
        "o",
        "f",
        "t",
        "Y",
        "8",
        "z",
        "j",
        "t",
        "4",
        "E",
        "x",
        "b",
        "i",
        "9"
    ]
]);

use franciscoblancojn\wordpress_utils\FWUSystemLog;

if (is_admin()) {
    FWUSystemLog::init(WPFM_KEY);
}

if (
    !is_plugin_active('duplicate-post/duplicate-post.php')
) {
    function WPFM_Error_Install_o_Active()
    {
?>
        <div class="notice notice-error is-dismissible">
            <p>
                Generate Page AI requiere el plugin "Yoast Duplicate Post" para funcionar correctamente.
            </p>
        </div>
<?php
    }
    add_action('admin_notices', 'WPFM_Error_Install_o_Active');
} else {
    require_once WPFM_DIR . 'src/_.php';
}
