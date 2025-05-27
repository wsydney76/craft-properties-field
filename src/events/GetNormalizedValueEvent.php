<?php

namespace wsydney76\propertiesfield\events;

use craft\base\ElementInterface;
use craft\base\Event;
use wsydney76\propertiesfield\fields\Properties;

class GetNormalizedValueEvent extends Event
{
    public Properties $field;
    public ElementInterface $element;
    public array $propertyConfig;
    public mixed $normalizedValue;
    public mixed $value;
}