<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use Craft;
use craft\web\Response;
use putyourlightson\datastar\assets\DatastarAssetBundle;
use putyourlightson\datastar\models\Settings;
use putyourlightson\datastar\services\Sse;
use putyourlightson\datastar\twigextensions\DatastarTwigExtension;
use putyourlightson\datastar\web\StreamedResponse;
use yii\base\Event;
use yii\base\Module;

/**
 * @property-read Sse $sse
 * @property-read StreamedResponse $streamedResponse
 * @property-read Settings $settings
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
    private ?Settings $settingsInternal = null;

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

    public function getSettings(): Settings
    {
        if ($this->settingsInternal === null) {
            $this->settingsInternal = new Settings(Craft::$app->getConfig()->getConfigFromFile('datastar'));
        }

        return $this->settingsInternal;
    }

    private function registerComponents(): void
    {
        $this->setComponents([
            'sse' => Sse::class,
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

        /**
         * Register the JS file explicitly so that it will be output when using template caching. We use the `EVENT_BEFORE_SEND` event so that the JS file is registered regardless of whether this is an error response or not.
         * https://github.com/putyourlightson/craft-datastar/issues/20
         */
        Event::on(Response::class, Response::EVENT_BEFORE_SEND, function() use ($bundle) {
            $url = Craft::$app->getView()->getAssetManager()->getAssetUrl($bundle, $bundle->js[0]);
            Craft::$app->getView()->registerJsFile($url, $bundle->jsOptions);
        });
    }
}
