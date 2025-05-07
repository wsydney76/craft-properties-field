<?php

namespace wsydney76\propertiesfield\models;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\data\JsonData;
use craft\fields\data\SingleOptionFieldData;
use craft\helpers\DateTimeHelper;
use Exception;
use wsydney76\propertiesfield\fields\Properties;
use wsydney76\propertiesfield\PropertiesFieldPlugin;
use yii\base\InvalidConfigException;
use function is_string;
use function reset;

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

    public ElementInterface $element;
    public Properties $field;

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
     *  - normalizedValue: The normalized value of the property, based on the type, e.g.
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
            'ignoreMissing' => false,
            'default' => null,
        ], $props);


        $data = [];

        foreach ($this->propertiesFieldConfig as $propertyConfig) {

            if ($props['ignoreMissing'] && !isset($this->properties[$propertyConfig['handle']])) {
                continue;
            }

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
                'normalizedValue' => $this->getNormalizedValue($propertyConfig, $value),
                // 'fieldConfig' => $propertyConfig['fieldConfig'],
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
     *
     * @param $type
     * @param mixed $value
     * @return array|\craft\base\ElementInterface[]|Asset|Entry|mixed|string|null
     * @throws \yii\base\InvalidConfigException
     */
    private function getNormalizedValue($config, mixed $value): mixed
    {
        $callback = $this->settings->getAllPropertyTypes()[$config['type']]['onNormalize'] ?? null;

        if ($callback) {
            return call_user_func($callback, $value, $config);
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
        return $this->getNormalizedValue($this->getPropertyConfigByHandle($handle), $this->get($handle));
    }

    /**
     * Get the type of property by handle
     *
     * @param string $handle
     * @return string
     */
    private function getPropertyConfigByHandle(string $handle): mixed
    {
        foreach ($this->propertiesFieldConfig as $propertyConfig) {
            if ($propertyConfig['handle'] === $handle) {
                return $propertyConfig;
            }
        }

        return null;
    }


    /**
     * Get entry element (or null)
     *
     * @param $value
     * @return Entry|null
     */
    public function normalizeEntry($value): ?Entry
    {
        try {
            return $value ? Entry::findOne($value) : null;
        } catch (Exception $e) {
            return null; // This can happen if a default value is set for empty values in getNormalizedProperties()
        }
    }

    /**
     * Get array of entry elements (or empty array)
     *
     * @param $value
     * @return mixed
     */
    public function normalizeEntries($value): mixed
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        return $value ? Entry::find()->id($value)->all() : [];
    }

    /**
     *  Get asset element (or null)
     *
     * @param $value
     * @return Asset|null
     */
    public function normalizeAsset($value): ?Asset
    {
        try {
            return $value ? Asset::findOne($value) : null;
        } catch (Exception $e) {
            return null; // This can happen if a default value is set for empty values in getNormalizedProperties()
        }
    }

    /**
     *  Get array of asset elements (or empty array)
     *
     * @param $value
     * @return mixed
     */
    public function normalizeAssets($value): mixed
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        return $value ? Asset::find()->id($value)->all() : [];
    }

    /**
     * Return formatted date string
     *
     * @param $value
     * @return string|null
     * @throws InvalidConfigException
     */
    public function normalizeDate($value): ?string
    {
        // Check if $value is a ISO 8601 date string (see __construct)
        if (is_string($value)) {
            $date = DateTimeHelper::toDateTime($value);
            if ($date) {
                return Craft::$app->getFormatter()->asDate($date, PropertiesFieldPlugin::getInstance()->getSettings()->dateFormat);
            }
        }
        return $value;
    }

    /**
     * Return SingleOptionFieldData object
     *
     * @param $value
     * @param $config
     * @return SingleOptionFieldData|mixed
     */
    public function normalizeSelect($value, $config)
    {
        try {
            // TODO: This is the same logic as in _inputs/select.twig, unify this
            $options = array_map(function($option) {
                $parts = explode(':', $option, 2);
                return [
                    'value' => $parts[0],
                    'label' => count($parts) === 2 ? $parts[1] : $parts[0],
                ];
            }, explode("\n", str_replace("\r", '', $config['options'])));


            // TODO: Check this AI generated code....
            $selectedOption = array_filter($options, function($option) use ($value) {
                return $option['value'] === $value;
            });

            // set the first selected option as the value
            $selectedOption = reset($selectedOption);

            $value = new SingleOptionFieldData($selectedOption ? $selectedOption['label'] : '', $value, !empty($selectedOption), true);
            $value->setOptions($options);
            return $value;
        } catch (Exception $e) {

            Craft::error($e->getMessage(), __METHOD__);
            return $value;
        }
    }

    /**
     * Return string with Yes/No and optional comment, e.g. "Yes (Beginner)"
     *
     * @param $value
     * @return string
     */
    public function normalizeExtendedBoolean($value): string
    {

        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        if (!$value) {
            return '';
        }

        $string = $value['isOn'] ? Craft::t('_properties-field', 'Yes') : Craft::t('_properties-field', 'No');

        if ($value['comment']) {
            $string .= ' (' . $value['comment'] . ')';
        }

        return $string;
    }

    /**
     * Return string with value and unit, e.g. "10 m"
     *
     * @param $value
     * @return string
     */
    public function normalizeDimension($value): string
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        if (!$value || !$value['quantity']) {
            return '';
        }
        $string = $value['quantity'] ?? '';
        if ($value['unit']) {
            $string .= ' ' . $value['unit'];
        }

        return $string;
    }

    /**
     * Return properties as JsonData object
     *
     * @param bool $raw true = return raw JSON data as stored in DB, false = return data matching current prop config
     * @return JsonData
     * @throws InvalidConfigException
     */
    public function getJsonData(bool $raw = false): JsonData
    {
        if (version_compare(Craft::$app->getVersion(), '5.7.0', '<')) {
            throw new InvalidConfigException('Craft 5.7 or higher is required to use the JSON data type.');
        }

        if ($raw) {
            return new JsonData($this->properties);
        }

        $props = [];

        foreach ($this->propertiesFieldConfig as $propertyConfig) {
            $value = $this->properties[$propertyConfig['handle']] ?? null;
                $props[$propertyConfig['handle']] = $value;
        }
        return new JsonData($props);
    }

    /**
     * Return properties as JSON string
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function __toString(): string
    {
        // JsonData returns a string representation of the JSON data
        return (string)$this->getJsonData();
    }

}
