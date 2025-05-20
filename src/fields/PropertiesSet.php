<?php

namespace wsydney76\propertiesfield\fields;

use Craft;
use craft\fields\Table;
use craft\helpers\StringHelper;
use wsydney76\propertiesfield\models\Config;

class PropertiesSet extends Table
{


    public function init(): void
    {
        parent::init();

        if ($this->columns === []) {
            $this->columns = Config::getConfigTableColumns();
        }
    }

    public array $columns = [];

    public ?array $defaults = [];

    public ?string $addRowLabel = 'Add Property';

    public static function displayName(): string
    {
        return 'Properties Set';
    }

    public static function icon(): string
    {
        return 'settings';
    }

    public function getElementValidationRules(): array
    {
        return array_merge(
            parent::getElementValidationRules(),
            ['checkConfig']
        );
    }

    public function checkConfig($element)
    {
        $value = $element->getFieldValue($this->handle);
        $attribute = $this->handle;

        // TODO: Refactor.
        // This is the same logic as in Properties::checkConfig()
        // It's a bit tricky to get this to work on both field and element level,
        // so accept copy/paste for now.

        $handles = [];
        foreach ($value as $i => $fieldConfig) {

            // Name is required
            if (empty($fieldConfig['name'])) {
                $element->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Name cannot be blank.'));
            }
            {
                // generate handle from name if handle is empty
                if (empty($fieldConfig['handle'])) {
                    // If handle is empty, use name as handle
                    $fieldConfig['handle'] = StringHelper::toHandle($fieldConfig['name']);
                    $value[$i]['handle'] = $fieldConfig['handle'];
                }
            }

            // Handle is required, must be a valid handle and unique
            if (empty($fieldConfig['handle'])) {
                $element->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Handle cannot be blank.'));
            } elseif (!(bool)preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $fieldConfig['handle'])) {
                $element->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Is not a valid handle.'));
            } elseif (in_array($fieldConfig['handle'], $handles, true)) {
                $element->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Handle must be unique.'));
            } else {
                $handles[] = $fieldConfig['handle'];
            }

            // Field config must be a valid JSON string
            if (!empty($fieldConfig['fieldConfig'])) {
                // Check if fieldConfig is a valid JSON string
                $isValidJson = json_decode($fieldConfig['fieldConfig'], true);
                if ($isValidJson === null) {
                    $element->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Field Config must be a valid JSON string.'));
                }
            }

            $element->setFieldValue($this->handle, $value);
        }
    }
}