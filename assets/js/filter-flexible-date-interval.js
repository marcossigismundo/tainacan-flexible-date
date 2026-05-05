/**
 * Componente do filtro "Intervalo de Data Flexível".
 *
 * Usa <b-input> do Buefy para harmonizar com o admin/tema do Tainacan.
 * Renderiza dois inputs (de/até) e emite [start, end] que o backend
 * (Query_Hook) traduz para meta_query sobre as postmeta canônicas.
 */
(function () {
    'use strict';

    window.tainacan_extra_components =
        typeof window.tainacan_extra_components !== 'undefined'
            ? window.tainacan_extra_components
            : {};

    var TainacanFilterFlexibleDateInterval = {
        name: 'TainacanFilterFlexibleDateInterval',
        props: {
            filter: Object,
            metadatumId: [String, Number],
            collectionId: [String, Number],
            value: { type: Array, default: function () { return []; } },
            isUsingElasticSearch: { type: Boolean, default: false }
        },
        data: function () {
            return {
                from: this.value[0] || '',
                until: this.value[1] || ''
            };
        },
        watch: {
            value: function (newVal) {
                this.from = (newVal && newVal[0]) || '';
                this.until = (newVal && newVal[1]) || '';
            }
        },
        methods: {
            emitChange: function () {
                if (!this.from && !this.until) {
                    this.$emit('input', []);
                    return;
                }
                this.$emit('input', [this.from || '0001-01-01', this.until || '9999-12-31']);
            }
        },
        template:
            '<div class="tainacan-flexible-date-interval-filter">' +
                '<b-field grouped>' +
                    '<b-field><b-input placeholder="YYYY-MM-DD" v-model="from" @blur="emitChange"></b-input></b-field>' +
                    '<span style="align-self: center; margin: 0 0.5em">–</span>' +
                    '<b-field><b-input placeholder="YYYY-MM-DD" v-model="until" @blur="emitChange"></b-input></b-field>' +
                '</b-field>' +
            '</div>'
    };

    window.tainacan_extra_components['tainacan-filter-flexible-date-interval'] = TainacanFilterFlexibleDateInterval;
})();
