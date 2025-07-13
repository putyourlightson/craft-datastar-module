<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\helpers\ActionHelper;
use yii\web\Response;

class DatastarVariable
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('get', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('post', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('put', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('patch', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return ActionHelper::getAction('delete', $template, $variables, $options);
    }

    /**
     * Returns the signals passed into the request.
     */
    public function getSignals(): array
    {
        return Datastar::getInstance()->request->getSignals();
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->request->runAction($route, $params);
    }
}
