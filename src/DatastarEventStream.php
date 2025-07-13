<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use putyourlightson\datastar\web\StreamedResponse;
use Throwable;

trait DatastarEventStream
{
    /**
     * Returns a streamed response.
     */
    protected function getStreamedResponse(?callable $callable = null): StreamedResponse
    {
        return Datastar::getInstance()->sse->getStreamedResponse($callable);
    }

    /**
     * Returns the signals passed into the request.
     */
    protected function getSignals(): array
    {
        return Datastar::getInstance()->request->getSignals();
    }

    /**
     * Patches elements into the DOM.
     */
    protected function patchElements(string $data, array $options = []): void
    {
        Datastar::getInstance()->sse->patchElements($data, $options);
    }

    /**
     * Removes elements from the DOM.
     */
    protected function removeElements(string $selector, array $options = []): void
    {
        Datastar::getInstance()->sse->removeElements($selector, $options);
    }

    /**
     * Patches signals.
     */
    protected function patchSignals(array $signals, array $options = []): void
    {
        Datastar::getInstance()->sse->patchSignals($signals, $options);
    }

    /**
     * Executes JavaScript in the browser.
     */
    protected function executeScript(string $script, array $options = []): void
    {
        Datastar::getInstance()->sse->executeScript($script, $options);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    protected function location(string $uri, array $options = []): void
    {
        Datastar::getInstance()->sse->location($uri, $options);
    }

    /**
     * Renders a Datastar template.
     */
    protected function renderDatastarTemplate(string $template, array $variables = []): void
    {
        Datastar::getInstance()->sse->renderDatastarTemplate($template, $variables);
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    public function throwException(Throwable|string $exception): void
    {
        Datastar::getInstance()->sse->throwException($exception);
    }
}
