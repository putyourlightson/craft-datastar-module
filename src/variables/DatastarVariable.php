<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use putyourlightson\datastar\helpers\Action;
use putyourlightson\datastar\helpers\Request;
use putyourlightson\datastar\traits\Sse;
use yii\web\Response;

class DatastarVariable
{
    use Sse;

    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $route, array $variables = [], array $options = []): string
    {
        return Action::getAction('get', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $route, array $variables = [], array $options = []): string
    {
        return Action::getAction('post', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $route, array $variables = [], array $options = []): string
    {
        return Action::getAction('put', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $route, array $variables = [], array $options = []): string
    {
        return Action::getAction('patch', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $route, array $variables = [], array $options = []): string
    {
        return Action::getAction('delete', $route, $variables, $options);
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return Request::readSignals();
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Request::runAction($route, $params);
    }

    /**
     * Sets server sent event options.
     */
    public function setSseEventOptions(array $options = []): void
    {
        $this->sse()->setSseEventOptions($options);
    }
}
