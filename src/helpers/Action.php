<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\helpers;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Request;
use putyourlightson\datastar\models\Config;
use Twig\Error\SyntaxError;

class Action
{
    /**
     * Returns a Datastar action.
     */
    public static function getAction(string $method, string $route, array $params = [], array $options = []): string
    {
        $url = self::getUrl($route, $params);
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
    public static function getUrl(string $route, array $params = []): string
    {
        $config = new Config([
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
            'route' => $route,
            'params' => $params,
        ]);

        if (!$config->validate()) {
            throw new SyntaxError(implode(' ', $config->getFirstErrors()));
        }

        return UrlHelper::actionUrl('datastar-module', [
            'config' => $config->getHashed(),
        ]);
    }
}
