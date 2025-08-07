<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use craft\web\Response;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\helpers\Request;
use starfederation\datastar\events\EventInterface;
use starfederation\datastar\events\ExecuteScript;
use starfederation\datastar\events\Location;
use starfederation\datastar\events\PatchElements;
use starfederation\datastar\events\PatchSignals;
use starfederation\datastar\events\RemoveElements;
use starfederation\datastar\ServerSentEventGenerator;
use Throwable;
use yii\web\BadRequestHttpException;

class Sse extends Component
{
    /**
     * Whether the response is a streamed response.
     */
    private bool $isStreamedResponse = false;

    /**
     * Whether the session should be closed when the event stream begins.
     * This is useful to allow other requests to be processed while the event stream is being sent.
     */
    private bool $closeSession = true;

    /**
     * Server sent events to send.
     *
     * @var EventInterface[]
     */
    private array $sseEvents = [];

    /**
     * Server sent event options to send.
     */
    private array $sseEventOptions = [];

    /**
     * The server sent event method currently in process.
     */
    private ?string $sseMethodInProcess = null;

    /**
     * Returns an event stream.
     */
    public function getEventStream(?callable $callable = null): Response
    {
        // Abort the process if the client closes the connection.
        ignore_user_abort(false);

        $this->isStreamedResponse = true;

        /** @var Response $response */
        $response = Craft::$app->getResponse();

        $response->stream = function() use ($callable) {
            if ($this->closeSession && session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            echo $this->getEventOutput();
            ob_flush();
            flush();

            if (is_callable($callable)) {
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
     * Returns the output of all events as a string.
     */
    public function getEventOutput(bool $reset = true): string
    {
        $data = '';
        foreach ($this->sseEvents as $event) {
            $data .= $event->getOutput();
        }

        if ($reset) {
            $this->resetEvents();
        }

        return $data;
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return Request::readSignals();
    }

    /**
     * Patches elements into the DOM.
     */
    public function patchElements(string $data, array $options = []): static
    {
        $options = $this->patchEventOptions(
            Datastar::getInstance()->settings->defaultElementOptions,
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchElements($data, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Removes elements from the DOM.
     */
    public function removeElements(string $selector, array $options = []): static
    {
        $options = $this->patchEventOptions(
            Datastar::getInstance()->settings->defaultElementOptions,
            $this->sseEventOptions,
            $options,
        );
        $event = new RemoveElements($selector, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Patches signals.
     */
    public function patchSignals(array $signals, array $options = []): static
    {
        $options = $this->patchEventOptions(
            Datastar::getInstance()->settings->defaultSignalOptions,
            $this->sseEventOptions,
            $options,
        );
        $event = new PatchSignals($signals, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): static
    {
        $options = $this->patchEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $this->sseEventOptions,
            $options,
        );

        $event = new ExecuteScript($script, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Redirects the browser by setting the location to the provided URI.
     */
    public function location(string $uri, array $options = []): static
    {
        $options = $this->patchEventOptions(
            Datastar::getInstance()->settings->defaultExecuteScriptOptions,
            $this->sseEventOptions,
            $options,
        );

        $event = new Location($uri, $options);

        $this->processEvent($event);

        return $this;
    }

    /**
     * Renders a template.
     */
    public function renderTemplate(string $template, array $variables = []): static
    {
        if (!Craft::$app->getView()->doesTemplateExist($template)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        $signals = $this->readSignals();
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
            $output = Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }

        if (trim($output) !== '') {
            $this->patchElements($output);
        }

        return $this;
    }

    /**
     * Resets the events.
     */
    public function resetEvents(): static
    {
        $this->sseEvents = [];

        return $this;
    }

    /**
     * Sets the value of the `closeSession` property.
     */
    public function closeSession(bool $value = true): static
    {
        $this->closeSession = $value;

        return $this;
    }

    /**
     * Sets server sent event options for the current request.
     */
    public function setSseEventOptions(array $options): static
    {
        $this->sseEventOptions = $options;

        return $this;
    }

    /**
     * Sets the server sent event method currently in process.
     */
    public function setSseMethodInProcess(?string $method): static
    {
        $this->sseMethodInProcess = $method;

        return $this;
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
     * Returns patch event options with null values removed.
     */
    private function patchEventOptions(array ...$optionSets): array
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
    private function processEvent(EventInterface $event): void
    {
        $this->verifySseMethodInProcess($event);

        $this->sseEvents[] = $event;

        if ($this->isStreamedResponse) {
            // Clean and end all existing output buffers.
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo $event->getOutput();

            if (ob_get_contents()) {
                ob_end_flush();
            }
            flush();

            // Start a new output buffer to capture any subsequent inline content.
            ob_start();
        }

        $this->setSseMethodInProcess(null);
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
            if ($method === 'patchElements') {
                $message .= ' Ensure that you are not setting or removing signals inside `{% patchelements %}` or `{% executescript %}` tags.';
            }
            $this->throwException($message);
        }
    }
}
