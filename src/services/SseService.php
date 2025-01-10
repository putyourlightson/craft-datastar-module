<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\twigextensions\nodes\ExecuteScriptNode;
use starfederation\datastar\ServerSentEventGenerator;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SseService extends Component
{
    /**
     * The server sent event generator.
     */
    private ServerSentEventGenerator|null $sseGenerator = null;

    /**
     * The server sent event method currently in process.
     */
    private ?string $sseMethodInProcess = null;

    /**
     * Merges HTML fragments into the DOM.
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultFragmentOptions,
            $options,
        );

        $this->sendSseEvent('mergeFragments', $data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    public function removeFragments(string $selector, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultFragmentOptions,
            $options,
        );

        $this->sendSseEvent('removeFragments', $selector, $options);
    }

    /**
     * Merges signals.
     */
    public function mergeSignals(array $signals, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultSignalOptions,
            $options,
        );

        $this->sendSseEvent('mergeSignals', $signals, $options);
    }

    /**
     * Removes signal paths.
     */
    public function removeSignals(array $paths, array $options = []): void
    {
        $this->sendSseEvent('removeSignals', $paths, $options);
    }

    /**
     * Executes JavaScript in the browser.
     *
     * @used-by ExecuteScriptNode
     */
    public function executeScript(string $script, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $options,
        );

        $this->sendSseEvent('executeScript', $script, $options);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        $request = Craft::$app->getRequest();
        $request->getHeaders()->set('Accept', 'application/json');

        if ($request->getIsGet()) {
            $requestParams = $request->getQueryParams();
            $request->setQueryParams(array_merge($requestParams, $params));
        } else {
            $requestParams = $request->getBodyParams();
            $request->setBodyParams(array_merge($requestParams, $params));
        }

        $response = Craft::$app->runAction($route);

        if ($request->getIsGet()) {
            $request->setQueryParams($requestParams);
        } else {
            $request->setBodyParams($requestParams);
        }

        return $response;
    }

    /**
     * Sets the server sent event method currently in process.
     */
    public function setSseInProcess(string $method): void
    {
        $this->sseMethodInProcess = $method;
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    public function throwException(Throwable|string $exception): void
    {
        Craft::$app->getRequest()->getHeaders()->set('Accept', 'text/html');
        Craft::$app->getResponse()->format = Response::FORMAT_HTML;

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }

    /**
     * Returns merged event options with null values removed.
     */
    private function mergeEventOptions(array ...$optionSets): array
    {
        $options = Datastar::getInstance()->settings->defaultEventOptions;

        foreach ($optionSets as $optionSet) {
            $options = array_merge($options, $optionSet);
        }

        return array_filter($options, fn($value) => $value !== null);
    }

    /**
     * Returns a server sent event generator.
     */
    private function getSseGenerator(): ServerSentEventGenerator
    {
        if ($this->sseGenerator === null) {
            $this->sseGenerator = new ServerSentEventGenerator();
        }

        return $this->sseGenerator;
    }

    /**
     * Sends an SSE event with arguments and cleans output buffers.
     */
    private function sendSseEvent(string $method, ...$args): void
    {
        if ($this->sseMethodInProcess && $this->sseMethodInProcess !== $method) {
            $message = 'The SSE method `' . $method . '` cannot be called when `' . $this->sseMethodInProcess . '` is already in process.';
            if (in_array($method, ['mergeSignals', 'removeSignals'])) {
                $message .= ' Ensure that you are not setting or removing signals inside `{% fragment %}` or `{% executescript %}` tags.';
            }
            $this->throwException($message);
        }

        // Clean and end all existing output buffers.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->getSseGenerator()->$method(...$args);

        $this->sseMethodInProcess = null;

        // Start a new output buffer to capture any subsequent inline content.
        ob_start();
    }
}
