<?php

namespace wsydney76\propertiesfield\console\controllers;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\helpers\Console;

/**
 * Change Handle controller
 */
class ChangeHandleController extends Controller
{
    public $defaultAction = 'index';

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'index':
                // $options[] = '...';
                break;
        }
        return $options;
    }

    /**
     * _properties-field/change-handle command
     */
    public function actionIndex(string $entryTypeHandle, string $fieldTypeHandle, string $oldHandle, string $newHandle): int
    {
        $entryType = Craft::$app->entries->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            Console::error('Invalid entry type handle: ' . $entryTypeHandle);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $field = $entryType->getFieldLayout()->getFieldByHandle($fieldTypeHandle);
        if (!$field) {
            Console::error('Invalid field type handle: ' . $fieldTypeHandle);
            return ExitCode::UNSPECIFIED_ERROR;
        }


        $sql = <<<SQL
UPDATE elements_sites
SET content = JSON_REMOVE(
    JSON_SET(
        content,
        '$."fieldHandle"."newHandle"',
        JSON_EXTRACT(content, '$."fieldHandle"."oldHandle"')
    ),
    '$."fieldHandle"."oldHandle"'
)
WHERE JSON_CONTAINS_PATH(content, 'one', '$."fieldHandle"."oldHandle"');
SQL;

        $sql = str_replace("oldHandle", $oldHandle, $sql);
        $sql = str_replace("newHandle", $newHandle, $sql);
        $sql = str_replace("fieldHandle", $field->layoutElement->uid, $sql);

        Console::output($sql);
        if (!$this->confirm("Are you sure you want to run this SQL command?")) {
            return ExitCode::OK;
        }

        if ($this->confirm("Backup your database before running this command?")) {
           $path = Craft::$app->db->backup();
           Console::output("Database backup created at: $path");
        }

        $command = Craft::$app->getDb()->createCommand($sql);

        try {
            $result = $command->execute();
        } catch (Exception $e) {
            Console::error($e->getMessage());
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Console::output("Updated {$result} rows.");

        return ExitCode::OK;
    }
}
