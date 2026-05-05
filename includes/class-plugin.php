<?php
namespace TFD;

/**
 * Bootstrap: registra tipo de metadado, tipo de filtro e hooks auxiliares.
 */
class Plugin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot() {
        // Persistência auxiliar (postmeta canônicas para filtro SQL).
        (new Storage())->register();

        // Reescrita de meta_query para filtros nativos do Tainacan.
        (new Query_Hook())->register();

        // Registro do tipo de metadado.
        add_action('tainacan-register-metadata-type', [$this, 'register_metadata_type']);

        // Registro do form-component Vue (segundo hook obrigatório segundo a doc).
        add_action('tainacan-register-vuejs-component', [$this, 'register_form_component']);

        // Registro do tipo de filtro.
        add_action('tainacan-register-filter-type', [$this, 'register_filter_type']);
    }

    public function register_metadata_type($helper) {
        $helper->register_metadata_type(
            'tainacan-flexible-date',
            'TFD\\Flexible_Date',
            TFD_PLUGIN_URL . 'assets/js/metadata-type-flexible-date.js'
        );
    }

    public function register_form_component($helper) {
        if (!is_object($helper) || !method_exists($helper, 'register_vuejs_component')) {
            return;
        }
        $helper->register_vuejs_component(
            'tainacan-form-flexible-date',
            TFD_PLUGIN_URL . 'assets/js/metadata-form-flexible-date.js'
        );
    }

    public function register_filter_type($helper) {
        $helper->register_filter_type(
            'tainacan-filter-flexible-date-interval',
            'TFD\\Flexible_Date_Interval',
            TFD_PLUGIN_URL . 'assets/js/filter-flexible-date-interval.js'
        );
    }
}
