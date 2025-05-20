<?php

namespace wsydney76\propertiesfield\fields\traits;

use Craft;
use craft\helpers\StringHelper;
use wsydney76\propertiesfield\models\Config;
use wsydney76\propertiesfield\PropertiesFieldPlugin;

trait PropertiesTrait

{
    public function getDefaultColumns()
    {
        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        $options = array_merge(Config::$propertyTypes, $settings->extraPropertyTypes);

        $optionValues = [];

        foreach ($options as $option) {
            $optionValues[] = [
                'label' => $option['label'],
                'value' => $option['type'],
            ];
        }


        return [
            'name' => [
                'heading' => 'Name',
                'handle' => 'name',
                'type' => 'singleline',
            ],
            'handle' => [
                'heading' => 'Handle',
                'handle' => 'handle',
                'type' => 'singleline',
                'class' => 'code',
            ],
            'instructions' => [
                'heading' => 'Instructions',
                'handle' => 'instructions',
                'type' => 'singleline',
            ],
            'required' => [
                'heading' => 'Required',
                'handle' => 'required',
                'type' => 'lightswitch',
            ],
            'searchable' => [
                'heading' => 'Searchable',
                'handle' => 'required',
                'type' => 'lightswitch',
            ],
            'type' => [
                'heading' => 'Type',
                'handle' => 'type',
                'type' => 'select',
                'options' => $optionValues,
                'width' => '10%',
            ],
            'options' => [
                'heading' => 'Options',
                'handle' => 'options',
                'type' => 'multiline',
            ],
            'fieldConfig    ' => [
                'heading' => 'Field Config',
                'handle' => 'fieldConfig',
                'type' => 'multiline',
            ],
        ];
    }
}

