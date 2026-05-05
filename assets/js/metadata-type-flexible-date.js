/**
 * Componente de exibição/edição do tipo "Flexible Date".
 *
 * Usa <b-input> do Buefy para herdar o visual do admin do Tainacan.
 * A validação acontece no PHP (validate()), então qualquer formato
 * aceito pelo Normalizer passa. O placeholder orienta o usuário.
 *
 * Padrão de registro segue a documentação oficial do Tainacan:
 * https://tainacan.github.io/tainacan-wiki/#/dev/creating-metadata-type
 */
(function () {
    'use strict';

    window.tainacan_extra_components =
        typeof window.tainacan_extra_components !== 'undefined'
            ? window.tainacan_extra_components
            : {};

    var TainacanFlexibleDate = {
        name: 'TainacanFlexibleDate',
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: { type: Boolean, default: false }
        },
        data: function () {
            return {
                internalValue: this.value || ''
            };
        },
        watch: {
            value: function (newVal) {
                this.internalValue = newVal || '';
            }
        },
        methods: {
            onInput: function (value) {
                this.internalValue = value;
                this.$emit('input', value);
            },
            onBlur: function () {
                this.$emit('blur');
            }
        },
        template:
            '<b-input ' +
                ':disabled="disabled" ' +
                ':value="internalValue" ' +
                '@input="onInput" ' +
                '@blur="onBlur" ' +
                'placeholder="YYYY-MM-DD, YYYY-MM, YYYY ou intervalo YYYY-MM-DD/YYYY-MM-DD" ' +
                'autocomplete="off"></b-input>'
    };

    window.tainacan_extra_components['tainacan-flexible-date'] = TainacanFlexibleDate;
})();
