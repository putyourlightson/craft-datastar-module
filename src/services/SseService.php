<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\SignalsModel;
use putyourlightson\datastar\web\StreamedResponse;
use starfederation\datastar\events\EventInterface;
use starfederation\datastar\events\ExecuteScript;
use starfederation\datastar\events\Location;
use starfederation\datastar\events\PatchElements;
use starfederation\datastar\events\PatchSignals;
use starfederation\datastar\events\RemoveElements;
use starfederation\datastar\ServerSentEventGenerator;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SseService extends Component
{
    /**
     * The response data.
     */
    private string $responseData = '';

    /**
     * Whether to send SSE events when processing them.
     */
    private bool $sendSseEvents = true;

    /**
     * The server sent event method currently in process.
     */
    private ?string $sseMethodInProcess = null;

    /**
     * Returns a streamed response.
     */
    public function getStreamedResponse(?callable $callable = null): StreamedResponse
    {
        $response = Datastar::getInstance()->streamedResponse;
        Craft::$app->set('response', $response);

        $response->stream = function() use ($callable) {
            if ($callable !== null) {
                $callable();
            }

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
     * Returns the response data.
     */
    public function getResponseData(): string
    {
        return $this->responseData;
    }

    /**
     * Returns a signals model populated with signals passed into the request.
     */
    public function getSignals(): SignalsModel
    {
        return new SignalsModel(ServerSentEventGenerator::readSignals());
    }

    /**
     * Patches elements into the DOM.
     */
    public function patchElements(string $data, array $options = [], bool $send = true): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultElementOptions,
            $options,
        );
        $event = new PatchElements($data, $options);

        $this->processEvent($event, $send);
    }

    /**
     * Removes elements from the DOM.
     */
    public function removeElements(string $selector, array $options = [], bool $send = true): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultElementOptions,
            $options,
            ['mode' => 'remove']
        );
        $event = new PatchElements($selector, $options);

        $this->processEvent($event, $send);
    }

    /**
     * Patches signals.
     */
    public function patchSignals(array $signals, array $options = [], bool $send = true): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultSignalOptions,
            $options,
        );
        $event = new PatchSignals($signals, $options);

        $this->processEvent($event, $send);
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = [], bool $send = true): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $options,
        );

        $event = new ExecuteScript($script, $options);

        $this->processEvent($event, $send);
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = [], bool $send = true): void
    {
        $options = $this->mergeEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $options,
        );

        $event = new Location($uri, $options);

        $this->processEvent($event, $send);
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
    public function renderDatastarTemplate(string $template, array $variables = [], bool $sendSseEvents = true): void
    {
        if (!Craft::$app->getView()->doesTemplateExist($template)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        $signals = $this->getSignals();
        $variables = array_merge(
            [Datastar::getInstance()->settings->signalsVariableName => $signals],
            $variables,
        );

        $originalSendSseEvents = $this->sendSseEvents;
        $this->sendSseEvents = $sendSseEvents;

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

        $this->sendSseEvents = $originalSendSseEvents;
    }

    /**
     * Sets the server sent event method currently in process.
     */
    public function setSseInProcess(?string $method): void
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
     * Processes an event.
     */
    private function processEvent(EventInterface $event, bool $send): void
    {
        $this->verifySseMethodInProcess($event);

        Datastar::getInstance()->streamedResponse->resendHeaders();

        $shouldSend = $this->sendSseEvents && $send;

        if ($shouldSend) {
            // Clean and end all existing output buffers.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $output = $event->getOutput();

        if ($shouldSend) {
            echo $output;

            if (ob_get_contents()) {
                ob_end_flush();
            }
            flush();
        }

        // Append the resulting output to the response data.
        $this->responseData .= $event->getOutput();

        if ($shouldSend) {
            // Start a new output buffer to capture any subsequent inline content.
            ob_start();
        }

        $this->setSseInProcess(null);
    }

    /**
     * Verifies that another SSE method is not already in process.
     */
    private function verifySseMethodInProcess(EventInterface $event): void
    {
        if ($this->sseMethodInProcess === null) {
            return;
        }

        $sseMethods = [
            PatchElements::class => 'patchElements',
            RemoveElements::class => 'removeElements',
            PatchSignals::class => 'patchSignals',
            ExecuteScript::class => 'executeScript',
        ];

        $method = $sseMethods[$event::class] ?? null;
        if ($method === null) {
            return;
        }

        if ($method !== $this->sseMethodInProcess) {
            $message = 'The SSE method `' . $method . '` cannot be called when `' . $this->sseMethodInProcess . '` is already in process.';
            if (in_array($method, ['mergeSignals', 'removeSignals'])) {
                $message .= ' Ensure that you are not setting or removing signals inside `{% fragment %}` or `{% executescript %}` tags.';
            }
            $this->throwException($message);
        }
    }
}
