<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\helpers\DatastarHelper;
use putyourlightson\datastar\helpers\RequestHelper;
use yii\web\Response;

class DatastarVariable
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $route, array $variables = [], array $options = []): string
    {
        return DatastarHelper::getAction('get', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $route, array $variables = [], array $options = []): string
    {
        return DatastarHelper::getAction('post', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $route, array $variables = [], array $options = []): string
    {
        return DatastarHelper::getAction('put', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $route, array $variables = [], array $options = []): string
    {
        return DatastarHelper::getAction('patch', $route, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $route, array $variables = [], array $options = []): string
    {
        return DatastarHelper::getAction('delete', $route, $variables, $options);
    }

    /**
     * Reads and returns the signals passed into the request.
     */
    public function readSignals(): array
    {
        return RequestHelper::readSignals();
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return RequestHelper::runAction($route, $params);
    }

    /**
     * Sets server sent event options.
     */
    public function setSseEventOptions(array $options = []): void
    {
        Datastar::getInstance()->sse->setSseEventOptions($options);
    }
}
