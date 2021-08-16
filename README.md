[![Build Status](https://travis-ci.org/menatwork/contao-multicolumnwizard-bundle.png)](https://travis-ci.org/menatwork/contao-multicolumnwizard-bundle)
[![Latest Version tagged](http://img.shields.io/github/tag/menatwork/contao-multicolumnwizard-bundle.svg)](https://github.com/menatwork/contao-multicolumnwizard-bundle/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/menatwork/contao-multicolumnwizard-bundle.svg)](https://packagist.org/packages/menatwork/contao-multicolumnwizard-bundle)
[![Installations via composer per month](http://img.shields.io/packagist/dm/menatwork/contao-multicolumnwizard-bundle.svg)](https://packagist.org/packages/menatwork/contao-multicolumnwizard-bundle)

# ❗This repository will not be continued
You can find the current version here: https://github.com/contao-community-alliance/contao-multicolumnwizard-bundle

# MultiColumnWizard

The MultiColumnWizard is a widget for mapping several fields of the same and/or different type (input type) in a DCA element. The individual fields of the MCW are listed column by column in the backend and can be extended row by row as a group. The arrangement corresponds to a multidimensional array of the form array[rows][fields], which is stored in the database as a serialized array. The widget is almost identical to MultiTextWizard or MultiSelectWizard. It extends the functionality of any widget.

More information can be found in the contao wiki
http://de.contaowiki.org/MultiColumnWizard

## Install

The Multicolumnwizard is usually installed via an extension. If it is necessary to install Multicolumnwizard yourself, please use the console with the composer via the call

`composer require menatwork/contao-multicolumnwizard-bundle`

or

`web/contao-manager.phar.php composer require menatwork/contao-multicolumnwizard-bundle`

Developers should add the Multicolumnwizard to their `composer.json` as a dependent package.

## Usages

### Usage with columnFields

```php
<?php

$GLOBALS['TL_DCA']['tl_theme']['fields']['templateSelection'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_theme']['templateSelection'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'columnFields' => [
            'ts_client_os'      => [
                'label'     => &$GLOBALS['TL_LANG']['tl_theme']['ts_client_os'],
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => [
                    'style'              => 'width:250px',
                    'includeBlankOption' => true,
                ],
                'options'   => [
                    'option1' => 'Option 1',
                    'option2' => 'Option 2',
                ],
            ],
            'ts_client_browser' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_theme']['ts_client_browser'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [ 'style' => 'width:180px' ],
            ],
        ],
    ],
    'sql'       => 'blob NULL',
];

?>
```


### Usage with callback

```php
<?php

$GLOBALS['TL_DCA']['tl_table']['fields']['anything'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_table']['anything'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'mandatory'       => true,
        'columnsCallback' => [ 'Class', 'Method' ],
    ],
    'sql'       => 'blob NULL',
];

?>
```


### Disable Drag and Drop

```php
<?php

$GLOBALS['TL_DCA']['tl_theme']['fields']['templateSelection'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_theme']['templateSelection'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        // add this line for use the up and down arrows
        'dragAndDrop'  => false,
        'columnFields' => [
            'ts_client_browser' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_theme']['ts_client_browser'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [ 'style' => 'width:180px' ],
            ],
        ],
    ],
    'sql'       => 'blob NULL',
];

?>
```

### Hide buttons

```php
<?php

$GLOBALS['TL_DCA']['tl_theme']['fields']['templateSelection'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_theme']['templateSelection'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        // add this line for hide one or all buttons
        'buttons'      =>
        [
            'new'    => false,
            'copy'   => false,
            'delete' => false,
            'up'     => false,
            'down'   => false
        ],
        // as alternative to hide all buttons use the next line
        //'hideButtons'  => true,
        'columnFields' => [
            'ts_client_browser' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_theme']['ts_client_browser'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [ 'style' => 'width:180px' ],
            ],
        ],
    ],
    'sql'       => 'blob NULL',
];

?>
```
