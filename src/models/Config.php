<?php

namespace wsydney76\propertiesfield\models;

use wsydney76\propertiesfield\fields\Properties;

// Using a separate class here to avoid conflicts with plugin settings/project config

class Config
{
    public static array $propertyTypes = [
        'text' => [
            'label' => 'Text',
            'type' => 'text',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'textarea' => [
            'label' => 'Text Area',
            'type' => 'textarea',
            'template' => '_properties-field/_inputs/textarea.twig',
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'number' => [
            'label' => 'Number',
            'type' => 'number',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateNumber'],
            ],
        ],
        'range' => [
            'label' => 'Range',
            'type' => 'range',
            'template' => '_properties-field/_inputs/range.twig',
            'onValidate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateNumber'],
            ],
        ],
        'money' => [
            'label' => 'Money',
            'type' => 'money',
            'template' => '_properties-field/_inputs/money.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeMoney'],
            'onValidate' => [
                [Properties::class, 'validateRequired'],
                // [Properties::class, 'validateMoney'],
            ],
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateEmail'],
            ],
        ],
        'url' => [
            'label' => 'URL',
            'type' => 'url',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateUrl'],
            ],
        ],
        'boolean' => [
            'label' => 'Boolean',
            'type' => 'boolean',
            'template' => '_properties-field/_inputs/boolean.twig',
        ],
        'date' => [
            'label' => 'Date',
            'type' => 'date',
            'template' => '_properties-field/_inputs/date.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeDate'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'datetime' => [
            'label' => 'Date Time',
            'type' => 'datetime',
            'template' => '_properties-field/_inputs/datetime.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeDateTime'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'select' => [
            'label' => 'Select',
            'type' => 'select',
            'template' => '_properties-field/_inputs/select.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeSelect'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'radio' => [
            'label' => 'Radio Buttons',
            'type' => 'radio',
            'template' => '_properties-field/_inputs/radio.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeSelect'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'checkboxes' => [
            'label' => 'Checkboxes',
            'type' => 'checkboxes',
            'template' => '_properties-field/_inputs/checkboxes.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeMultiSelect'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'multiselect' => [
            'label' => 'Multiselect',
            'type' => 'multiselect',
            'template' => '_properties-field/_inputs/multiselect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeMultiSelect'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'entry' => [
            'label' => 'Entry (Single)',
            'type' => 'entry',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeEntry'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'entrySelect' => [
            'label' => 'Entry Select (Single)',
            'type' => 'entrySelect',
            'template' => '_properties-field/_inputs/entrySelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeEntry'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'entries' => [
            'label' => 'Entry (Multi)',
            'type' => 'entries',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeEntries'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'entriesSelect' => [
            'label' => 'Entries Select (Multi)',
            'type' => 'entriesSelect',
            'template' => '_properties-field/_inputs/entriesSelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeEntries'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'asset' => [
            'label' => 'Asset (Single)',
            'type' => 'asset',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeAsset'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'country' => [
            'label' => 'Country',
            'type' => 'country',
            'template' => '_properties-field/_inputs/country.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeCountry'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'assets' => [
            'label' => 'Asset (Multi)',
            'type' => 'assets',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeAssets'],
            'onValidate' => [[Properties::class, 'validateRequired']],
        ],
        'table' => [
            'label' => 'Table',
            'type' => 'table',
            'template' => '_properties-field/_inputs/table.twig',
        ],
        'extendedBoolean' => [
            'label' => 'Boolean with comment',
            'type' => 'extendedBoolean',
            'template' => '_properties-field/_inputs/extendedBoolean.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeExtendedBoolean'],
            'onValidate' => [[Properties::class, 'validateExtendedBoolean']],
        ],
        'dimension' => [
            'label' => 'Dimension',
            'type' => 'dimension',
            'template' => '_properties-field/_inputs/dimension.twig',
            'onNormalize' => [PropertiesModel::class, 'normalizeDimension'],
            'onValidate' => [[Properties::class, 'validateDimension']],
        ],
        'groupHeader' => [
            'label' => 'Group Header',
            'type' => 'groupHeader'
        ],
        'set' => [
            'label' => 'Dynamic Property Set',
            'type' => 'set'
        ],
    ];
}