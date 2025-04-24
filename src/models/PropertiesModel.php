<?php

namespace wsydney76\propertiesfield\models;

use Craft;
use craft\base\Model;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\helpers\DateTimeHelper;
use wsydney76\propertiesfield\PropertiesFieldPlugin;
use yii\base\InvalidConfigException;

/**
 * The PropertiesModel class is used to represent the properties field model data.
 */
class PropertiesModel extends Model
{

    private Model $settings;

    // The properties field configuration as entered via the field settings
    public array $propertiesFieldConfig = [];

    // The properties field values for the current element/field
    public array $properties = [];

    /**
     * Store dates in ISO 8601 format
     * TODO: Check correct date handling in all situations???
     *
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        $this->settings = PropertiesFieldPlugin::getInstance()->getSettings();

        foreach ($config['propertiesFieldConfig'] as $propertyConfig) {
            if ($propertyConfig['type'] === 'date' && isset($config['properties'][$propertyConfig['handle']])) {
                $config['properties'][$propertyConfig['handle']] =
                    DateTimeHelper::toIso8601($config['properties'][$propertyConfig['handle']]);
            }
        }

        parent::__construct($config);
    }


    /**
     * Get the properties field values as an array of normalized values
     *
     * This returns an array of properties with the following structure:
     *  - name: The name of the property
     *  - handle: The handle of the property
     *  - value: The raw value of the property, a default is returned if the property is not in the database
     *  - type: The type of the property
     *  - normalizedValue: The normalized value of the property, based on the type
     *    - type = date: The value is formatted as a date string
     *    - type = entry/asset: The value is an Entry/Asset object
     *    - type = entries/assets: The value is an array of Entry/Asset objects
     *
     * @param array $props
     * @return array
     * @throws InvalidConfigException
     */
    public function getNormalizedProperties(array $props = []): array
    {
        $props = array_merge([
            'ignoreEmpty' => false,
            'default' => null,
        ], $props);


        $data = [];

        foreach ($this->propertiesFieldConfig as $propertyConfig) {

            // Newly added properties not in database
            if (!isset($this->properties[$propertyConfig['handle']])) {
                $this->properties[$propertyConfig['handle']] = $props['default'];
            }

            $value = $this->properties[$propertyConfig['handle']] ?: $props['default'];

            if ($props['ignoreEmpty'] && empty($value)) {
                continue;
            }


            $data[$propertyConfig['handle']] = [
                'name' => $propertyConfig['name'],
                'handle' => $propertyConfig['handle'],
                'value' => $value,
                'type' => $propertyConfig['type'],
                'normalizedValue' => $this->getNormalizedValue($propertyConfig['type'], $value),
            ];
        }

        return $data;
    }

    /**
     * Get the raw value of a property
     *
     * @param array $props
     * @return array
     */
    public function get($handle): mixed
    {
        return $this->properties[$handle] ?? null;
    }

    /**
     * Get the normalized value of a property, depending on the type
     *
     * TODO: Find a better way to handle this, also for custom property types
     *
     * @param $type
     * @param mixed $value
     * @return array|\craft\base\ElementInterface[]|Asset|Entry|mixed|string|null
     * @throws \yii\base\InvalidConfigException
     */
    private function getNormalizedValue($type, mixed $value): mixed
    {
        $callback = $this->settings[$type]['normalize'] ?? null;

        if ($callback) {
            return call_user_func($callback, $value);
        }

        return $value;
    }

    /**
     * Get the normalized value of a property by handle
     *
     *
     * @throws InvalidConfigException
     */
    public function getNormalized(string $handle): mixed
    {
        return $this->getNormalizedValue($this->getTypeByHandle($handle), $this->get($handle));
    }

    /**
     * Get the type of property by handle
     *
     * @param string $handle
     * @return string
     */
    private function getTypeByHandle(string $handle): string
    {
        foreach ($this->propertiesFieldConfig as $propertyConfig) {
            if ($propertyConfig['handle'] === $handle) {
                return $propertyConfig['type'];
            }
        }

        return 'text';
    }


    public function normalizeEntry($value): ?Entry
    {
        return $value ? Entry::findOne($value): null;
    }
    public function normalizeEntries($value): array
    {
        return $value ? Entry::find()->id($value)->all() : [];
    }

    public function normalizeAsset($value): ?Asset
    {
        return $value ? Asset::findOne($value): null;
    }
    public function normalizeAssets($value): array
    {
        return $value ? Asset::find()->id($value)->all() : [];
    }

    public function normalizeDate($value): string
    {
        return $value ? Craft::$app->getFormatter()->asDate($value, PropertiesFieldPlugin::getInstance()->getSettings()->dateFormat) : '';
    }

    public function normalizeExtendedBoolean($value): string
    {
        $string = $value['isOn'] ? Craft::t('_properties-field', 'Yes') : Craft::t('_properties-field', 'No');

        if ($value['comment']) {
            $string .= ' (' . $value['comment'] . ')';
        }

        return $string;
    }

    public function normalizeDimension($value): string
    {
        if (!$value || !$value['quantity']) {
            return '';
        }
        $string = $value['quantity'] ?? '';
        if ($value['unit']) {
            $string .= ' ' . $value['unit'];
        }

        return $string;
    }

}
