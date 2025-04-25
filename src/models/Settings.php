<?php

namespace wsydney76\propertiesfield\models;

use craft\base\Model;

class Settings extends Model
{
    // Whether to show table header (Property/Value
    public bool $showTableHeader = false;

    // The date format to use for normalized date outputs
    // Anything that Craft::$app->formatter->asDate() can use
    public string $dateFormat = 'short';

    // The view mode for entries/assets sub-fields (cards/list)
    public string $entriesViewMode = 'cards';
    public string $assetsViewMode = 'cards';

    public array $propertiesConfig = [
       'text' => [
            'label' => 'Text',
            'type' => 'text',
            'template' => '_properties-field/_inputs/text.twig',
        ],
        'textarea' => [
            'label' => 'Text Area',
            'type' => 'textarea',
            'template' => '_properties-field/_inputs/textarea.twig',
        ],
        'number' => [
            'label' => 'Number',
            'type' => 'number',
            'template' => '_properties-field/_inputs/text.twig',
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'template' => '_properties-field/_inputs/text.twig',
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
            'normalize' => [PropertiesModel::class, 'normalizeDate'],
        ],
        'select' => [
            'label' => 'Select',
            'type' => 'select',
            'template' => '_properties-field/_inputs/select.twig',
        ],
        'entry' => [
            'label' => 'Entry (Single)',
            'type' => 'entry',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeEntry'],
        ],
        'entries' => [
            'label' => 'Entry (Multi)',
            'type' => 'entries',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeEntries'],
        ],
        'asset' => [
            'label' => 'Asset (Single)',
            'type' => 'asset',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeAsset'],
        ],
        'assets' => [
            'label' => 'Asset (Multi)',
            'type' => 'assets',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeAssets'],
        ],
        'extendedBoolean' => [
            'label' => 'Boolean with comment',
            'type' => 'extendedBoolean',
            'template' => '_properties-field/_inputs/extendedBoolean.twig',
            'normalize' => [PropertiesModel::class, 'normalizeExtendedBoolean'],
        ],
        'dimension' => [
            'label' => 'Dimension',
            'type' => 'dimension',
            'template' => '_properties-field/_inputs/dimension.twig',
            'normalize' => [PropertiesModel::class, 'normalizeDimension'],
        ],
        'groupHeader' => [
            'label' => 'Group Header',
            'type' => 'groupHeader'
        ],
    ];


    public string $customInputTemplateDir = '';
    public array $extraPropertiesConfig = [];
    
}
