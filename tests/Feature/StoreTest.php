<?php
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpUndefinedMethodInspection */

/**
 * Tests the store.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\StoreModel;
use putyourlightson\datastar\services\ResponseService;

beforeEach(function() {
    $response = Mockery::mock(ResponseService::class);
    Datastar::getInstance()->set('response', $response);
});

test('Test getting a value from the store', function() {
    $store = new StoreModel(['a' => 1]);
    expect($store->get('a'))
        ->toBe(1);
});

test('Test getting a value from the store using a magic call', function() {
    $store = new StoreModel(['a' => 1]);
    expect($store->a)
        ->toBe(1);
});

test('Test getting a nested value from the store', function() {
    $store = new StoreModel(['a' => ['b' => ['c' => 1]]]);
    expect($store->get('a.b.c'))
        ->toBe(1);
});

test('Test getting a missing value from the store', function() {
    $store = new StoreModel(['a' => 1]);
    expect($store->get('x'))
        ->toBeNull();
});

test('Test getting a missing value from the store using a magic call', function() {
    $store = new StoreModel(['a' => 1]);
    expect($store->x)
        ->toBeNull();
});

test('Test adding a value to the store', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel([]);
    $store->set('a', 1);
    expect($store->get('a'))
        ->toBe(1);
});

test('Test adding a value to the store using a magic call', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel([]);
    $store->a(1);
    expect($store->get('a'))
        ->toBe(1);
});

test('Test modifying an existing value in the store', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel(['a' => 1]);
    $store->set('a', 2);
    expect($store->get('a'))
        ->toBe(2);
});

test('Test modifying an existing value in the store using a magic call', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel(['a' => 1]);
    $store->a(2);
    expect($store->get('a'))
        ->toBe(2);
});

test('Test adding a nested value to the store', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel([]);
    $store->set('a.b.c', 1);
    expect($store->get('a.b.c'))
        ->toBe(1);
});

test('Test modifying an existing nested value in the store', function() {
    Datastar::getInstance()->response->shouldReceive('mergeSignals');
    $store = new StoreModel(['a' => ['b' => ['c' => 1]]]);
    $store->set('a.b.c', 2);
    expect($store->get('a.b.c'))
        ->toBe(2);
});

test('Test removing a value from the store', function() {
    Datastar::getInstance()->response->shouldReceive('removeSignals');
    $store = new StoreModel(['a' => 1]);
    $store->remove('a');
    expect($store->getValues())
        ->toBe([]);
});

test('Test removing a nested value from the store', function() {
    Datastar::getInstance()->response->shouldReceive('removeSignals');
    $store = new StoreModel(['a' => ['b' => ['c' => 1]]]);
    $store->remove('a.b.c');
    expect($store->getValues())
        ->toBe(['a' => ['b' => []]]);
});
