<?php

namespace wsydney76\propertiesfield\fields;

use Craft;
use craft\base\CrossSiteCopyableFieldInterface;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\base\RelationalFieldInterface;
use craft\elements\Entry;
use craft\errors\InvalidFieldException;
use craft\helpers\Cp;
use craft\helpers\StringHelper;
use craft\validators\UrlValidator;
use Exception;
use wsydney76\propertiesfield\events\DefineSearchKeywordsEvent;
use wsydney76\propertiesfield\models\Config;
use wsydney76\propertiesfield\models\PropertiesModel;
use wsydney76\propertiesfield\PropertiesFieldPlugin;
use yii\db\Schema;

/**
 * Properties field type
 */
class Properties extends Field implements RelationalFieldInterface, CrossSiteCopyableFieldInterface, PreviewableFieldInterface
{


    public const string EVENT_DEFINE_SEARCH_KEYWORDS = 'defineSearchKeywords';

    // The properties selected for this field
    public array $propertiesFieldConfig = [];
    // color for the headers and labels, as used in CP CSS variables, e.g, blue, green, red, etc.
    public string $color = '';
    // The icon to display in the field header
    public string $icon = '';
    // The main text to display in the field header
    public string $heading = '';
    // The additional text to display in the field header
    public string $heading2 = '';
    // The text to display as tip below the field
    public string $tip = '';
    // The text to display as warning below the field
    public string $warning = '';

