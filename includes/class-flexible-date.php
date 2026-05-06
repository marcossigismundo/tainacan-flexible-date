<?php
namespace TFD;

use Tainacan\Entities\Item_Metadata_Entity;

/**
 * Tipo de metadado "Data flexível" para o Tainacan.
 *
 * Aceita formatos diversos: YYYY, YYYY-MM, YYYY-MM-DD, intervalos com '/',
 * datas brasileiras (DD/MM/YYYY) e marcadores como 's.d.'. Internamente,
 * cada valor é espelhado em postmeta canônicas pelo {@see Storage} para
 * permitir filtros SQL nativos via {@see Flexible_Date_Interval}.
 */
class Flexible_Date extends \Tainacan\Metadata_Types\Metadata_Type {

    public function __construct() {
        parent::__construct();
        // primitive_type DEVE ser escalar (string) — a validação em
        // Filter_Type::validate_options usa in_array($primitive, $supported_types)
        // que só funciona com escalar. Usamos um identificador único
        // ('flexible-date') para garantir que só o nosso filtro
        // (Flexible_Date_Interval, com supported_types=['flexible-date'])
        // seja oferecido para metadados deste tipo — filtros nativos como
        // Date_Interval não funcionariam em valores parciais ou intervalos.
        $this->set_primitive_type('flexible-date');
        $this->set_component('tainacan-flexible-date');
        $this->set_form_component('tainacan-form-flexible-date');
        $this->set_name(__('Data flexível', 'tainacan-flexible-date'));
        $this->set_description(__(
            'Data com suporte a formatos parciais (apenas ano, ano-mês), intervalos e variações brasileiras. Permite filtros nativos do Tainacan.',
            'tainacan-flexible-date'
        ));
        $this->set_preview_template('<input type="text" placeholder="YYYY-MM-DD ou YYYY-MM ou YYYY/YYYY">');
        $this->set_default_options([
            'allow_partial' => 'yes',
            'allow_range'   => 'yes',
        ]);
    }

    public function get_form_labels() {
        return [
            'allow_partial' => [
                'title'       => __('Permitir datas parciais', 'tainacan-flexible-date'),
                'description' => __('Aceita YYYY e YYYY-MM além de YYYY-MM-DD.', 'tainacan-flexible-date'),
            ],
            'allow_range' => [
                'title'       => __('Permitir intervalos', 'tainacan-flexible-date'),
                'description' => __('Aceita "YYYY-MM-DD/YYYY-MM-DD" como intervalo.', 'tainacan-flexible-date'),
            ],
        ];
    }

    /**
     * Valida o valor enviado pelo usuário/importador.
     * Aceita string vazia. Rejeita só quando a entrada não é interpretável
     * como data nem como marcador "sem data".
     */
    public function validate(Item_Metadata_Entity $item_metadata) {
        $value = $item_metadata->get_value();
        $values = is_array($value) ? $value : [$value];

        $opts            = $this->get_options();
        $allow_partial   = !isset($opts['allow_partial']) || $opts['allow_partial'] === 'yes';
        $allow_range     = !isset($opts['allow_range'])   || $opts['allow_range']   === 'yes';

        foreach ($values as $single) {
            $single = is_string($single) ? trim($single) : '';
            if ($single === '') continue;

            $n = Normalizer::normalize($single);

            if (!$n['is_valid']) {
                // Aceita marcadores 's.d.', 's/d', etc. — guardados como texto.
                if (self::looks_like_no_date($single)) continue;

                $this->add_error(sprintf(
                    /* translators: %s = valor de data inválido informado pelo usuário */
                    __('Data em formato não reconhecido: %s', 'tainacan-flexible-date'),
                    $single
                ));
                return false;
            }

            if (!$allow_partial && in_array($n['precision'], ['year', 'month'], true)) {
                $this->add_error(__('Datas parciais não estão habilitadas para este metadado.', 'tainacan-flexible-date'));
                return false;
            }

            if (!$allow_range && $n['precision'] === 'range') {
                $this->add_error(__('Intervalos de data não estão habilitados para este metadado.', 'tainacan-flexible-date'));
                return false;
            }
        }

        return true;
    }

    /**
     * Renderiza o valor para exibição no frontend.
     * Escape no ponto de saída — a string formatada vai para HTML.
     */
    public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
        $value = $item_metadata->get_value();

        if ($item_metadata->is_multiple() && is_array($value)) {
            $parts = [];
            foreach ($value as $single) {
                $parts[] = esc_html(Normalizer::format_for_display((string) $single));
            }
            $separator = '<span class="multivalue-separator"> | </span>';
            return implode($separator, $parts);
        }

        return esc_html(Normalizer::format_for_display((string) $value));
    }

    /**
     * Versão texto plano do valor (sem HTML), usada por Tainacan em alguns
     * contextos como busca, exportação CSV, etc.
     */
    public function get_value_as_string(Item_Metadata_Entity $item_metadata) {
        $value = $item_metadata->get_value();

        if (is_array($value)) {
            $parts = array_map(fn($v) => Normalizer::format_for_display((string) $v), $value);
            return implode(', ', $parts);
        }

        return Normalizer::format_for_display((string) $value);
    }

    private static function looks_like_no_date($value) {
        $clean = strtolower(trim($value, " \t\n\r\0\x0B[]"));
        return in_array($clean, ['s.d.', 's/d', 'sd', 'sem data', 'n/d', 'na'], true);
    }
}
