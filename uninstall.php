<?php
/**
 * Limpeza ao desinstalar o plugin.
 *
 * Remove apenas postmeta auxiliares com prefixo controlado (_tfd_*).
 * Os metadados Tainacan em si (entradas no postmeta com meta_key = ID
 * do metadatum) NÃO são tocados — o usuário pode reverter o tipo do
 * metadatum para outro tipo no Tainacan e os valores crus permanecem.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Limpeza única de postmeta com prefixo controlado pelo plugin durante uninstall.
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
    $wpdb->esc_like('_tfd_') . '%'
));
