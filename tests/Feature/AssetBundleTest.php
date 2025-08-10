<?php
/**
 * Tests the Datastar asset bundle.
 */

use putyourlightson\datastar\assets\DatastarAssetBundle;

test('Test that the asset bundle uses the correct version', function() {
    $bundle = new DatastarAssetBundle();
    $filePath = Craft::getAlias($bundle->sourcePath . '/datastar.js');

    expect(file_exists($filePath))
        ->toBeTrue();
});
