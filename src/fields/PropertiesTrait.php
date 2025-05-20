<?php

namespace wsydney76\propertiesfield\fields;

use Craft;
use craft\helpers\StringHelper;

trait PropertiesTrait
{
    /**
     * Validiert die Konfiguration von Eigenschaften.
     *
     * @param array $config
     * @param callable $addErrorCallback
     * @return array Die aktualisierte Konfiguration
     */
    public function validateConfig(array $config, callable $addErrorCallback): array
    {
        $handles = [];
        foreach ($config as $i => $fieldConfig) {
            // Name ist erforderlich
            if (empty($fieldConfig['name'])) {
                $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Name cannot be blank.'));
            } else {
                // Handle aus Name generieren, falls leer
                if (empty($fieldConfig['handle'])) {
                    $fieldConfig['handle'] = StringHelper::toHandle($fieldConfig['name']);
                    $config[$i]['handle'] = $fieldConfig['handle'];
                }
            }

            // Handle ist erforderlich, muss gültig und eindeutig sein
            if (empty($fieldConfig['handle'])) {
                $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Handle cannot be blank.'));
            } elseif (!(bool)preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $fieldConfig['handle'])) {
                $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Is not a valid handle.'));
            } elseif (in_array($fieldConfig['handle'], $handles, true)) {
                $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Handle must be unique.'));
            } else {
                $handles[] = $fieldConfig['handle'];
            }

            // FieldConfig muss ein gültiger JSON-String sein
            if (!empty($fieldConfig['fieldConfig'])) {
                $isValidJson = json_decode($fieldConfig['fieldConfig'], true);
                if ($isValidJson === null) {
                    $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Field Config must be a valid JSON string.'));
                }
            }
        }

        return $config;
    }
}
