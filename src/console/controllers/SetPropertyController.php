<?php

namespace wsydney76\propertiesfield\console\controllers;

use Craft;
use craft\console\Controller;
use wsydney76\propertiesfield\helpers\PropertiesFieldHelper;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\helpers\Console;

/**
 * Set default value controller
 */
class SetPropertyController extends Controller
{
    public $defaultAction = 'index';


    /**
     * Set a default value for a property in the database
     */
    public function actionIndex(
    int $id,
        int $siteId,
        string $fieldHandle,
        string $propertyName,
        string $value
    ): int
    {
        $element = Craft::$app->entries->getEntryById($id, $siteId);

        $rows = PropertiesFieldHelper::updateProperty(
            $element,
            $fieldHandle,
            $propertyName,
            $value
        );

        if ($rows) {
            $this->stdout("Updated $rows rows\n");
        } else {
            $this->stderr("No rows updated\n");
        }

        return ExitCode::OK;
    }
}
