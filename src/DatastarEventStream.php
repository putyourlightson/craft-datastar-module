<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use putyourlightson\datastar\models\SignalsModel;
use Throwable;
use yii\web\Response;

trait DatastarEventStream
{
    /**
     * Returns a streamed response.
     */
    protected function getStreamedResponse(callable $callable): Response
    {
        return Datastar::getInstance()->sse->getStreamedResponse($callable);
    }

    /**
     * Returns a signals model populated with signals passed into the request.
     */
    protected function getSignals(): SignalsModel
    {
        return Datastar::getInstance()->sse->getSignals();
    }

    /**
     * Merges HTML fragments into the DOM.
     */
    protected function mergeFragments(string $data, array $options = []): void
    {
        Datastar::getInstance()->sse->mergeFragments($data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    protected function removeFragments(string $selector, array $options = []): void
    {
        Datastar::getInstance()->sse->removeFragments($selector, $options);
    }

    /**
     * Merges signals.
     */
    protected function mergeSignals(array $signals, array $options = []): void
    {
        Datastar::getInstance()->sse->mergeSignals($signals, $options);
    }

    /**
     * Removes signal paths.
     */
    protected function removeSignals(array $paths, array $options = []): void
    {
        Datastar::getInstance()->sse->removeSignals($paths, $options);
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
