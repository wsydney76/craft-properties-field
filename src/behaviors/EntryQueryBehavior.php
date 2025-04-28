<?php

namespace wsydney76\propertiesfield\behaviors;

use Craft;
use craft\elements\db\EntryQuery;
use yii\base\Behavior;
use yii\db\Expression;

/**
 * Entry Query Behavior behavior
 *
 * @property EntryQuery $owner
 */
class EntryQueryBehavior extends Behavior
{
    public function hasProp(string $entryTypeHandle, string $fieldHandle, string $prop, string $value): EntryQuery
    {
        $entryType = Craft::$app->entries->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            throw new \InvalidArgumentException('Invalid entry type handle: ' . $entryTypeHandle);
        }

        $field = $entryType->getFieldLayout()->getFieldByHandle($fieldHandle);
        if (!$field) {
            throw new \InvalidArgumentException('Invalid field handle: ' . $fieldHandle);
        }

        $sql = sprintf("JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"%s\".%s')) = '%s'", $field->layoutElement->uid, $prop, $value);
        // "JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"0f8cec2a-3764-4546-a7e0-6429150b66ca\".age')) = '24'"

        $this->owner->andWhere(new Expression($sql));
        return $this->owner;
    }

    public function propContains(string $entryTypeHandle, string $fieldHandle, string $prop, string $value): EntryQuery
{
    $entryType = Craft::$app->entries->getEntryTypeByHandle($entryTypeHandle);
    if (!$entryType) {
        throw new \InvalidArgumentException('Invalid entry type handle: ' . $entryTypeHandle);
    }

    $field = $entryType->getFieldLayout()->getFieldByHandle($fieldHandle);
    if (!$field) {
        throw new \InvalidArgumentException('Invalid field handle: ' . $fieldHandle);
    }


    $sql = sprintf("JSON_CONTAINS(JSON_EXTRACT(content, '$.\"%s\".%s'), '\"%s\"')", $field->layoutElement->uid, $prop, $value);
    // JSON_CONTAINS(JSON_EXTRACT(your_json_column, '$."26a389ed-ea3a-45f9-9f7f-fed91b9896b8".multiImage'), '"45"')

    $this->owner->andWhere(new Expression($sql));
    return $this->owner;
}
}


