<?php

/**
 * Tests the SSE service.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use yii\web\BadRequestHttpException;

beforeEach(function() {
    Datastar::getInstance()->set('sse', SseService::class);
    Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@putyourlightson/datastar/test/templates'));
});

test('Test that elements output in templates are patched', function(string $template) {
    Datastar::getInstance()->sse->renderDatastarTemplate($template, [], false);

    expect(Datastar::getInstance()->sse->getResponseData())
        ->toContain('data: elements <div>test</div>');
})->with([
    'html',
    'sse',
    'mixed',
]);

test('Test that calling an SSE method when another one is in process throws an exception', function() {
    Datastar::getInstance()->sse->setSseInProcess('patchElements');
    Datastar::getInstance()->sse->patchSignals([]);
})->throws(BadRequestHttpException::class);
