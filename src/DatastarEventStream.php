<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use putyourlightson\datastar\helpers\RequestHelper;
use putyourlightson\datastar\web\StreamedResponse;
use Throwable;

trait DatastarEventStream
{
    /**
     * Returns an event stream.
     */
    protected function getEventStream(?callable $callable = null): StreamedResponse
    {
        return Datastar::getInstance()->sse->getEventStream($callable);
    }

    /**
     * Returns the output of all events as a string.
     */
    protected function getEventOutput(): string
    {
        return Datastar::getInstance()->sse->getEventOutput();
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    protected function readSignals(): array
    {
        return RequestHelper::readSignals();
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
    protected function throwException(Throwable|string $exception): void
    {
        Datastar::getInstance()->sse->throwException($exception);
    }
}
