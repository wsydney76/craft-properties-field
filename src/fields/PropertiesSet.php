<?php

namespace wsydney76\propertiesfield\fields;

use craft\fields\Table;

class PropertiesSet extends Table
{

    use PropertiesTrait;

    public function init(): void
    {
        parent::init();

        if ($this->columns === []) {
            $this->columns = $this->getConfigTableColumns();
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

    public function checkConfig($element): void
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