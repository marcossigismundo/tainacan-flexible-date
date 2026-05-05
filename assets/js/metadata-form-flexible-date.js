/**
 * Form-component do tipo "Flexible Date" — usa Buefy (b-field, b-radio)
 * para harmonizar com o admin do Tainacan. Renderiza as opções
 * allow_partial e allow_range na tela de edição do metadatum.
 *
 * Registrado via hook `tainacan-register-vuejs-component`.
 */
(function () {
    'use strict';

    window.tainacan_extra_components =
        typeof window.tainacan_extra_components !== 'undefined'
            ? window.tainacan_extra_components
            : {};

    var TainacanFormFlexibleDate = {
        name: 'TainacanFormFlexibleDate',
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array, Object],
            disabled: { type: Boolean, default: false }
        },
        data: function () {
            var opts = (this.itemMetadatum && this.itemMetadatum.metadata_type_options) || {};
            return {
                allow_partial: opts.allow_partial || 'yes',
                allow_range: opts.allow_range || 'yes'
            };
        },
        watch: {
            allow_partial: function () { this.emit(); },
            allow_range: function () { this.emit(); }
        },
        methods: {
            emit: function () {
                this.$emit('input', {
                    allow_partial: this.allow_partial,
                    allow_range: this.allow_range
                });
            }
        },
        template:
            '<div>' +
                '<b-field label="Permitir datas parciais (YYYY ou YYYY-MM)">' +
                    '<b-radio :disabled="disabled" v-model="allow_partial" native-value="yes">Sim</b-radio>' +
                    '<b-radio :disabled="disabled" v-model="allow_partial" native-value="no">Não</b-radio>' +
                '</b-field>' +
                '<b-field label="Permitir intervalos (YYYY-MM-DD/YYYY-MM-DD)">' +
                    '<b-radio :disabled="disabled" v-model="allow_range" native-value="yes">Sim</b-radio>' +
                    '<b-radio :disabled="disabled" v-model="allow_range" native-value="no">Não</b-radio>' +
                '</b-field>' +
            '</div>'
    };

    window.tainacan_extra_components['tainacan-form-flexible-date'] = TainacanFormFlexibleDate;
})();
