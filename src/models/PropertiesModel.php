<?php

namespace wsydney76\propertiesfield\models;

use CommerceGuys\Addressing\Country\Country;
use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\data\JsonData;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;
use craft\fields\Money;
use craft\helpers\DateTimeHelper;
use Exception;
use Illuminate\Support\Collection;
use wsydney76\propertiesfield\fields\Properties;
use wsydney76\propertiesfield\PropertiesFieldPlugin;
use yii\base\InvalidConfigException;
use function call_user_func;
use function is_array;
use function is_string;
use function json_decode;
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
        $propertyTypes = $this->settings->getAllPropertyTypes();

        foreach ($config['propertiesFieldConfig'] as $propertyConfig) {
            if (isset($propertyTypes[$propertyConfig['type']]['onConstruct']) && isset($config['properties'][$propertyConfig['handle']])) {
                $property = call_user_func(
                    $propertyTypes[$propertyConfig['type']]['onConstruct'],
                    $config['properties'][$propertyConfig['handle']],
                    $propertyConfig);

                $config['properties'][$propertyConfig['handle']] = $property;
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

            if ($props['ignoreMissing'] && $propertyConfig['type'] !== 'groupHeader' && !isset($this->properties[$propertyConfig['handle']])) {
                continue;
            }

            // Newly added properties not in database
            if (!isset($this->properties[$propertyConfig['handle']])) {
                $this->properties[$propertyConfig['handle']] = $props['default'];
            }

            $value = $this->properties[$propertyConfig['handle']] ?: $props['default'];

            if ($props['ignoreEmpty'] && $propertyConfig['type'] !== 'groupHeader' && empty($value)) {
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
