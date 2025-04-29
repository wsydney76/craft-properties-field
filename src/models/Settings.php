<?php

namespace wsydney76\propertiesfield\models;

use craft\base\Model;
use wsydney76\propertiesfield\fields\Properties;
use function array_merge;

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
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'textarea' => [
            'label' => 'Text Area',
            'type' => 'textarea',
            'template' => '_properties-field/_inputs/textarea.twig',
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'number' => [
            'label' => 'Number',
            'type' => 'number',
            'template' => '_properties-field/_inputs/text.twig',
            'validate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateNumber'],
            ],
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'template' => '_properties-field/_inputs/text.twig',
            'validate' => [
                [Properties::class, 'validateRequired'],
                [Properties::class, 'validateEmail'],
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
            'normalize' => [PropertiesModel::class, 'normalizeDate'],
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'select' => [
            'label' => 'Select',
            'type' => 'select',
            'template' => '_properties-field/_inputs/select.twig', 7,
            'normalize' => [PropertiesModel::class, 'normalizeSelect'],
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'entry' => [
            'label' => 'Entry (Single)',
            'type' => 'entry',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeEntry'],
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'entrySelect' => [
            'label' => 'Entry Select',
            'type' => 'entrySelect',
            'template' => '_properties-field/_inputs/entrySelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeEntry'],
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'entries' => [
            'label' => 'Entry (Multi)',
            'type' => 'entries',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeEntries'],
            'validate' => [[Properties::class, 'validateRequired']],
            ],
        'asset' => [
            'label' => 'Asset (Single)',
            'type' => 'asset',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeAsset'],
            'validate' => [[Properties::class, 'validateRequired']],
        ],
        'assets' => [
            'label' => 'Asset (Multi)',
            'type' => 'assets',
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'normalize' => [PropertiesModel::class, 'normalizeAssets'],
            'validate' => [[Properties::class, 'validateRequired']],
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
            'normalize' => [PropertiesModel::class, 'normalizeExtendedBoolean'],
            'validate' => [[Properties::class, 'validateExtendedBoolean']],
            ],
        'dimension' => [
            'label' => 'Dimension',
            'type' => 'dimension',
            'template' => '_properties-field/_inputs/dimension.twig',
            'normalize' => [PropertiesModel::class, 'normalizeDimension'],
            'validate' => [[Properties::class, 'validateDimension']],
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


    public string $customInputTemplateDir = '';
    public array $extraPropertiesConfig = [];

    public function getAllPropertiesConfig()
    {
        return array_merge($this->propertiesConfig, $this->extraPropertiesConfig);
    }

}
