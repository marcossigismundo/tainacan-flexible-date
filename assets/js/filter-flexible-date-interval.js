/**
 * Componente do filtro "Intervalo de Data Flexível".
 *
 * Aceita qualquer formato suportado pelo Normalizer (YYYY, YYYY-MM,
 * YYYY-MM-DD, DD/MM/YYYY etc.). O backend (Query_Hook) chama o mesmo
 * Normalizer do tipo de metadado para canonicalizar o valor antes de
 * montar a meta_query, então a paridade de formatos é total.
 *
 * Quando o usuário digita um formato parcial em "de", o backend resolve
 * para o início do intervalo natural (YYYY → YYYY-01-01); em "até",
 * para o fim (YYYY → YYYY-12-31). Assim, "de: 2020 / até: 2022"
 * cobre todo o período de 1º jan 2020 a 31 dez 2022.
 */
(function () {
    'use strict';

    window.tainacan_extra_components =
        typeof window.tainacan_extra_components !== 'undefined'
            ? window.tainacan_extra_components
            : {};

    var PLACEHOLDER = 'YYYY, YYYY-MM, YYYY-MM-DD ou DD/MM/YYYY';

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
                var from = (this.from || '').trim();
                var until = (this.until || '').trim();
                if (!from && !until) {
                    this.$emit('input', []);
                    return;
                }
                // Sentinelas amplos quando um dos lados está vazio.
                // O backend ainda canonicaliza valores parciais via Normalizer.
                this.$emit('input', [from || '0001-01-01', until || '9999-12-31']);
            }
        },
        template:
            '<div class="tainacan-flexible-date-interval-filter">' +
                '<b-field grouped>' +
                    '<b-field expanded :label="\'De\'">' +
                        '<b-input :placeholder="\'' + PLACEHOLDER + '\'" v-model="from" @blur="emitChange"></b-input>' +
                    '</b-field>' +
                    '<b-field expanded :label="\'Até\'">' +
                        '<b-input :placeholder="\'' + PLACEHOLDER + '\'" v-model="until" @blur="emitChange"></b-input>' +
                    '</b-field>' +
                '</b-field>' +
                '<p class="help" style="margin-top: -0.5em">' +
                    'Aceita ano (2020), ano-mês (2020-06), data completa ou formato BR. ' +
                    'Em "De", um ano vira o primeiro dia; em "Até", o último dia.' +
                '</p>' +
            '</div>'
    };

    window.tainacan_extra_components['tainacan-filter-flexible-date-interval'] = TainacanFilterFlexibleDateInterval;
})();
