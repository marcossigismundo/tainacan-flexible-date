/**
 * Componente do filtro "Intervalo de Data Flexível".
 *
 * Renderiza dois inputs (de/até) e emite um par [start, end] com
 * compare = 'BETWEEN' que o backend (Query_Hook) traduz para uma
 * meta_query sobre as postmeta canônicas do Flexible_Date.
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
            },
            onInputFrom: function (e) {
                this.from = e.target.value;
                this.emitChange();
            },
            onInputUntil: function (e) {
                this.until = e.target.value;
                this.emitChange();
            }
        },
        template:
            '<div class="tainacan-flexible-date-interval-filter">' +
                '<div class="field is-grouped">' +
                    '<div class="control"><input class="input" type="text" placeholder="YYYY-MM-DD" :value="from" @input="onInputFrom" @blur="emitChange"></div>' +
                    '<span style="margin: 0 0.5em">–</span>' +
                    '<div class="control"><input class="input" type="text" placeholder="YYYY-MM-DD" :value="until" @input="onInputUntil" @blur="emitChange"></div>' +
                '</div>' +
            '</div>'
    };

    window.tainacan_extra_components['tainacan-filter-flexible-date-interval'] = TainacanFilterFlexibleDateInterval;
})();
