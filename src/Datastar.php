<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use Craft;
use nystudio107\autocomplete\events\DefineGeneratorValuesEvent;
use nystudio107\autocomplete\generators\AutocompleteTwigExtensionGenerator;
use putyourlightson\datastar\assets\DatastarAssetBundle;
use putyourlightson\datastar\models\SettingsModel;
use putyourlightson\datastar\models\SignalsModel;
use putyourlightson\datastar\services\SseService;
use putyourlightson\datastar\twigextensions\DatastarTwigExtension;
use yii\base\Event;
use yii\base\Module;

/**
 * @property-read SseService $sse
 * @property-read SettingsModel $settings
 */
class Datastar extends Module
{
    /**
     * The module ID.
     */
    public const ID = 'datastar-module';

    /**
     * The module settings.
     */
    private ?SettingsModel $_settings = null;

    /**
     * The bootstrap process creates an instance of the module.
     */
    public static function bootstrap(): void
    {
        static::getInstance();
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): Datastar
    {
        if ($module = Craft::$app->getModule(self::ID)) {
            /** @var Datastar $module */
            return $module;
        }

        $module = new Datastar(self::ID);
        static::setInstance($module);
        Craft::$app->setModule(self::ID, $module);
        Craft::setAlias('@putyourlightson/datastar', __DIR__);

        return $module;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->registerComponents();
        $this->registerTwigExtension();
        $this->registerScript();
        $this->registerAutocompleteEvent();
    }

    public function getSettings(): SettingsModel
    {
        if ($this->_settings === null) {
            $this->_settings = new SettingsModel(Craft::$app->getConfig()->getConfigFromFile('datastar'));
        }

        return $this->_settings;
    }

    private function registerComponents(): void
    {
        $this->setComponents([
            'sse' => SseService::class,
        ]);
    }

    private function registerTwigExtension(): void
    {
        Craft::$app->getView()->registerTwigExtension(new DatastarTwigExtension());
    }

    private function registerScript(): void
    {
        if (!$this->settings->registerScript) {
            return;
        }

        $bundle = Craft::$app->getView()->registerAssetBundle(DatastarAssetBundle::class);

        // Register the JS file explicitly so that it will be output when using template caching.
        $url = Craft::$app->getView()->getAssetManager()->getAssetUrl($bundle, $bundle->js[0]);
        Craft::$app->getView()->registerJsFile($url, $bundle->jsOptions);
    }

    private function registerAutocompleteEvent(): void
    {
        if (!class_exists('nystudio107\autocomplete\generators\AutocompleteTwigExtensionGenerator')) {
            return;
        }

        Event::on(AutocompleteTwigExtensionGenerator::class,
            AutocompleteTwigExtensionGenerator::EVENT_BEFORE_GENERATE,
            function(DefineGeneratorValuesEvent $event) {
                $event->values[$this->settings->signalsVariableName] = 'new \\' . SignalsModel::class . '()';
            }
        );
    }
}
