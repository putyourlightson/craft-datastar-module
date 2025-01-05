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
    $value = $variable->$method('template');
    expect($value)
        ->toStartWith("@$method(")
        ->toContain('template');

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

test('Test creating an action containing an array of primitive variables', function() {
    $variable = new DatastarVariable();
    $value = $variable->get('template', ['x' => 1, 'y' => 'string', 'z' => true]);
    expect($value)
        ->toContain('1', 'string', 'true');
});

test('Test that creating an action containing a reserved variable name throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('template', [Datastar::getInstance()->settings->signalsVariableName => 1]);
})->throws(SyntaxError::class);

test('Test that creating an action containing an object variable throws an exception', function() {
    $variable = new DatastarVariable();
    $variable->get('template', ['object' => new stdClass()]);
})->throws(SyntaxError::class);
