<?php

/**
 * Tests the SSE service.
 */

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;

beforeEach(function() {
    Datastar::getInstance()->set('sse', SseService::class);
    Craft::$app->getView()->setTemplatesPath(Craft::getAlias('@putyourlightson/datastar/test/templates'));
});

test('Test that elements output in templates are patched', function(string $template) {
    Datastar::getInstance()->sse->renderTemplate($template);

    expect(Datastar::getInstance()->sse->getEventOutput())
        ->toContain('data: elements <div>test</div>');
})->with([
    'html',
    'sse',
    'mixed',
]);

test('Test remove elements tag', function() {
    Datastar::getInstance()->sse->renderTemplate('remove');

    expect(Datastar::getInstance()->sse->getEventOutput())
        ->toContain('data: mode remove');
});
