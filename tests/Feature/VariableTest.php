<?php

/**
 * Tests the Datastar functions.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use putyourlightson\datastar\variables\DatastarVariable;
use Twig\Error\SyntaxError;

beforeEach(function() {
    Datastar::getInstance()->set('sse', SseService::class);
});

test('Test creating an action', function(string $method) {
    $variable = new DatastarVariable();
    $value = $variable->$method('template');
    expect($value)
        ->toStartWith('sse(')
        ->toContain('template');

    if ($method === 'get') {
        expect($value)
            ->not->toContain($method)
            ->not->toContain('csrf');
    } else {
        expect($value)
            ->toContain($method)
            ->toContain('csrf');
    }
})->with([
    'get',
    'post',
    'put',
    'patch',
    'delete',
]);

test('Test that creating an action containing a reserved variable name throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('template', [Datastar::getInstance()->settings->signalsVariableName => 1]);
})->throws(SyntaxError::class);

test('Test that creating an action containing an object variable throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('template', ['object' => new stdClass()]);
})->throws(SyntaxError::class);
