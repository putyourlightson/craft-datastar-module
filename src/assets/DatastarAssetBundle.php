<?php

namespace putyourlightson\datastar\assets;

use craft\web\AssetBundle;
use putyourlightson\datastar\Datastar;

class DatastarAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/../resources/lib/datastar/' . Datastar::DATASTAR_VERSION;

    /**
     * @inheritdoc
     */
    public $js = [
        'datastar.js',
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'type' => 'module',
        'defer' => true,
    ];
}
