<?php
namespace TFD;

/**
 * Tipo de filtro "Intervalo de data flexível".
 *
 * Suporta o tipo de metadado `tainacan-flexible-date`. O componente Vue
 * envia um par [data_inicial, data_final] com compare = 'BETWEEN'; a
 * reescrita SQL é feita pelo {@see Query_Hook}, que troca a meta_query
 * para operar sobre as postmeta canônicas (_tfd_<id>_start/_end).
 */
class Flexible_Date_Interval extends \Tainacan\Filter_Types\Filter_Type {

    public function __construct() {
        // ATENÇÃO: NÃO chamar parent::__construct().
        //
        // O construtor de \Tainacan\Filter_Types\Filter_Type registra
        //   add_action('register_filter_types', [&$this, 'register_filter_type'])
        // o que exige que toda subclasse implemente register_filter_type().
        // Os filter types nativos do Tainacan (Date_Interval, Numeric_Interval,
        // etc.) deliberadamente NÃO chamam parent::__construct() para não
        // disparar essa registração. Seguimos o mesmo padrão.
        $this->set_name(__('Intervalo de data flexível', 'tainacan-flexible-date'));
        // Apenas metadados do tipo Flexible_Date (primitive_type = 'flexible-date')
        // recebem este filtro. Nenhum tipo nativo do Tainacan usa esse identificador.
        $this->set_supported_types(['flexible-date']);
        $this->set_component('tainacan-filter-flexible-date-interval');
        $this->set_use_max_options(false);
        $this->set_preview_template(
            '<div><input type="text" placeholder="YYYY-MM-DD"> – <input type="text" placeholder="YYYY-MM-DD"></div>'
        );
    }
}
