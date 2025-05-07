<?php

namespace wsydney76\propertiesfield\events;

use craft\base\ElementInterface;
use craft\base\Event;
use craft\base\FieldInterface;

/**
 * DefineSearchKeywordsEvent class
 *
 * This event is triggered when defining search keywords for a custom property.
 * Add your own keywords to the $keywords property, space separated.
 *
 * @property string $keywords The search keywords
 * @property ElementInterface $element The element being indexed
 * @property FieldInterface $field The field being indexed
 * @property array $property The property being indexed
 */
class DefineSearchKeywordsEvent extends Event
{
    public string $keywords = '';
    public ElementInterface $element;
    public FieldInterface $field;
    public array $property = [];
}