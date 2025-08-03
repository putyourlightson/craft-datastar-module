<?php

/**
 * Tests the Datastar config model.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\Config;

test('Test that creating a config model containing the signals variable name throws an exception', function() {
    $config = new Config([
        'route' => 'test',
        'params' => [Datastar::getInstance()->settings->signalsVariableName => 1],
    ]);

    expect($config->validateParams())
        ->toBeFalse();
});

test('Test that creating a config model containing an object param throws an exception', function() {
    $config = new Config([
        'route' => 'test',
        'params' => ['object' => new stdClass()],
    ]);

    expect($config->validateParams())
        ->toBeFalse();
});
