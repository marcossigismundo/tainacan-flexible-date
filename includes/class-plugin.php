<?php
namespace TFD;

/**
 * Bootstrap: registra tipo de metadado, tipo de filtro e hooks auxiliares.
 *
 * IMPORTANTE: o Tainacan instancia seus singletons (Metadata_Type_Helper,
 * Filter_Type_Helper, Component_Hooks) durante o INCLUDE do tainacan-creator.php,
 * que roda no top-level do tainacan.php. Como nosso plugin é carregado em
 * ordem alfabética DEPOIS do Tainacan, o `do_action('tainacan-register-...')`
 * dispara antes que possamos registrar listeners — eles nunca seriam chamados.
 *
 * Solução: ainda registramos as actions (caso o Tainacan re-dispare em algum
 * fluxo), MAS também chamamos os helpers diretamente via get_instance() em
 * `plugins_loaded`, o que funciona em qualquer ordem de carregamento.
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

        // 1) Listeners das actions (defensivo — caso Tainacan dispare novamente).
        add_action('tainacan-register-metadata-type', [$this, 'register_metadata_type']);
        add_action('tainacan-register-vuejs-component', [$this, 'register_form_component']);
        add_action('tainacan-register-filter-type', [$this, 'register_filter_type']);

        // 2) Registro DIRETO via singletons — caminho confiável que ignora
        //    a janela de timing do hook.
        $this->register_directly();
    }

    private function register_directly() {
        if (class_exists('\\Tainacan\\Metadata_Types\\Metadata_Type_Helper')) {
            $helper = \Tainacan\Metadata_Types\Metadata_Type_Helper::get_instance();
            $this->register_metadata_type($helper);
        }

        if (class_exists('\\Tainacan\\Component_Hooks')) {
            $component_hooks = \Tainacan\Component_Hooks::get_instance();
            $this->register_form_component($component_hooks);
        }

        if (class_exists('\\Tainacan\\Filter_Types\\Filter_Type_Helper')) {
            $filter_helper = \Tainacan\Filter_Types\Filter_Type_Helper::get_instance();
            $this->register_filter_type($filter_helper);
        }
    }

    public function register_metadata_type($helper) {
        if (!is_object($helper) || !method_exists($helper, 'register_metadata_type')) return;
        $helper->register_metadata_type(
            'tainacan-flexible-date',
            'TFD\\Flexible_Date',
            TFD_PLUGIN_URL . 'assets/js/metadata-type-flexible-date.js'
        );
    }

    public function register_form_component($helper) {
        if (!is_object($helper) || !method_exists($helper, 'register_vuejs_component')) return;
        $helper->register_vuejs_component(
            'tainacan-form-flexible-date',
            TFD_PLUGIN_URL . 'assets/js/metadata-form-flexible-date.js'
        );
    }

    public function register_filter_type($helper) {
        if (!is_object($helper) || !method_exists($helper, 'register_filter_type')) return;
        $helper->register_filter_type(
            'tainacan-filter-flexible-date-interval',
            'TFD\\Flexible_Date_Interval',
            TFD_PLUGIN_URL . 'assets/js/filter-flexible-date-interval.js'
        );
    }
}
