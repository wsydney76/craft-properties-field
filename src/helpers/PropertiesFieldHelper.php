<?php

namespace wsydney76\propertiesfield\helpers;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\models\FieldLayout;
use yii\base\InvalidArgumentException;
use yii\db\Expression;
use function count;
use function explode;
use function implode;
use function in_array;
use function sprintf;

class PropertiesFieldHelper
{


    /**
     * Returns a SQL expression to extract a property value from a JSON field.
     *
     * @param string $fieldIdent in the form of 'entryTypeHandle.fieldHandle' or 'fieldHandle'
     * @param string $prop The handle of the property to check
     * @param string $cast The SQL type to cast the value to
     * @return string
     */
    public static function propValueSql(string $fieldIdent, string $prop, string $cast = ''): string
    {
        $parts = explode('.', $fieldIdent);

        if (count($parts) === 2) {
            $entryTypes = [Craft::$app->entries->getEntryTypeByHandle($parts[0])];
            $fieldHandle = $parts[1];
        } else {
            // TODO: Can be more efficient?
            $entryTypes = Craft::$app->entries->getAllEntryTypes();
            $fieldHandle = $parts[0];
        }

        $fields = static::getFieldsFromCandidates($entryTypes, $fieldHandle);

        if (count($fields) === 1) {
            return self::getSingleFieldSQL($fields[0], $prop, $cast);
        }

        $singleFieldSQL = [];
        foreach ($fields as $field) {
            $singleFieldSQL[] = self::getSingleFieldSQL($field, $prop, $cast);
        }

        return sprintf('COALESCE(%s)', implode(', ', $singleFieldSQL));
    }


    /**
     * @param array $candidates
     * @param string $fieldHandle
     * @return array
     */
    private static function getFieldsFromCandidates(array $candidates, string $fieldHandle): array
    {
        $fields = [];

        foreach ($candidates as $candidate) {
            $field = static::getFieldFromLayout($candidate->getFieldLayout(), $fieldHandle);
            if ($field) {
                $fields[] = $field;
            }
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException("Field not found: $fieldHandle");
        }

        return $fields;
    }

    /**
     * @param mixed $fieldLayout
     * @param string $fieldHandle
     * @return FieldInterface|null
     */
    private static function getFieldFromLayout(FieldLayout $fieldLayout, string $fieldHandle): ?FieldInterface
    {
        $field = $fieldLayout->getFieldByHandle($fieldHandle);

        if ($field && !in_array($field::dbType(), ['json', 'text', 'decimal(65,16)'])) {
            throw new \InvalidArgumentException("Field is not a JSON,TEXT or DECIMAL field: $fieldHandle");
        }
        return $field;
    }

    /**
     * @param $fields
     * @param string $prop
     * @return string
     */
    protected static function getSingleFieldSQL($fields, string $prop, string $cast = ''): string
    {
        $sql = sprintf("JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"%s\".%s'))", $fields->layoutElement->uid, $prop);

        return $cast ?
            sprintf("CAST(%s AS %s)", $sql, $cast) :
            $sql;
    }

    public static function updateProperty(ElementInterface $element, string $fieldHandle, string $propertyHandle, mixed $value)
    {
        $uid = static::getUid($element->type->handle, $fieldHandle);

        $sql = <<<SQL
UPDATE elements_sites
    SET content = JSON_SET(content, '$."%s".%s', "%s")
    WHERE elementId = %s
    AND siteId = %s
SQL;

        $sql = sprintf($sql, $uid, $propertyHandle, $value, $element->id, $element->siteId);

        return Craft::$app->db->createCommand($sql)
            ->execute();
    }

    public static function getUid(string $entryTypeHandle, string $fieldHandle): string
    {
        $entryType = Craft::$app->entries->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            throw new InvalidArgumentException("Entry type not found: $entryTypeHandle");
        }
        $field = $entryType->getFieldLayout()->getFieldByHandle($fieldHandle);
        if (!$field) {
            throw new InvalidArgumentException("Field not found: $fieldHandle");
        }
        return $field->layoutElement->uid;
    }


}