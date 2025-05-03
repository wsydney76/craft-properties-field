<?php

namespace wsydney76\propertiesfield\helpers;

use Craft;
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


    public static function propValueSql(string $fieldIdent, string $prop, string $cast = ''): string
    {
        $parts = explode('.', $fieldIdent);

        if (count($parts) === 2) {
            $entryTypes = [Craft::$app->entries->getEntryTypeByHandle($parts[0])];
            $fieldHandle = $parts[1];
        } else {
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


}