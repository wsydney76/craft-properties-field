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
}
