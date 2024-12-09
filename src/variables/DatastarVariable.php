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
     * Returns a Datastar SSE action using a GET request.
     */
    public function get(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction($template, $variables, $options);
    }

    /**
     * Returns a Datastar SSE action using a POST request.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction($template, $variables, $options, 'post');
    }

    /**
     * Returns a Datastar SSE action using a PUT request.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction($template, $variables, $options, 'put');
    }

    /**
     * Returns a Datastar SSE action using a PATCH request.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction($template, $variables, $options, 'patch');
    }

    /**
     * Returns a Datastar SSE action using a DELETE request.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction($template, $variables, $options, 'delete');
    }

    /**
     * Returns a Datastar URL endpoint.
     */
    public function getUrl(string $template, array $variables = [], string $method = 'get'): string
    {
        return Datastar::getInstance()->sse->getUrl($template, $variables, $method);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->sse->runAction($route, $params);
    }

    /**
     * Returns a Datastar SSE action.
     */
    private function getAction(string $template, array $variables = [], array $options = [], string $method = 'get'): string
    {
        if ($method !== 'get') {
            $options['method'] = $method;
        }

        $url = Datastar::getInstance()->sse->getUrl($template, $variables, $method);
        $args = ["'$url'"];
        if (!empty($options)) {
            $args[] = json_encode($options);
        }

        return 'sse(' . implode(', ', $args) . ')';
    }
}
