/**
 * Componente de exibição/edição do tipo "Flexible Date".
 *
 * Renderiza um input texto livre. A validação acontece no PHP (validate()),
 * então qualquer formato aceito pelo Normalizer passa. O placeholder
 * orienta o usuário sobre formatos esperados.
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
            onInput: function (event) {
                var v = event && event.target ? event.target.value : event;
                this.internalValue = v;
                this.$emit('input', v);
            },
            onBlur: function () {
                this.$emit('blur');
            }
        },
        template:
            '<input type="text" ' +
                ':disabled="disabled" ' +
                ':value="internalValue" ' +
                '@input="onInput" ' +
                '@blur="onBlur" ' +
                'class="input" ' +
                'placeholder="YYYY-MM-DD, YYYY-MM, YYYY ou intervalo YYYY-MM-DD/YYYY-MM-DD" ' +
                'autocomplete="off">'
    };

    window.tainacan_extra_components['tainacan-flexible-date'] = TainacanFlexibleDate;
})();
