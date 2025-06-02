<?php

namespace wsydney76\propertiesfield\models;

use Craft;
use craft\base\Model;
use phpDocumentor\Reflection\Types\Boolean;
use function array_merge;

class Settings extends Model
{
    // Whether to show table header (Property/Value
    // deprecated
    public bool $showTableHeader = false;

    // The date format to use for normalized date outputs
    // Anything that Craft::$app->formatter->asDate() can use
    public string $dateFormat = 'short';

    // The datetime format to use for normalized date time outputs
    // Anything that Craft::$app->formatter->asDatetime() can use
    public string $dateTimeFormat = 'short';

    // The view mode for entries/assets sub-fields (cards/list)
    public string $entriesViewMode = 'cards';
    public string $assetsViewMode = 'cards';

    public string $currency = 'EUR';

    // Whether to enable element query helpers, adds a slight overhead
    public bool $enableElementQueryHelpers = false;

    // Where to find input templates for custom property types
    public string $customTemplateDir = '';

    // Whether to enable dynamic properties
    public bool $enableDynamicProperties = false;

    // The field name that holds the dynamic properties
    public string $dynamicPropertiesFieldHandle = 'propertiesSet';

    // Custom property types as defined in config/_properties-field.php
    public array $extraPropertyTypes = [];


    /**
     * Get merged property types (built-in and custom)
     *
     * @return array
     */
    public function getAllPropertyTypes()
    {
        $types = array_merge(Config::$propertyTypes, $this->extraPropertyTypes);;
        return array_filter($types, function($type) {
            if (isset($type['requiresPlugin'])) {
                return Craft::$app->plugins->isPluginEnabled($type['requiresPlugin']);
            }
            return true;
        });
    }

}
