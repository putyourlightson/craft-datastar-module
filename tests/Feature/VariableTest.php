<?php

/**
 * Tests the Datastar variable.
 */

use craft\web\Request;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use putyourlightson\datastar\variables\DatastarVariable;
use Twig\Error\SyntaxError;

beforeEach(function() {
    Datastar::getInstance()->set('sse', SseService::class);
});

test('Test creating an action', function(string $method) {
    $variable = new DatastarVariable();
    $value = $variable->$method('route');
    expect($value)
        ->toStartWith("@$method(")
        ->toContain('route');

    if ($method === 'get') {
        expect($value)
            ->not->toContain(Request::CSRF_HEADER);
    } else {
        expect($value)
            ->toContain(Request::CSRF_HEADER);
    }
})->with([
    'get',
    'post',
    'put',
    'patch',
    'delete',
]);

test('Test creating an action containing an array of primitive params', function() {
    $variable = new DatastarVariable();
    $value = $variable->get('route', ['x' => 1, 'y' => 'string', 'z' => true]);
    expect($value)
        ->toContain('1', 'string', 'true');
});

test('Test that creating an action containing the signals variable name throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('route', [Datastar::getInstance()->settings->signalsVariableName => 1]);
})->throws(SyntaxError::class);

test('Test that creating an action containing an object param throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('route', ['object' => new stdClass()]);
})->throws(SyntaxError::class);
