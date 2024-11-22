<?php

/**
 * Tests the Datastar functions.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\twigextensions\DatastarFunctions;
use Twig\Error\SyntaxError;

test('Test creating an action', function(string $method) {
    $value = DatastarFunctions::datastar('template', method: $method);
    expect($value)
        ->toStartWith('$$' . $method . '(')
        ->toContain('template');

    if ($method === 'get') {
        expect($value)
            ->not->toContain('csrf');
    } else {
        expect($value)
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
    DatastarFunctions::datastar('template', [Datastar::getInstance()->settings->storeVariableName => 1]);
})->throws(SyntaxError::class);

test('Test that creating an action containing an object variable throws an exception', function() {
    DatastarFunctions::datastar('template', ['object' => new stdClass()]);
})->throws(SyntaxError::class);

test('Test creating a store', function() {
    $value = DatastarFunctions::datastarStore(['a' => 1, 'b' => 'x']);
    expect($value)
        ->toBe('{"a":1,"b":"x"}');
});

test('Test creating a nested store', function() {
    $value = DatastarFunctions::datastarStore(['a' => 1, 'b' => ['c' => 2, 'd' => 3]]);
    expect($value)
        ->toBe('{"a":1,"b":{"c":2,"d":3}}');
});

test('Test that creating a store containing an object throws an exception', function() {
    DatastarFunctions::datastarStore(['a' => 1, 'b' => new stdClass()]);
})->throws(SyntaxError::class);

test('Test that creating a nested store containing an object throws an exception', function() {
    DatastarFunctions::datastarStore(['a' => 1, 'b' => ['c' => 2, 'd' => new stdClass()]]);
})->throws(SyntaxError::class);
