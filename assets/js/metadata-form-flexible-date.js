/**
 * Form-component do tipo "Flexible Date" — renderiza as opções de
 * configuração do metadado (allow_partial, allow_range) na tela de
 * edição do metadatum no admin do Tainacan.
 *
 * Registrado via hook `tainacan-register-vuejs-component` conforme
 * documentação oficial.
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
            '<div class="field">' +
                '<div class="field">' +
                    '<label class="label">Permitir datas parciais (YYYY ou YYYY-MM)</label>' +
                    '<div class="control">' +
                        '<label class="radio"><input type="radio" :disabled="disabled" v-model="allow_partial" value="yes"> Sim</label> ' +
                        '<label class="radio"><input type="radio" :disabled="disabled" v-model="allow_partial" value="no"> Não</label>' +
                    '</div>' +
                '</div>' +
                '<div class="field">' +
                    '<label class="label">Permitir intervalos (YYYY-MM-DD/YYYY-MM-DD)</label>' +
                    '<div class="control">' +
                        '<label class="radio"><input type="radio" :disabled="disabled" v-model="allow_range" value="yes"> Sim</label> ' +
                        '<label class="radio"><input type="radio" :disabled="disabled" v-model="allow_range" value="no"> Não</label>' +
                    '</div>' +
                '</div>' +
            '</div>'
    };

    window.tainacan_extra_components['tainacan-form-flexible-date'] = TainacanFormFlexibleDate;
})();
