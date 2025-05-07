<?php

namespace wsydney76\propertiesfield\models;

use craft\base\Model;
use function array_merge;

class Settings extends Model
{
    // Whether to show table header (Property/Value
    // deprecated
    public bool $showTableHeader = false;

    // The date format to use for normalized date outputs
    // Anything that Craft::$app->formatter->asDate() can use
    public string $dateFormat = 'short';

    // The view mode for entries/assets sub-fields (cards/list)
    public string $entriesViewMode = 'cards';
    public string $assetsViewMode = 'cards';

    // Whether to enable element query helpers, adds a slight overhead
    public bool $enableElementQueryHelpers = false;

    // Where to find input templates for custom property types
    public string $customInputTemplateDir = '';

    // Custom property types as defined in config/_properties-field.php
    public array $extraPropertyTypes = [];


    /**
     * Get merged property types (built-in and custom)
     *
     * @return array
     */
    public function getAllPropertyTypes()
    {
        return array_merge(Config::$propertyTypes, $this->extraPropertyTypes);
    }

}
