<?php
/**
 * Plugin Name: Tainacan Flexible Date
 * Plugin URI:  https://github.com/marcossigismundo/tainacan-flexible-date
 * Description: Tipo de metadado para Tainacan que aceita formatos flexíveis de data — YYYY, YYYY-MM, YYYY-MM-DD, intervalos (YYYY-MM-DD/YYYY-MM-DD), e variações brasileiras. Inclui filtro de busca por intervalo que opera nativamente sobre datas normalizadas internamente.
 * Version:     1.0.0
 * Author:      Tainacan Team
 * License:     GPL-3.0+
 * Text Domain: tainacan-flexible-date
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TFD_VERSION', '1.0.0');
define('TFD_PLUGIN_FILE', __FILE__);
define('TFD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TFD_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once TFD_PLUGIN_DIR . 'includes/class-normalizer.php';
require_once TFD_PLUGIN_DIR . 'includes/class-storage.php';
require_once TFD_PLUGIN_DIR . 'includes/class-query-hook.php';
require_once TFD_PLUGIN_DIR . 'includes/class-plugin.php';

add_action('plugins_loaded', function () {
    // A classe do metadatum precisa de \Tainacan\Metadata_Types\Metadata_Type carregado.
    if (!class_exists('\Tainacan\Metadata_Types\Metadata_Type')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Tainacan Flexible Date:</strong> requer o plugin Tainacan ativo.</p></div>';
        });
        return;
    }

    require_once TFD_PLUGIN_DIR . 'includes/class-flexible-date.php';
    require_once TFD_PLUGIN_DIR . 'includes/class-flexible-date-interval.php';

    \TFD\Plugin::instance()->boot();
});
