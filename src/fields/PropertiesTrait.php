<?php

namespace wsydney76\propertiesfield\fields;

use Craft;
use craft\helpers\StringHelper;
use wsydney76\propertiesfield\models\Config;
use wsydney76\propertiesfield\PropertiesFieldPlugin;

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

            if (in_array($fieldConfig['type'], ['entry', 'entries', 'entrySelect', 'asset', 'assets'], true)) {
                if (empty($fieldConfig['options'])) {
                    $addErrorCallback($i + 1 . ': ' . Craft::t('_properties-field', 'Options are required for this type.'));
                }
            }
        }

        return $config;
    }

    /**
     * Table input columns for properties config
     *
     * @return array
     */
    public function getConfigTableColumns(): array
    {
        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        $propertyTypes = PropertiesFieldPlugin::getInstance()->getSettings()->getAllPropertyTypes();

        $options = [];

        foreach ($propertyTypes as $option) {
            $options[] = [
                'label' => $option['label'],
                'value' => $option['type'],
            ];
        }


        return [
            'name' => [
                'heading' => Craft::t('_properties-field', 'Name'),
                'handle' => 'name',
                'type' => 'singleline',
            ],
            'handle' => [
                'heading' => Craft::t('_properties-field', 'Handle'),
                'handle' => 'handle',
                'type' => 'singleline',
                'class' => 'code',
            ],
            'instructions' => [
                'heading' => Craft::t('_properties-field', 'Instructions'),
                'handle' => 'instructions',
                'type' => 'singleline',
            ],
            'required' => [
                'heading' => Craft::t('_properties-field', 'Required'),
                'handle' => 'required',
                'type' => 'lightswitch',
            ],
            'searchable' => [
                'heading' => Craft::t('_properties-field', 'Searchable'),
                'handle' => 'required',
                'type' => 'lightswitch',
            ],
            'type' => [
                'heading' => Craft::t('_properties-field', 'Type'),
                'handle' => 'type',
                'type' => 'select',
                'options' => $options,
                'width' => '10%',
            ],
            'options' => [
                'heading' => Craft::t('_properties-field', 'Options'),
                'handle' => 'propertyTypes',
                'type' => 'multiline',
            ],
            'fieldConfig' => [
                'heading' => Craft::t('_properties-field', 'Field Config'),
                'handle' => 'fieldConfig',
                'type' => 'multiline',
            ],
        ];
    }
}
