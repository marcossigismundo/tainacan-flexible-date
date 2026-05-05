# Tainacan Flexible Date

Tipo de metadado para o [Tainacan](https://tainacan.org) que aceita formatos flexíveis de data, mantendo compatibilidade com filtros nativos do Tainacan.

## Por quê

O tipo "Date" nativo do Tainacan aceita estritamente `YYYY-MM-DD`. Acervos arquivísticos costumam ter datas parciais (`2020`, `2020-06`), intervalos (`2020-06-08/2020-06-09`), formatos brasileiros (`08/06/2020`) e marcadores como `s.d.`. Este plugin acrescenta um tipo "Data flexível" que aceita tudo isso e ainda permite filtragem por intervalo na busca pública.

## Formatos aceitos

| Formato | Exemplo | Precisão interna |
|---|---|---|
| ISO completo | `2020-06-08` | dia |
| ISO mês | `2020-06` | mês |
| ISO ano | `2020` | ano |
| Intervalo | `2020-06-08/2020-06-09` | range |
| Brasileiro | `08/06/2020`, `08-06-2020` | dia |
| Brasileiro mês/ano | `06/2020` | mês |
| ISO com hora | `2020-06-08T14:30:00Z` | dia (hora descartada) |
| Sem data | `s.d.`, `s/d`, `[s.d.]`, `n/d` | guardado como texto |

Qualquer outra string é guardada como texto cru e exibida como veio.

## Como funciona internamente

Para cada valor gravado, o plugin armazena três postmeta:

- meta_key padrão do Tainacan (= `metadatum_id`) → string crua original
- `_tfd_<metadatum_id>_start` → data canônica `YYYY-MM-DD` inicial
- `_tfd_<metadatum_id>_end` → data canônica `YYYY-MM-DD` final

O filtro "Intervalo de Data Flexível" e a reescrita em `tainacan-api-prepare-items-args` redirecionam queries SQL para as keys canônicas, então a busca por intervalo funciona mesmo quando o valor visível é parcial ou intervalo.

## Instalação

1. Copie a pasta para `wp-content/plugins/`
2. Ative em **Plugins → Instalados** (Tainacan precisa estar ativo)
3. Em uma coleção Tainacan, ao criar um metadado, selecione **Data flexível** como tipo
4. Para habilitar filtro de busca, adicione um filtro no metadado escolhendo **Intervalo de data flexível**

## Requisitos

- WordPress 6.0+
- PHP 8.0+
- Tainacan instalado e ativo

## Estrutura

```
tainacan-flexible-date/
├── tainacan-flexible-date.php          Bootstrap
├── uninstall.php                        Limpeza
├── includes/
│   ├── class-plugin.php                 Bootstrap (registra hooks)
│   ├── class-flexible-date.php          Tipo de metadado
│   ├── class-flexible-date-interval.php Tipo de filtro
│   ├── class-normalizer.php             Conversão de formatos
│   ├── class-storage.php                Postmeta canônicas
│   └── class-query-hook.php             Reescrita de meta_query
└── assets/js/
    ├── metadata-type-flexible-date.js   Vue: input do metadado
    ├── metadata-form-flexible-date.js   Vue: opções do tipo
    └── filter-flexible-date-interval.js Vue: filtro de busca
```

## Conformidade com a documentação Tainacan

Implementação baseada em:

- [Creating Metadata Type](https://tainacan.github.io/tainacan-wiki/#/dev/creating-metadata-type) — hooks `tainacan-register-metadata-type` e `tainacan-register-vuejs-component`, props obrigatórias `itemMetadatum`/`value`/`disabled`, registro em `window.tainacan_extra_components`
- [Creating Filter Type](https://tainacan.github.io/tainacan-wiki/#/dev/creating-filters-type) (página marcada TODO na wiki) — segue o mesmo padrão estrutural, com hook `tainacan-register-filter-type` e classe estendendo `\Tainacan\Filter_Types\Filter_Type`

## Licença

GPL-3.0+
