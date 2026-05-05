<?php
namespace TFD;

/**
 * Converte strings de data em formatos variados para a representação interna
 * canônica usada pelo plugin: ['raw', 'start', 'end', 'precision', 'is_valid'].
 *
 * - 'raw'       string original do XML/usuário (preservada para exibição)
 * - 'start'     YYYY-MM-DD canônico (ou null se não interpretável)
 * - 'end'       YYYY-MM-DD canônico (igual a 'start' quando não é intervalo)
 * - 'precision' 'day' | 'month' | 'year' | 'range' | 'unknown'
 * - 'is_valid'  bool — se foi possível extrair pelo menos uma start válida
 */
class Normalizer {

    /**
     * Normaliza um valor de data.
     *
     * Formatos aceitos:
     *   YYYY-MM-DD                     → start = end = data
     *   YYYY-MM                        → start = YYYY-MM-01, end = YYYY-MM-(último dia)
     *   YYYY                           → start = YYYY-01-01, end = YYYY-12-31
     *   YYYY-MM-DD/YYYY-MM-DD          → intervalo
     *   YYYY-MM/YYYY-MM, YYYY/YYYY     → intervalo (precisão reduzida)
     *   DD/MM/YYYY                     → BR (dia/mês/ano)
     *   DD-MM-YYYY                     → BR (dia-mês-ano)
     *   YYYY-MM-DDTHH:MM:SS[Z]         → ISO com hora (descarta hora)
     *   '', null, 's.d.', 's/d', '[s.d.]' → not valid
     *
     * @param string $raw
     * @return array{raw:string,start:?string,end:?string,precision:string,is_valid:bool}
     */
    public static function normalize($raw) {
        $raw = is_string($raw) ? trim($raw) : '';

        $result = [
            'raw'       => $raw,
            'start'     => null,
            'end'       => null,
            'precision' => 'unknown',
            'is_valid'  => false,
        ];

        if ($raw === '' || self::is_no_date_marker($raw)) {
            return $result;
        }

        // Intervalo (separador / ou ..)
        $sep = null;
        foreach (['/', '..'] as $candidate) {
            if (strpos($raw, $candidate) !== false && substr_count($raw, $candidate) === 1) {
                $sep = $candidate;
                break;
            }
        }

        if ($sep !== null) {
            [$a, $b] = array_map('trim', explode($sep, $raw, 2));
            $start = self::parse_single($a);
            $end   = self::parse_single($b);

            if ($start['start'] !== null && $end['end'] !== null) {
                $result['start']     = $start['start'];
                $result['end']       = $end['end'];
                $result['precision'] = 'range';
                $result['is_valid']  = true;
            } elseif ($start['start'] !== null) {
                $result['start']     = $start['start'];
                $result['end']       = $start['end'];
                $result['precision'] = $start['precision'];
                $result['is_valid']  = true;
            }
            return $result;
        }

        $single = self::parse_single($raw);
        if ($single['start'] !== null) {
            $result['start']     = $single['start'];
            $result['end']       = $single['end'];
            $result['precision'] = $single['precision'];
            $result['is_valid']  = true;
        }
        return $result;
    }

    /**
     * Tenta interpretar UM token de data simples (não intervalo).
     * @return array{start:?string,end:?string,precision:string}
     */
    private static function parse_single($value) {
        $value = trim($value);
        $out = ['start' => null, 'end' => null, 'precision' => 'unknown'];

        if ($value === '') return $out;

        // YYYY-MM-DD (com ou sem hora)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[T ].*)?$/', $value, $m)) {
            $iso = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
            if (self::is_valid_ymd($m[1], $m[2], $m[3])) {
                $out['start']     = $iso;
                $out['end']       = $iso;
                $out['precision'] = 'day';
            }
            return $out;
        }

        // YYYY-MM
        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $m)) {
            if (self::is_valid_ymd($m[1], $m[2], '01')) {
                $out['start']     = sprintf('%04d-%02d-01', $m[1], $m[2]);
                $out['end']       = sprintf('%04d-%02d-%02d', $m[1], $m[2], self::last_day($m[1], $m[2]));
                $out['precision'] = 'month';
            }
            return $out;
        }

        // YYYY
        if (preg_match('/^(\d{4})$/', $value, $m)) {
            $out['start']     = sprintf('%04d-01-01', $m[1]);
            $out['end']       = sprintf('%04d-12-31', $m[1]);
            $out['precision'] = 'year';
            return $out;
        }

        // DD/MM/YYYY ou DD-MM-YYYY (BR)
        if (preg_match('#^(\d{2})[/-](\d{2})[/-](\d{4})$#', $value, $m)) {
            if (self::is_valid_ymd($m[3], $m[2], $m[1])) {
                $iso = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                $out['start']     = $iso;
                $out['end']       = $iso;
                $out['precision'] = 'day';
            }
            return $out;
        }

        // MM/YYYY (BR mês/ano)
        if (preg_match('#^(\d{2})/(\d{4})$#', $value, $m)) {
            if (self::is_valid_ymd($m[2], $m[1], '01')) {
                $out['start']     = sprintf('%04d-%02d-01', $m[2], $m[1]);
                $out['end']       = sprintf('%04d-%02d-%02d', $m[2], $m[1], self::last_day($m[2], $m[1]));
                $out['precision'] = 'month';
            }
            return $out;
        }

        return $out;
    }

    /**
     * Renderiza um valor normalizado como string amigável para exibição.
     * Respeita a configuração de date_format do WordPress quando possível.
     */
    public static function format_for_display($value) {
        $normalized = self::normalize($value);

        if (!$normalized['is_valid']) {
            return $normalized['raw']; // devolve a string original (inclusive 's.d.', texto livre)
        }

        $df = get_option('date_format');

        switch ($normalized['precision']) {
            case 'day':
                return mysql2date($df, $normalized['start']);
            case 'month':
                // ex: "junho de 2020"
                return mysql2date('F \d\e Y', $normalized['start']);
            case 'year':
                return substr($normalized['start'], 0, 4);
            case 'range':
                $a = mysql2date($df, $normalized['start']);
                $b = mysql2date($df, $normalized['end']);
                return $a . ' – ' . $b;
            default:
                return $normalized['raw'];
        }
    }

    private static function is_valid_ymd($y, $m, $d) {
        return checkdate((int) $m, (int) $d, (int) $y);
    }

    private static function last_day($y, $m) {
        // gmdate em vez de date() para conformidade com WordPress.DateTime.RestrictedFunctions.
        return (int) gmdate('t', mktime(0, 0, 0, (int) $m, 1, (int) $y));
    }

    private static function is_no_date_marker($value) {
        $clean = strtolower(trim($value, " \t\n\r\0\x0B[]"));
        return in_array($clean, ['s.d.', 's/d', 'sd', 'sem data', 'n/d', 'na'], true);
    }
}
