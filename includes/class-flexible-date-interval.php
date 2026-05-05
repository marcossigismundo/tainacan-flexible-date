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
        parent::__construct();
        $this->set_name(__('Intervalo de data flexível', 'tainacan-flexible-date'));
        $this->set_supported_types(['string']);
        $this->set_component('tainacan-filter-flexible-date-interval');
        $this->set_use_max_options(false);
        $this->set_preview_template(
            '<div><input type="text" placeholder="YYYY-MM-DD"> – <input type="text" placeholder="YYYY-MM-DD"></div>'
        );
    }

    /**
     * Habilita este filtro APENAS para metadados Flexible_Date.
     * (set_supported_types('string') deixaria muitos tipos aparecerem na UI.)
     */
    public function supports_metadatum_type($metadata_type) {
        return $metadata_type === 'TFD\\Flexible_Date';
    }
}
