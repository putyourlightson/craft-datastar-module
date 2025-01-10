<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\variables;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Request;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\ConfigModel;
use Twig\Error\SyntaxError;
use yii\web\Response;

class DatastarVariable
{
    /**
     * Returns a Datastar `@get` action.
     */
    public function get(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('get', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@post` action.
     */
    public function post(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('post', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@put` action.
     */
    public function put(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('put', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@patch` action.
     */
    public function patch(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('patch', $template, $variables, $options);
    }

    /**
     * Returns a Datastar `@delete` action.
     */
    public function delete(string $template, array $variables = [], array $options = []): string
    {
        return $this->getAction('delete', $template, $variables, $options);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->sse->runAction($route, $params);
    }

    /**
     * Returns a Datastar action.
     */
    private function getAction(string $method, string $template, array $variables = [], array $options = []): string
    {
        $url = $this->getUrl($template, $variables);
        $args = ["'$url'"];

        if ($method !== 'get') {
            $headers = $options['headers'] ?? [];
            $headers[Request::CSRF_HEADER] = Craft::$app->getRequest()->getCsrfToken();
            $options['headers'] = $headers;
        }

        if (!empty($options)) {
            $args[] = Json::encode($options);
        }

        $args = implode(', ', $args);

        return "@$method($args)";
    }

    /**
     * Returns a Datastar URL endpoint.
     */
    private function getUrl(string $template, array $variables = []): string
    {
        $config = new ConfigModel([
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
            'template' => $template,
            'variables' => $variables,
        ]);

        if (!$config->validate()) {
            throw new SyntaxError(implode(' ', $config->getFirstErrors()));
        }

        return UrlHelper::actionUrl('datastar-module', [
            'config' => $config->getHashed(),
        ]);
    }
}
