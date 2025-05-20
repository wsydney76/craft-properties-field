<?php

namespace wsydney76\propertiesfield\models;

use CommerceGuys\Addressing\Country\Country;
use Craft;
use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;
use craft\fields\Money;
use craft\helpers\DateTimeHelper;
use craft\validators\UrlValidator;
use Exception;
use Illuminate\Support\Collection;
use wsydney76\propertiesfield\fields\Properties;
use wsydney76\propertiesfield\PropertiesFieldPlugin;
use yii\base\InvalidConfigException;

// Using a separate class here to avoid conflicts with plugin settings/project config

class Config
{

    public static array $propertyTypes = [
        'text' => [
            'label' => 'Text',
            'type' => 'text',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsValue'],
        ],
        'textarea' => [
            'label' => 'Text Area',
            'type' => 'textarea',
            'template' => '_properties-field/_inputs/textarea.twig',
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsValue'],
        ],
        'number' => [
            'label' => 'Number',
            'type' => 'number',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [self::class, 'validateRequired'],
                [self::class, 'validateNumber'],
            ],
            'onNormalize' => [self::class, 'normalizeNumber'],
            'onDefineKeywords' => [self::class, 'keywordsValue'],
        ],
        'range' => [
            'label' => 'Range',
            'type' => 'range',
            'template' => '_properties-field/_inputs/range.twig',
            'onValidate' => [
                [self::class, 'validateRequired'],
                [self::class, 'validateNumber'],
            ],
        ],
        'money' => [
            'label' => 'Money',
            'type' => 'money',
            'template' => '_properties-field/_inputs/money.twig',
            'onNormalize' => [self::class, 'normalizeMoney'],
            'onValidate' => [
                [self::class, 'validateRequired'],
                // [self::class, 'validateMoney'],
            ],
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [self::class, 'validateRequired'],
                [self::class, 'validateEmail'],
            ],
            'onDefineKeywords' => [self::class, 'keywordsValue'],
        ],
        'url' => [
            'label' => 'URL',
            'type' => 'url',
            'template' => '_properties-field/_inputs/text.twig',
            'onValidate' => [
                [self::class, 'validateRequired'],
                [self::class, 'validateUrl'],
            ],
            'onDefineKeywords' => [self::class, 'keywordsValue'],
        ],
        'boolean' => [
            'label' => 'Boolean',
            'type' => 'boolean',
            'template' => '_properties-field/_inputs/boolean.twig',
            'onDefineKeywords' => [self::class, 'keywordsBoolean'],
        ],
        'date' => [
            'label' => 'Date',
            'type' => 'date',
            'template' => '_properties-field/_inputs/date.twig',
            'onNormalize' => [self::class, 'normalizeDate'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onConstruct' => [self::class, 'constructDate'],
        ],
        'datetime' => [
            'label' => 'Date Time',
            'type' => 'datetime',
            'template' => '_properties-field/_inputs/datetime.twig',
            'onNormalize' => [self::class, 'normalizeDateTime'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onConstruct' => [self::class, 'constructDate'],
        ],
        'select' => [
            'label' => 'Select',
            'type' => 'select',
            'template' => '_properties-field/_inputs/select.twig',
            'onNormalize' => [self::class, 'normalizeSelect'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsSingleOptionData'],
        ],
        'radio' => [
            'label' => 'Radio Buttons',
            'type' => 'radio',
            'template' => '_properties-field/_inputs/radio.twig',
            'onNormalize' => [self::class, 'normalizeSelect'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsSingleOptionData'],
        ],
        'checkboxes' => [
            'label' => 'Checkboxes',
            'type' => 'checkboxes',
            'template' => '_properties-field/_inputs/checkboxes.twig',
            'onNormalize' => [self::class, 'normalizeMultiSelect'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsMultiOptionsData'],
        ],
        'multiselect' => [
            'label' => 'Multiselect',
            'type' => 'multiselect',
            'template' => '_properties-field/_inputs/multiselect.twig',
            'onNormalize' => [self::class, 'normalizeMultiSelect'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsMultiOptionsData'],
        ],
        'entry' => [
            'label' => 'Entry (Single)',
            'type' => 'entry',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [self::class, 'normalizeEntry'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsSingleElement'],
        ],
        'entrySelect' => [
            'label' => 'Entry Select (Single)',
            'type' => 'entrySelect',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/entrySelect.twig',
            'onNormalize' => [self::class, 'normalizeEntry'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsSingleElement'],
        ],
        'entries' => [
            'label' => 'Entry (Multi)',
            'type' => 'entries',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [self::class, 'normalizeEntries'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsMultiElements'],
        ],
        'entriesSelect' => [
            'label' => 'Entries Select (Multi)',
            'type' => 'entriesSelect',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/entriesSelect.twig',
            'onNormalize' => [self::class, 'normalizeEntries'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsMultiElements'],
        ],
        'asset' => [
            'label' => 'Asset (Single)',
            'type' => 'asset',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [self::class, 'normalizeAsset'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsSingleElement'],
        ],
        'assets' => [
            'label' => 'Asset (Multi)',
            'type' => 'assets',
            'isRelation' => true,
            'template' => '_properties-field/_inputs/elementSelect.twig',
            'onNormalize' => [self::class, 'normalizeAssets'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsMultiElements'],
        ],
        'country' => [
            'label' => 'Country',
            'type' => 'country',
            'template' => '_properties-field/_inputs/country.twig',
            'onNormalize' => [self::class, 'normalizeCountry'],
            'onValidate' => [[self::class, 'validateRequired']],
            'onDefineKeywords' => [self::class, 'keywordsCountry'],
        ],
        'table' => [
            'label' => 'Table',
            'type' => 'table',
            'template' => '_properties-field/_inputs/table.twig',
        ],
        'extendedBoolean' => [
            'label' => 'Boolean with comment',
            'type' => 'extendedBoolean',
            'template' => '_properties-field/_inputs/extendedBoolean.twig',
            'onNormalize' => [self::class, 'normalizeExtendedBoolean'],
            'onValidate' => [[self::class, 'validateExtendedBoolean']],
            'onDefineKeywords' => [self::class, 'keywordsExtendedBoolean'],
        ],
        'dimension' => [
            'label' => 'Dimension',
            'type' => 'dimension',
            'template' => '_properties-field/_inputs/dimension.twig',
            'onNormalize' => [self::class, 'normalizeDimension'],
            'onValidate' => [[self::class, 'validateDimension']],
        ],
        'groupHeader' => [
            'label' => 'Group Header',
            'type' => 'groupHeader',
            'onNormalize' => [self::class, 'normalizeGroupHeader'],
        ],
        'set' => [
            'label' => 'Dynamic Property Set',
            'type' => 'set',
            'template' => '_properties-field/_inputs/set.twig'
        ],
    ];

    /**
     * ===================================================================
     * CONSTRUCT CALLBACKS
     * ===================================================================
     */

    public static function constructDate(mixed $date, array $propertyConfig): string
    {
        if ($date && is_array($date)) {
            $date = DateTimeHelper::toIso8601($date);
        }

        return $date;
    }


    /**
     * ===================================================================
     * VALIDATION CALLBACKS
     * ===================================================================
     */

    public static function validateRequired(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if ($property['required'] && !$value) {
            self::addError($element, $field, $property, Craft::t('_properties-field', 'cannot be blank'));
        }
    }

    public static function validateNumber(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $fieldConfig = json_decode($property['fieldConfig'], true);

        if (isset($fieldConfig['min'])) {
            if ($value < $fieldConfig['min']) {
                self::addError($element, $field, $property, Craft::t('_properties-field', 'must be greater than or equal to {min}.', ['min' => $fieldConfig['min']]));
            }
        }
        if (isset($fieldConfig['max'])) {
            if ($value > $fieldConfig['max']) {
                self::addError($element, $field, $property, Craft::t('_properties-field', 'must be less than or equal to {max}.', ['max' => $fieldConfig['max']]));
            }
        }
    }

    public static function validateEmail(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        // TODO: Validator??
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::addError($element, $field, $property, Craft::t('_properties-field', 'must be a valid email address.'));
        }
    }

    public static function validateUrl(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        if ((new UrlValidator())->validateValue($value)) {
            self::addError($element, $field, $property, Craft::t('_properties-field', 'must be a valid URL.'));
        }
    }

    public static function validateExtendedBoolean(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }
        if ($property['required'] && $value['isOn'] &&  !$value['comment']) {
            self::addError($element, $field, $property, Craft::t('_properties-field', 'Comment cannot be blank.'));
        }
    }

    public static function validateDimension(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if ($property['required'] && !$value['quantity']) {
            self::addError($element, $field, $property, Craft::t('_properties-field', 'Quantity cannot be blank.'));
        }
    }

    /**
     * ===================================================================
     * NORMALIZATION CALLBACKS
     * ===================================================================
     */

    public static function normalizeNumber($value, $config)
    {
        $fieldConfig = json_decode($config['fieldConfig'], true) ?? [];

        if (isset($fieldConfig['suffix'])) {
            $value = $value . ' ' . $fieldConfig['suffix'];
        }

        return $value;
    }


    /**
     * Get entry element (or null)
     *
     * @param $value
     * @return Entry|null
     */
    public static function normalizeEntry($value): ?Entry
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
    public static function normalizeEntries($value): mixed
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        return $value ? Entry::find()->id($value)->collect() : Collection::make([]);
    }

    /**
     *  Get asset element (or null)
     *
     * @param $value
     * @return Asset|null
     */
    public static function normalizeAsset($value): ?Asset
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
    public static function normalizeAssets($value): mixed
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (is_string($value)) {
            return $value;
        }

        return $value ? Asset::find()->id($value)->collect() : Collection::make([]);
    }

    /**
     *  Get country model
     *
     * @param $value
     * @return ?Country
     */
    public static function normalizeCountry($value): ?Country
    {
        // This can happen if a default value is set for empty values in getNormalizedProperties()
        if (!$value) {
            return null;
        }

        return Craft::$app->getAddresses()->getCountryRepository()->get($value, Craft::$app->language);
    }

    /**
     *  Get money
     *
     * @param $value
     * @return ?Country
     */
    public static function normalizeMoney($value, $config): ?\Money\Money
    {
        $currency = PropertiesFieldPlugin::getInstance()->getSettings()->currency;

        if ($config['fieldConfig']) {
            $fieldConfig = json_decode($config['fieldConfig']);
            if (isset($fieldConfig->currency)) {
                $currency = $fieldConfig->currency;
            }
        }

        if (!$value) {
            return null;
        }

        $field = new Money([
            'currency' => $currency,
        ]);

        return $field->normalizeValue($value, new Entry());
    }


    /**
     * Return formatted date string
     *
     * @param $value
     * @return string|null
     * @throws InvalidConfigException
     */
    public static function normalizeDate($value): ?string
    {

        $format = PropertiesFieldPlugin::getInstance()->getSettings()->dateFormat;
        // Check if $value is a ISO 8601 date string (see __construct)
        if (is_string($value)) {
            $date = DateTimeHelper::toDateTime($value);
            if ($date) {
                return Craft::$app->getFormatter()->asDate($date, $format);
            }
        }
        $date = DateTimeHelper::toDateTime($value);
        return Craft::$app->getFormatter()->asDate($date, $format);
    }

    /**
     * Return formatted date string
     *
     * @param $value
     * @return string|null
     * @throws InvalidConfigException
     */
    public static function normalizeDateTime($value): ?string
    {

        $format = PropertiesFieldPlugin::getInstance()->getSettings()->dateTimeFormat;
        // Check if $value is a ISO 8601 date string (see __construct)
        if (is_string($value)) {
            $date = DateTimeHelper::toDateTime($value);
            if ($date) {
                return Craft::$app->getFormatter()->asDatetime($date, $format);
            }
        }
        $date = DateTimeHelper::toDateTime($value);
        return Craft::$app->getFormatter()->asDatetime($date, $format);
    }

    /**
     * Return SingleOptionFieldData object
     *
     * @param $value
     * @param $config
     * @return SingleOptionFieldData|mixed
     */
    public static function normalizeSelect($value, $config)
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

    public static function normalizeMultiSelect($value, $config)
    {
        try {
            if ($value === null || $value === '') {
                $value = [];
            }

            // TODO: This is the same logic as in _inputs/select.twig, unify this
            $options = array_map(function($option) {
                $parts = explode(':', $option, 2);
                return [
                    'value' => $parts[0],
                    'label' => count($parts) === 2 ? $parts[1] : $parts[0],
                ];
            }, explode("\n", str_replace("\r", '', $config['options'])));


            // Copied from BaseOptionsField::normalizeValue()
            // TODO: Any chance to reuse this code?
            $selectedBlankOption = false;
            $optionValues = [];
            $optionLabels = [];
            $optionData = [];

            foreach ($options as $option) {
                $selected = self::isOptionSelected($option, $value, $value, $selectedBlankOption);
                $optionData[] = new OptionData($option['label'], $option['value'], $selected, true);
                $optionValues[] = (string)$option['value'];
                $optionLabels[] = (string)$option['label'];
            }

            $selectedOptions = [];

            foreach ($value as $selectedValue) {
                $index = array_search($selectedValue, $optionValues, true);
                $valid = $index !== false;
                $label = $valid ? $optionLabels[$index] : null;
                $selectedOptions[] = new OptionData($label, $selectedValue, true, $valid);
            }

            $value = new MultiOptionsFieldData($selectedOptions);
            $value->setOptions($optionData);

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
    public static function normalizeExtendedBoolean($value): string
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
    public static function normalizeDimension($value): string
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

    public static function normalizeGroupHeader($value, $config)
    {
        return $config['name'];
    }

    protected static function isOptionSelected(array $option, mixed $value, array &$selectedValues, bool &$selectedBlankOption): bool
    {
        return in_array($option['value'], $selectedValues, true);
    }


    /**
     * ===================================================================
     * KEYWORDS CALLBACKS
     * ===================================================================
     */

    public static function keywordsValue($value): string
    {
        return $value;
    }

    public static function keywordsSingleOptionData($data): string
    {
        return $data->label;
    }

    public static function keywordsMultiOptionsData($data): string
    {
        $keywords = '';
        foreach ($data as $option) {
            $keywords .= $option->label . ' ';
        }

        return $keywords;
    }


    public static function keywordsSingleElement($element): string
    {
        return $element->title;
    }
    public static function keywordsMultiElements($elements): string
    {
        return $elements->map(function($element) {
            return $element->title;
        })->join(' ');
    }

    public static function keywordsBoolean($value, $config)
    {
        return $value ? Craft::t('site', $config['name']): '';
    }

    public static function keywordsExtendedBoolean($value, $config, $data)
    {
        $value = $data->get($config['handle']);
        $keywords = $value['isOn'] ? Craft::t('site', $config['name']) : '';
        $keywords .= " " . $value['comment'];

        return $keywords;
    }

    public static function keywordsCountry($value): string
    {
        return $value->getName();
    }

    private static function addError(ElementInterface $element, Properties $field, array $property, string $message): void
    {
        $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . $message);

        $flash = Craft::$app->getSession()->getFlash('propertiesFieldErrors');

        $flash[$field->handle][$property['handle']] = "{$property['name']}: $message";

        Craft::$app->getSession()->setFlash('propertiesFieldErrors', $flash);
    }


    /**
     * Table input columns for properties config
     *
     * @return array
     */
    public static function getConfigTableColumns(): array
    {
        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        $propertyTypes = array_merge(Config::$propertyTypes, $settings->extraPropertyTypes);

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
            'propertyTypes' => [
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