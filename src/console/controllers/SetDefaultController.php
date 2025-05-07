<?php

namespace wsydney76\propertiesfield\console\controllers;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\helpers\Console;

/**
 * Set default value controller
 */
class SetDefaultController extends Controller
{
    public $defaultAction = 'index';


    /**
     * Set a default value for a property in the database
     */
    public function actionIndex(string $entryTypeHandle, string $fieldTypeHandle, string $handle, string $value): int
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
UPDATE elements_sites es
JOIN elements e ON es.elementId = e.id
SET es.content = JSON_SET(es.content, '$."%fieldHandle%".%handle%', '%value%')
WHERE JSON_EXTRACT(es.content, '$."%fieldHandle%".%handle%') IS NULL
AND e.revisionId IS NULL;
SQL;

        $sql = str_replace("%handle%", $handle, $sql);
        $sql = str_replace("%value%", $value, $sql);
        $sql = str_replace("%fieldHandle%", $field->layoutElement->uid, $sql);

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
