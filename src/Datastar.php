<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use Craft;
use putyourlightson\datastar\assets\DatastarAssetBundle;
use putyourlightson\datastar\models\SettingsModel;
use putyourlightson\datastar\services\RequestService;
use putyourlightson\datastar\services\SseService;
use putyourlightson\datastar\twigextensions\DatastarTwigExtension;
use putyourlightson\datastar\web\StreamedResponse;
use yii\base\Module;

/**
 * @property-read RequestService $request
 * @property-read SseService $sse
 * @property-read StreamedResponse $streamedResponse
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
    private ?SettingsModel $settingsInternal = null;

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

        return $module;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        Craft::setAlias('@putyourlightson/datastar', __DIR__);

        parent::init();

        $this->registerComponents();
        $this->registerTwigExtension();
        $this->registerScript();
    }

    public function getSettings(): SettingsModel
    {
        if ($this->settingsInternal === null) {
            $this->settingsInternal = new SettingsModel(Craft::$app->getConfig()->getConfigFromFile('datastar'));
        }

        return $this->settingsInternal;
    }

    private function registerComponents(): void
    {
        $this->setComponents([
            'request' => RequestService::class,
            'sse' => SseService::class,
            'streamedResponse' => StreamedResponse::class,
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
}
