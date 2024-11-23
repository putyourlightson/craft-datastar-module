<?php

namespace putyourlightson\datastar\assets;

use craft\web\AssetBundle;
use starfederation\datastar\Consts;

class DatastarAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/../resources/lib/datastar/' . Consts::VERSION;

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
