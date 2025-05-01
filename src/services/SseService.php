<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\SignalsModel;
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
     * Returns a streamed response.
     */
    public function getStreamedResponse(callable $callable): Response
    {
        $response = Craft::$app->getResponse();

        $response->stream = function() use ($callable) {
            $callable();

            // Return an array to prevent Yii from throwing an exception.
            return [];
        };

        $response->format = Response::FORMAT_RAW;

        // Set headers defined in `ServerSentEventGenerator` that are not already set.
        $headers = $response->getHeaders();
        foreach (ServerSentEventGenerator::headers() as $name => $value) {
            if (!$headers->has($name)) {
                $headers->set($name, $value);
            }
        }

        return $response;
    }

    /**
     * Returns a signals model populated with signals passed into the request.
     */
    public function getSignals(): SignalsModel
    {
        return new SignalsModel(ServerSentEventGenerator::readSignals());
    }

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
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $options,
        );

        $this->sendSseEvent('location', $uri, $options);
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
     * Renders a Datastar template.
     */
    public function renderDatastarTemplate(string $template, array $variables = []): void
    {
        if (!Craft::$app->getView()->doesTemplateExist($template)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        $signals = $this->getSignals();
        $variables = array_merge(
            [Datastar::getInstance()->settings->signalsVariableName => $signals],
            $variables,
        );

        $request = Craft::$app->getRequest();

        if (strtolower($request->getContentType()) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            $request->setQueryParams([]);
            $request->setBodyParams([]);
        }

        try {
            Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }
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

        $this->sendHeaders();

        // Clean and end all existing output buffers.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->getSseGenerator()->$method(...$args);

        $this->sseMethodInProcess = null;

        // Start a new output buffer to capture any subsequent inline content.
        ob_start();
    }

    /**
     * Sends response headers that may have been set by the rendered Twig template.
     *
     * @see Response::sendHeaders()
     */
    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach (Craft::$app->getResponse()->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $replace = true;
            foreach ($values as $value) {
                header("$name: $value", $replace);
                $replace = false;
            }
        }
    }
}
