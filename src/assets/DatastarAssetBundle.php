<?php

namespace putyourlightson\datastar\assets;

use Craft;
use craft\web\AssetBundle;

class DatastarAssetBundle extends AssetBundle
{
    public const VERSION = '1.0.0-RC.8';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $aliased = Craft::$app->getRequest()->getIsCpRequest();
        $this->js = [
            'datastar' . ($aliased ? '-aliased' : '') . '.js',
        ];
    }

    /**
     * @inheritdoc
     */
    public $sourcePath = '@putyourlightson/datastar/resources/lib/datastar/' . self::VERSION;

    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'type' => 'module',
    ];
}
