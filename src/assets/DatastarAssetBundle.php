<?php

namespace putyourlightson\datastar\assets;

use Craft;
use craft\web\AssetBundle;
use starfederation\datastar\Consts;

class DatastarAssetBundle extends AssetBundle
{
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
    public $sourcePath = '@putyourlightson/datastar/resources/lib/datastar/' . Consts::VERSION;

    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'type' => 'module',
    ];
}
