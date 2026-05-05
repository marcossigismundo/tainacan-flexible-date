<?php
namespace TFD;

/**
 * Persistência auxiliar para o tipo Flexible_Date.
 *
 * O metadado padrão do Tainacan grava em postmeta com meta_key = $metadatum_id
 * e meta_value = string crua (ex: "2020-06"). Para que filtros SQL funcionem
 * em qualquer formato, esta classe espelha cada valor em duas postmeta
 * auxiliares com datas canônicas YYYY-MM-DD:
 *
 *   _tfd_<metadatum_id>_start
 *   _tfd_<metadatum_id>_end
 *
 * O filtro Flexible_Date_Interval consulta essas keys via WP_Meta_Query.
 *
 * Hooks:
 *   tainacan-insert-Tainacan\Entities\Item_Metadata_Entity (após persistir)
 *   delete_post (limpa órfãos quando o item é apagado)
 */
class Storage {

    const META_PREFIX_START = '_tfd_';
    const META_SUFFIX_START = '_start';
    const META_SUFFIX_END   = '_end';
    const META_SUFFIX_RAW   = '_raw';

    public function register() {
        add_action('tainacan-insert-Tainacan\\Entities\\Item_Metadata_Entity', [$this, 'on_metadata_persisted']);
        add_action('delete_post', [$this, 'on_post_deleted']);
    }

    /**
     * Disparado pelo Tainacan após gravar um Item_Metadata_Entity.
     * Se o metadatum for do nosso tipo, espelha em postmeta canônicas.
     *
     * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata
     */
    public function on_metadata_persisted($item_metadata) {
        if (!is_object($item_metadata)) return;
        if (!method_exists($item_metadata, 'get_metadatum')) return;

        $metadatum = $item_metadata->get_metadatum();
        if (!$metadatum) return;

        if ($metadatum->get_metadata_type() !== 'TFD\\Flexible_Date') {
            return;
        }

        $item = $item_metadata->get_item();
        if (!$item) return;

        $item_id      = (int) $item->get_id();
        $metadatum_id = (int) $metadatum->get_id();
        if ($item_id <= 0 || $metadatum_id <= 0) return;

        $value = $item_metadata->get_value();
        $values = is_array($value) ? $value : [$value];

        $start_key = self::start_key($metadatum_id);
        $end_key   = self::end_key($metadatum_id);
        $raw_key   = self::raw_key($metadatum_id);

        // Limpar valores anteriores antes de regravar.
        delete_post_meta($item_id, $start_key);
        delete_post_meta($item_id, $end_key);
        delete_post_meta($item_id, $raw_key);

        foreach ($values as $single) {
            $single = (string) $single;
            if ($single === '') continue;

            $normalized = Normalizer::normalize($single);
            // Sempre guardamos a forma crua para reconstrução/exibição.
            add_post_meta($item_id, $raw_key, $single);

            if ($normalized['is_valid']) {
                add_post_meta($item_id, $start_key, $normalized['start']);
                add_post_meta($item_id, $end_key,   $normalized['end']);
            }
        }
    }

    public function on_post_deleted($post_id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Limpando postmeta órfã do plugin com prefixo controlado; cleanup raro disparado por delete_post.
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
            (int) $post_id,
            $wpdb->esc_like(self::META_PREFIX_START) . '%'
        ));
    }

    public static function start_key($metadatum_id) {
        return self::META_PREFIX_START . (int) $metadatum_id . self::META_SUFFIX_START;
    }

    public static function end_key($metadatum_id) {
        return self::META_PREFIX_START . (int) $metadatum_id . self::META_SUFFIX_END;
    }

    public static function raw_key($metadatum_id) {
        return self::META_PREFIX_START . (int) $metadatum_id . self::META_SUFFIX_RAW;
    }
}