    // The template that renders the preview in element indexes and cards
    public string $previewTemplate = '';

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('_properties-field', 'Properties');
    }

    /**
     * @inheritDoc
     */
    public static function icon(): string
    {
        return 'table-list';
    }

    /**
     * @inheritDoc
     */
    public static function phpType(): string
    {
        return PropertiesModel::class;
    }

    /**
     * @inheritDoc
     */
    public static function dbType(): array|string|null
    {
        return Schema::TYPE_JSON;
    }


    /**
     * @inheritDoc
     */
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [

            ['propertiesFieldConfig', 'checkConfig'],

        ]);
    }

    /**
     * Validate properties field configuration
     *
     * @param $attribute
     * @return void
     */
    public function checkConfig($attribute): void
    {
        $handles = [];
        foreach ($this->propertiesFieldConfig as $i => $fieldConfig) {

            // Name is required
            if (empty($fieldConfig['name'])) {
                $this->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Name cannot be blank.'));
            } else {
                // generate handle from name if handle is empty
                if (empty($fieldConfig['handle'])) {
                    // If handle is empty, use name as handle
                    $fieldConfig['handle'] = StringHelper::toHandle($fieldConfig['name']);
                    $this->propertiesFieldConfig[$i]['handle'] = $fieldConfig['handle'];
                }
            }

            // Handle is required, must be a valid handle and unique
            if (empty($fieldConfig['handle'])) {
                $this->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Handle cannot be blank.'));
            } elseif (!(bool)preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $fieldConfig['handle'])) {
                $this->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Is not a valid handle.'));
            } elseif (in_array($fieldConfig['handle'], $handles, true)) {
                $this->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Handle must be unique.'));
            } else {
                $handles[] = $fieldConfig['handle'];
            }


            // Field config must be a valid JSON string
            if (!empty($fieldConfig['fieldConfig'])) {
                // Check if fieldConfig is a valid JSON string
                $isValidJson = json_decode($fieldConfig['fieldConfig'], true);
                if ($isValidJson === null) {
                    $this->addError($attribute, Craft::t('_properties-field', $i + 1 . ': Field Config must be a valid JSON string.'));
                }
            }
        }
    }

    /**
     * The HTML for the field settings in the control panel.
     *
     * @return string|null
     */
    public function getSettingsHtml(): ?string
    {
        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        $options = array_merge(Config::$propertyTypes, $settings->extraPropertyTypes);
        return
            Cp::iconPickerFieldHtml([
                'label' => Craft::t('_properties-field', 'Icon'),
                'name' => 'icon',
                'value' => $this->icon,
                'id' => 'icon',
                'instructions' => Craft::t('_properties-field', 'An icon displayed in a field header.'),
            ]) .
            Cp::TextFieldHtml([
                'label' => Craft::t('_properties-field', 'Heading'),
                'name' => 'heading',
                'value' => $this->heading,
                'id' => 'heading',
                'instructions' => Craft::t('_properties-field', 'A text displayed in a field header.'),
                'tip' => Craft::t('_properties-field', 'Works best if the field name is hidden.'),
            ]) .
            Cp::TextFieldHtml([
                'label' => Craft::t('_properties-field', 'Additional heading text'),
                'name' => 'heading2',
                'value' => $this->heading2,
                'id' => 'heading2',
            ]) .
            Cp::colorSelectFieldHtml([
                'label' => Craft::t('_properties-field', 'Base Color'),
                'name' => 'color',
                'value' => $this->color,
                'id' => 'color',
                'instructions' => Craft::t('_properties-field', 'The background color for headers and property labels.'),
            ]) .
            Cp::editableTableFieldHtml([
                'label' => Craft::t('_properties-field', 'Properties Configuration'),
                'instructions' => Craft::t('_properties-field', 'Options for select: one option per line, in the format value:label<br>Options for entries/assets: section/volume handles, comma separated'),
                'id' => 'propertiesFieldConfig',
                'name' => 'propertiesFieldConfig',
                'addRowLabel' => Craft::t('_properties-field', 'Add a property'),
                'allowAdd' => true,
                'allowReorder' => true,
                'allowDelete' => true,
                'warning' => Craft::t('_properties-field', 'Changing the handle or type may result in data loss or runtime errors without migrating existing content.'),
                'cols' => [
                    'name' => ['heading' => Craft::t('_properties-field', 'Name'), 'type' => 'singleline'],
                    'handle' => ['heading' => Craft::t('_properties-field', 'Handle'), 'type' => 'singleline', 'class' => 'code'],
                    'instructions' => ['heading' => Craft::t('_properties-field', 'Instructions'), 'type' => 'singleline'],
                    'required' => ['heading' => Craft::t('_properties-field', 'Required'), 'type' => 'lightswitch'],
                    'searchable' => ['heading' => Craft::t('_properties-field', 'Search'), 'type' => 'lightswitch'],
                    'type' => [
                        'heading' => Craft::t('_properties-field', 'Type'),
                        'type' => 'select',
                        'class' => 'code',
                        'options' => $options,
                        'width' => '10%',
                    ],
                    'options' => ['heading' => Craft::t('_properties-field', 'Options'), 'type' => 'multiline'],
                    'fieldConfig' => ['heading' => Craft::t('_properties-field', 'Field Config'), 'type' => 'multiline'],

                ],
                'rows' => $this->propertiesFieldConfig,
                'errors' => $this->getErrors('propertiesFieldConfig'),
                'data' => ['error-key' => 'options'],
            ]) .
            Cp::autosuggestFieldHtml([
                'label' => Craft::t('_properties-field', 'Preview Template'),
                'name' => 'previewTemplate',
                'value' => $this->previewTemplate,
                'id' => 'previewTemplate',
                'suggestTemplates' => true,
                'instructions' => Craft::t('_properties-field', 'The template that renders the preview in element indexes and cards.'),
            ]) .
            Cp::textareaFieldHtml([
                'label' => Craft::t('_properties-field', 'Tip'),
                'name' => 'tip',
                'value' => $this->tip,
                'id' => 'tip',
                'class' => 'nicetext',
                'rows' => 1,
                'warning' => Craft::t('_properties-field', 'This will be shown on every instance of the field and cannot be overwritten.')
            ]) .
            Cp::textareaFieldHtml([
                'label' => Craft::t('_properties-field', 'Warning'),
                'name' => 'warning',
                'value' => $this->warning,
                'id' => 'warning',
                'class' => 'nicetext',
                'rows' => 1,
                'warning' => Craft::t('_properties-field', 'This will be shown on every instance of the field and cannot be overwritten.')
            ]);
    }

    /**
     * @inheritDoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element): PropertiesModel
    {
        // Add propertiesFieldConfig for convenience
        if ($value instanceof PropertiesModel) {
            $value->propertiesFieldConfig = $this->expandPropertySet($this->propertiesFieldConfig);
            return $value;
        }

        if (is_array($value)) {
            return new PropertiesModel([
                'properties' => $value,
                'propertiesFieldConfig' => $this->expandPropertySet($this->propertiesFieldConfig),
                'element' => $element,
                'field' => $this,
            ]);
        }


        return new PropertiesModel(['propertiesFieldConfig' => $this->expandPropertySet($this->propertiesFieldConfig)]);
    }


    /**
     * @inheritDoc
     */
    public function serializeValue($value, ElementInterface $element = null): array
    {
        return $value->properties ?? [];
    }

    /**
     * @inheritDoc
     */
    protected function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {

        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        return Craft::$app->getView()->renderTemplate('_properties-field/_properties-input', [
            'field' => $this,
            'properties' => $value->properties,
            'propertiesFieldConfig' => $this->expandPropertySet($value->propertiesFieldConfig, true),
            'element' => $element,
            'settings' => $settings,
            'defaultPropertyTypes' => Config::$propertyTypes,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = ['validateProperties', 'on' => [Entry::SCENARIO_LIVE]];


        return $rules;
    }

    /**
     * Validate properties
     *
     * Using a callback configured in the 'onValidate' key of the property type config
     * The callback function must call ->addError() on the field if the validation fails
     *
     * TODO: Check if Craft's built-in validations can be used
     *
     * @param ElementInterface $element
     * @return void
     * @throws InvalidFieldException
     */
    public function validateProperties(ElementInterface $element)
    {
        $data = $element->getFieldValue($this->handle);
        $settings = PropertiesFieldPlugin::getInstance()->getSettings();

        foreach ($this->propertiesFieldConfig as $property) {
            // Using try/catch to avoid errors when the property is not set or mal formatted
            $callbacks = $settings->getAllPropertyTypes()[$property['type']]['onValidate'] ?? null;
            try {
                if ($callbacks) {
                    foreach ($callbacks as $callback) {
                        call_user_func($callback, $element, $this, $property, $data->properties[$property['handle']] ?? null);
                    }
                }
            } catch (Exception $e) {
                if (Craft::$app->config->general->devMode) {
                    \Craft::dd($e->getMessage());
                }
                Craft::error($e->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * @inheritDoc
     * @throws InvalidFieldException
     */
    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {

        $data = $element->getFieldValue($this->handle);
        $keywords = [];

        // TODO: Implement this via callbacks in the property type config??

        foreach ($data->getNormalizedProperties() as $property) {
            switch ($property['type']) {
                case 'text':
                case 'textarea':
                case 'email':
                case 'number':
                    $keywords[] = $property['value'];
                    break;
                case 'select':
                    $keywords[] = $property['normalizedValue']->label ?? '';
                    break;
                case 'entry':
                case 'asset':
                    if ($property['normalizedValue']) {
                        $keywords[] = $property['normalizedValue']->title ?? '';
                    }
                    break;
                case 'entries':
                case 'assets':
                    if ($property['normalizedValue']) {
                        foreach ($property['normalizedValue'] as $entry) {
                            $keywords[] = $entry->title ?? '';
                        }
                    }
                    break;
                case 'extendedBoolean':
                    $keywords[] = $property['value']['comment'] ?? '';
                    $keywords[] = $property['name'];
                    break;
                case 'boolean':
                    $keywords[] = $property['name'];
                    break;
                case 'date':
                case 'groupHeader':
                case 'set':
                    // Do nothing
                    break;
                default:
                    // Use event to define search keywords for custom property types
                    if ($this->hasEventHandlers(self::EVENT_DEFINE_SEARCH_KEYWORDS)) {
                        $event = new DefineSearchKeywordsEvent([
                            'element' => $element,
                            'field' => $this,
                            'property' => $property,
                        ]);
                        $this->trigger(self::EVENT_DEFINE_SEARCH_KEYWORDS, $event);
                        $keywords[] = $event->keywords;
                    }
                    break;
            }
        }

        return implode(' ', $keywords);
    }


    /**
     * Add dynamic property types from an entry to the propertiesFieldConfig
     * See read me for details
     *
     * @param $propertiesFieldConfig
     * @param bool $expandTableCriteria
     * @return array
     */
    private function expandPropertySet($propertiesFieldConfig, bool $expandTableCriteria = true)
    {
        $newConfig = [];
        foreach ($propertiesFieldConfig as $i => $config) {
            if ($config['type'] == 'set') {
                $setEntry = Entry::find()
                    ->slug($config['options'])
                    ->one();
                if ($setEntry) {
                    $extraConfigs = $setEntry->propertyTypes;
                    foreach ($extraConfigs as $extraConfig) {
                        $newConfig[] = [
                            'name' => $extraConfig['name'] ?? '',
                            'handle' => $extraConfig['handle'] ?? '',
                            'instructions' => $extraConfig['instructions'] ?? '',
                            'required' => $extraConfig['required'] ?? false,
                            'searchable' => $extraConfig['required'] ?? false,
                            'type' => $extraConfig['type'] ?? '',
                            'options' => $extraConfig['options'] ?? '',
                            'fieldConfig' => $extraConfig['fieldConfig'] ?? '',
                        ];
                    }
                }
            } else {
                $newConfig[] = $config;
            }
        }

        // If a pseudo 'entrySelect' table column is found, replace it with a select based on an entry query with the given criteria
        if ($expandTableCriteria) {
            foreach ($newConfig as $i => $config) {

                if ($config['type'] == 'table' && $config['fieldConfig']) {
                    $fieldConfig = json_decode($config['fieldConfig'], true);
                    if (isset($fieldConfig['cols'])) {
                        foreach ($fieldConfig['cols'] as $j => $col) {
                            if (isset($col['type']) && $col['type'] == 'entrySelect' && isset($col['criteria'])) {
                                $query = Entry::find();
                                Craft::configure($query, $col['criteria']);
                                $entries = $query->withCustomFields(false)->all();

                                $fieldConfig ['cols'][$j]['type'] = 'select';
                                $fieldConfig ['cols'][$j]['options'] = array_merge(
                                    [['value' => '', 'label' => '-']],
                                    array_map(function($entry) {
                                        return [
                                            'value' => $entry->id,
                                            'label' => $entry->title,
                                        ];
                                    }, $entries)
                                );

                                $newConfig[$i]['fieldConfig'] = json_encode($fieldConfig);
                            }
                        }
                    }
                }
            }
        }

        return $newConfig;
    }

    /**
     * Get a property config by handle
     *
     * @param string $handle
     * @return mixed
     */
    public function getPropertyConfigByHandle(string $handle): mixed
    {

        foreach ($this->propertiesFieldConfig as $propertyConfig) {
            if ($propertyConfig['handle'] === $handle) {
                return $propertyConfig;
            }
        }

        return null;
    }

    /**
     * ===================================================================
     * VALIDATION CALLBACKS
     * ===================================================================
     */

    public function validateRequired(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if ($property['required'] && !$value) {
            $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'cannot be blank.'));
        }
    }

    public function validateNumber(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $fieldConfig = json_decode($property['fieldConfig'], true);

        if (isset($fieldConfig['min'])) {
            if ($value < $fieldConfig['min']) {
                $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'must be greater than or equal to {min}.', ['min' => $fieldConfig['min']]));
            }
        }
        if (isset($fieldConfig['max'])) {
            if ($value > $fieldConfig['max']) {
                $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'must be less than or equal to {max}.', ['max' => $fieldConfig['max']]));
            }
        }
    }

    public function validateEmail(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        // TODO: Validator??
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'must be a valid email address.'));
        }
    }

    public function validateUrl(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if (!$value) {
            return;
        }

        if ((new UrlValidator())->validateValue($value)) {
            $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'must be a valid URL.'));
        }
    }

    public function validateExtendedBoolean(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if ($property['required'] && !$value['comment']) {
            $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'Comment cannot be blank.'));
        }
    }

    public function validateDimension(ElementInterface $element, Properties $field, array $property, mixed $value): void
    {
        if ($property['required'] && !$value['quantity']) {
            $element->addError($field->handle, $field->name . '/' . $property['name'] . ': ' . Craft::t('_properties-field', 'Quantity cannot be blank.'));
        }
    }


    /* --------------------------------------
     * RELATIONAL FIELD INTERFACE
     * TODO: Check for correct values
     * --------------------------------------
     */

    /**
     * @inheritDoc
     */
    public function localizeRelations(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function forceUpdateRelations(ElementInterface $element): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getRelationTargetIds(ElementInterface $element): array
    {
        $propertyModel = $element->getFieldValue($this->handle);
        $properties = $propertyModel->getNormalizedProperties();
        $ids = [];
        foreach ($properties as $property) {
            // The types that provide either a single id or an array of IDs
            if (in_array($property['type'], ['entry', 'entries', 'asset', 'assets'], true) && !empty($property['value'])) {
                if (is_string($property['value'])) {
                    $ids[] = $property['value'];
                } else {
                    $ids = array_merge($ids, $property['value']);
                }
            }
        }

        // Add the IDs from the table fields columns with type = entrySelect
        // Experimental...
        foreach ($this->propertiesFieldConfig as $propertyConfig) {
            if ($propertyConfig['type'] === 'table') {
                $fieldConfig = json_decode($propertyConfig['fieldConfig'], true);
                if (isset($fieldConfig['cols'])) {
                    foreach ($fieldConfig['cols'] as $key => $col) {
                        if ($col['type'] === 'entrySelect') {
                            $rows = $propertyModel->properties[$propertyConfig['handle']];
                            if ($rows) {
                                foreach ($rows as $row) {
                                    if ($row[$key]) {
                                        $ids[] = $row[$key];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ids;
    }

    public function getPreviewHtml(mixed $value, ElementInterface $element): string
    {
        $template = $this->previewTemplate;
        if ($template) {
            return Craft::$app->getView()->renderTemplate($template, [
                'value' => $value,
                'element' => $element,
            ], 'cp');
        }
        // Hint: Don't add fields without a preview template to element index columns or cards
        return Craft::t('_properties-field', 'No preview available.');
    }

    public function previewPlaceholderHtml(mixed $value, ?ElementInterface $element): string
    {
        return $this->getPreviewHtml($value, $element ?? new Entry());
    }
}
