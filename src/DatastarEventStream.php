<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar;

use Craft;
use putyourlightson\datastar\models\SignalsModel;
use starfederation\datastar\ServerSentEventGenerator;
use Throwable;
use yii\web\Response;

trait DatastarEventStream
{
    /**
     * Returns a streamed response.
     */
    protected function getStreamedResponse(callable $callable): Response
    {
        $response = new Response();

        $response->stream = function() use ($callable) {
            $callable();

            // Return an array to prevent Yii from throwing an exception.
            return [];
        };

        $response->format = Response::FORMAT_RAW;

        foreach (ServerSentEventGenerator::headers() as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    /**
     * Returns a signals model populated with signals passed into the request.
     */
    protected function getSignals(): SignalsModel
    {
        return new SignalsModel(ServerSentEventGenerator::readSignals());
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
     *
     * @used-by ExecuteScriptNode
     */
    protected function executeScript(string $script, array $options = []): void
    {
        Datastar::getInstance()->sse->executeScript($script, $options);
    }

    /**
     * Renders a template, catching exceptions.
     */
    protected function renderDatastarTemplate(string $template, array $variables): void
    {
        if (!Craft::$app->getView()->doesTemplateExist($template)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        try {
            Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }
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
