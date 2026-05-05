<?php
namespace TFD;

/**
 * Intercepta a query gerada pela REST API do Tainacan e reescreve as
 * meta_query relativas a metadados do tipo Flexible_Date para usar as
 * postmeta auxiliares canônicas (_tfd_<id>_start e _tfd_<id>_end).
 *
 * Sem esta intervenção, o filtro Date_Interval (que opera com BETWEEN
 * sobre meta_value) não funcionaria, pois o valor cru pode ser "2020"
 * ou "2020-06" ou "2020-06-08/2020-06-10" — formatos que MySQL não
 * reconhece como datas comparáveis.
 *
 * Estratégia: para cada cláusula meta_query cuja key seja o ID de um
 * metadatum Flexible_Date, substituir a key por uma sub-cláusula
 * composta sobre _tfd_<id>_start e _tfd_<id>_end:
 *
 *   - compare = 'BETWEEN' [start_filter, end_filter]:
 *     overlap = (item.end >= start_filter) AND (item.start <= end_filter)
 *
 *   - compare = '=' / '>=' / '<=' / '>' / '<' value:
 *     opera sobre _tfd_<id>_start (data canônica)
 */
class Query_Hook {

    public function register() {
        add_filter('tainacan-api-prepare-items-args', [$this, 'rewrite_meta_query'], 10, 2);
    }

    public function rewrite_meta_query($args, $request = null) {
        if (empty($args['meta_query']) || !is_array($args['meta_query'])) {
            return $args;
        }

        $args['meta_query'] = $this->walk($args['meta_query']);
        return $args;
    }

    /**
     * Caminha recursivamente pela meta_query (que pode ter sub-arrays
     * com 'relation' => 'AND' / 'OR') reescrevendo cláusulas que
     * referenciam metadados Flexible_Date.
     */
    private function walk(array $clauses) {
        foreach ($clauses as $key => $clause) {
            if ($key === 'relation') continue;

            // Sub-grupo aninhado.
            if (is_array($clause) && !isset($clause['key'])) {
                $clauses[$key] = $this->walk($clause);
                continue;
            }

            if (!is_array($clause) || !isset($clause['key'])) continue;

            $metadatum_id = (int) $clause['key'];
            if ($metadatum_id <= 0) continue;

            if (!$this->is_flexible_date_metadatum($metadatum_id)) continue;

            $rewritten = $this->rewrite_clause($metadatum_id, $clause);
            if ($rewritten !== null) {
                $clauses[$key] = $rewritten;
            }
        }
        return $clauses;
    }

    /**
     * Verifica se um metadatum é do tipo Flexible_Date.
     * Cache estático por request.
     */
    private function is_flexible_date_metadatum($metadatum_id) {
        static $cache = [];
        if (isset($cache[$metadatum_id])) return $cache[$metadatum_id];

        if (!class_exists('\\Tainacan\\Repositories\\Metadata')) {
            return $cache[$metadatum_id] = false;
        }

        try {
            $repo = \Tainacan\Repositories\Metadata::get_instance();
            $metadatum = $repo->fetch($metadatum_id);
            if (!$metadatum || is_wp_error($metadatum)) {
                return $cache[$metadatum_id] = false;
            }
            return $cache[$metadatum_id] = ($metadatum->get_metadata_type() === 'TFD\\Flexible_Date');
        } catch (\Throwable $e) {
            return $cache[$metadatum_id] = false;
        }
    }

    /**
     * Reescreve uma cláusula meta_query para operar sobre postmeta canônicas.
     * Retorna null se não houver reescrita aplicável.
     */
    private function rewrite_clause($metadatum_id, array $clause) {
        $start_key = Storage::start_key($metadatum_id);
        $end_key   = Storage::end_key($metadatum_id);

        $compare = isset($clause['compare']) ? strtoupper($clause['compare']) : '=';
        $value   = $clause['value'] ?? null;

        if ($compare === 'BETWEEN' && is_array($value) && count($value) === 2) {
            $a = self::canonicalize_input((string) $value[0], 'start');
            $b = self::canonicalize_input((string) $value[1], 'end');

            // Overlap: item.end >= a AND item.start <= b
            return [
                'relation' => 'AND',
                [
                    'key'     => $end_key,
                    'value'   => $a,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => $start_key,
                    'value'   => $b,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
            ];
        }

        // Comparações simples — operam sobre a data inicial canônica.
        if (in_array($compare, ['=', '!=', '>', '>=', '<', '<='], true)) {
            return [
                'key'     => $start_key,
                'value'   => self::canonicalize_input((string) $value, 'start'),
                'compare' => $compare,
                'type'    => 'DATE',
            ];
        }

        if ($compare === 'EXISTS' || $compare === 'NOT EXISTS') {
            return [
                'key'     => $start_key,
                'compare' => $compare,
            ];
        }

        return null;
    }

    /**
     * Aceita valor parcial vindo do filtro UI e canonicaliza para YYYY-MM-DD.
     * Se for parcial, use o início para 'start' e o final para 'end'.
     */
    private static function canonicalize_input($value, $boundary) {
        $n = Normalizer::normalize($value);
        if ($n['is_valid']) {
            return $boundary === 'end' ? $n['end'] : $n['start'];
        }
        // fallback: devolve a string como veio (MySQL DATE rejeita silenciosamente).
        return $value;
    }
}
