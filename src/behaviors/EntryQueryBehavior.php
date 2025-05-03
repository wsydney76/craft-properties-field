<?php

namespace wsydney76\propertiesfield\behaviors;

use Craft;
use craft\elements\db\EntryQuery;
use wsydney76\propertiesfield\helpers\PropertiesFieldHelper;
use yii\base\Behavior;
use yii\db\Expression;
use function str_replace;

/**
 * Entry Query Behavior behavior
 *
 * @property EntryQuery $owner
 */
class EntryQueryBehavior extends Behavior
{
    public function propEquals(string $fieldIdent, string $prop, string $value): EntryQuery
    {
        $sql = PropertiesFieldHelper::propValueSql($fieldIdent, $prop );
        $sql = sprintf("(%s = '%s')", $sql, $value);

        // "JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"0f8cec2a-3764-4546-a7e0-6429150b66ca\".name')) = 'Karl'"

        $this->owner->andWhere(new Expression($sql));
        return $this->owner;
    }

    public function propLike(string $fieldIdent, string $prop, string $value): EntryQuery
    {
        $sql = PropertiesFieldHelper::propValueSql($fieldIdent, $prop);
        $sql = sprintf("%s LIKE '%%%s%%'", $sql, $value);

        $this->owner->andWhere(new Expression($sql));
        return $this->owner;
    }

    public function propContains(string $fieldIdent, string $prop, string $value): EntryQuery
    {
        $sql = PropertiesFieldHelper::propValueSql($fieldIdent, $prop);

        // Hack
        $sql = str_replace('JSON_UNQUOTE', 'JSON_CONTAINS', $sql);
        $sql = str_replace('))', '), \'"%s"\')', $sql);
        $sql = sprintf($sql, $value);

        // JSON_CONTAINS(JSON_EXTRACT(your_json_column, '$."26a389ed-ea3a-45f9-9f7f-fed91b9896b8".multiImage'), '"45"')

        $this->owner->andWhere(new Expression($sql));
        return $this->owner;
    }

    public function propIsOn(string $fieldIdent, string $prop): EntryQuery
    {
        $sql = PropertiesFieldHelper::propValueSql($fieldIdent, $prop);

        // Hack
        $sql = str_replace("'))", ".isOn')) = '1'", $sql);

        $this->owner->andWhere(new Expression($sql));
        return $this->owner;
    }

    /**
     * @param string $entryTypeHandle
     * @param string $fieldHandle
     * @return \craft\base\FieldInterface
     */
    protected function getField(string $entryTypeHandle, string $fieldHandle): \craft\base\FieldInterface
    {
        $entryType = Craft::$app->entries->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            throw new \InvalidArgumentException('Invalid entry type handle: ' . $entryTypeHandle);
        }

        $field = $entryType->getFieldLayout()->getFieldByHandle($fieldHandle);
        if (!$field) {
            throw new \InvalidArgumentException('Invalid field handle: ' . $fieldHandle);
        }
        return $field;
    }
}


