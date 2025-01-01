<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use putyourlightson\datastar\Datastar;
use yii\web\Response;

class DatastarVariable
{
    /**
     * Returns a Datastar SSE action.
     */
    public function sse(string $template, array $variables = [], array $options = []): string
    {
        $method = $options['method'] ?? 'get';
        $includeCsrfToken = strtolower($method) !== 'get';

        $url = Datastar::getInstance()->sse->getUrl($template, $variables, $includeCsrfToken);

        $args = ["'$url'"];
        if (!empty($options)) {
            $args[] = json_encode($options);
        }

        return 'sse(' . implode(', ', $args) . ')';
    }

    /**
     * Returns a Datastar SSE action with a `get` request.
     */
    public function get(string $template, array $variables = [], array $options = []): string
    {
        return $this->sse($template, $variables, array_merge($options, ['method' => 'get']));
    }

    /**
     * Returns a Datastar SSE action with a `post` request.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return $this->sse($template, $variables, array_merge($options, ['method' => 'post']));
    }

    /**
     * Returns a Datastar SSE action with a `put` request.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return $this->sse($template, $variables, array_merge($options, ['method' => 'put']));
    }

    /**
     * Returns a Datastar SSE action with a `patch` request.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return $this->sse($template, $variables, array_merge($options, ['method' => 'patch']));
    }

    /**
     * Returns a Datastar SSE action with a `delete` request.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return $this->sse($template, $variables, array_merge($options, ['method' => 'delete']));
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->sse->runAction($route, $params);
    }
}
