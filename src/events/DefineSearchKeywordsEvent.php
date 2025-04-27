<?php

namespace wsydney76\propertiesfield\events;

use craft\base\ElementInterface;
use craft\base\Event;
use craft\base\FieldInterface;

class DefineSearchKeywordsEvent extends Event
{
    public string $keywords = '';
    public ElementInterface $element;
    public FieldInterface $field;
    public array $property = [];
}