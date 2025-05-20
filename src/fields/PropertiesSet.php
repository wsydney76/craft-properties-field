<?php

namespace wsydney76\propertiesfield\fields;

use craft\fields\Table;
use wsydney76\propertiesfield\models\Config;

class PropertiesSet extends Table
{

    use PropertiesTrait;

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

        $value = $this->validateConfig(
            $value,
            function ($error) use ($element) {
                $element->addError($this->handle, $error);
            }
        );

        $element->setFieldValue($this->handle, $value);
    }
}