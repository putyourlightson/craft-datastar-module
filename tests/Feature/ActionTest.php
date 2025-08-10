<?php

/**
 * Tests the Datastar action helper.
 */

use craft\web\Request;
use putyourlightson\datastar\helpers\Action;

test('Test creating an action', function(string $method) {
    $value = Action::getAction($method, 'test');
    expect($value)
        ->toStartWith("@$method(")
        ->toContain('test');

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
    $params = ['x' => 1, 'y' => 'string', 'z' => true];
    $value = Action::getAction('get', 'test', $params);
    $expected = str_replace(['%7B', '%7D'], ['{', '}'], urlencode(json_encode($params)));

    expect($value)
        ->toContain($expected);
});

test('Test creating an action containing an array of options', function() {
    $options = ['foo' => 'bar'];
    $value = Action::getAction('get', 'test', [], $options);
    expect($value)
        ->toContain(json_encode($options));
});

test('Test creating an action containing an options string', function() {
    $options = '{foo: "bar"}';
    $value = Action::getAction('get', 'test', [], $options);

    expect($value)
        ->toContain($options);
});
