<?php

namespace wsydney76\propertiesfield;

use Craft;
use craft\base\Event;
use craft\base\Plugin;
use craft\db\Query;
use craft\elements\db\EntryQuery;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use wsydney76\propertiesfield\behaviors\EntryQueryBehavior;
use wsydney76\propertiesfield\fields\Properties;
use wsydney76\propertiesfield\models\Settings;

/**
 * Properties Field plugin
 *
 * @method static PropertiesFieldPlugin getInstance()
 */
class PropertiesFieldPlugin extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    public bool $hasReadOnlyCpSettings = true;

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            Event::on(
                Fields::class,
                Fields::EVENT_REGISTER_FIELD_TYPES,
                function(RegisterComponentTypesEvent $event) {
                    $event->types[] = Properties::class;
                });

            Event::on(
                EntryQuery::class,
                Query::EVENT_DEFINE_BEHAVIORS,
                function(DefineBehaviorsEvent $event) {
                    $event->behaviors[] = EntryQueryBehavior::class;
                });
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('_properties-field/_settings', [
            'settings' => $this->getSettings(),
            'plugin' => $this,
            'config' => Craft::$app->getConfig()->getConfigFromFile('_properties-field'),
        ]);
    }
}
