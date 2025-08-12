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
    public static function getAction(string $method, string $route, array $params = [], array|string $options = []): string
    {
        $url = self::getUrl($route, $params);
        $args = ['"' . $url . '"'];

        if ($method !== 'get') {
            $options = self::addCsrfToken($options);
        } else {
            $options = is_array($options) ? Json::encode($options) : $options;
        }

        if ($options !== '{}') {
            $args[] = $options;
        }

        $args = implode(', ', $args);

        return '@' . $method . '(' . $args . ')';
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

    private static function addCsrfToken(array|string $options): string
    {
        $csrfHeader = Request::CSRF_HEADER;
        $token = Craft::$app->getRequest()->getCsrfToken();

        if (is_array($options)) {
            return self::addCsrfToArray($options, $csrfHeader, $token);
        }

        return self::addCsrfToString($options, $csrfHeader, $token);
    }

    private static function addCsrfToArray(array $options, string $csrfHeader, string $token): string
    {
        $headers = $options['headers'] ?? [];
        $headers[$csrfHeader] = $token;
        $options['headers'] = $headers;

        return Json::encode($options);
    }

    private static function addCsrfToString(string $options, string $csrfHeader, string $token): string
    {
        if (preg_match('/headers:\s*\{/i', $options)) {
            return preg_replace(
                '/headers:\s*\{/i',
                'headers: {"' . $csrfHeader . '": "' . $token . '", ',
                $options
            );
        }

        if (preg_match('/}\s*$/', $options)) {
            return preg_replace(
                '/}\s*$/',
                ', headers: {"' . $csrfHeader . '": "' . $token . '"}}',
                $options
            );
        }

        return Json::encode(['headers' => [$csrfHeader => $token]]);
    }
}
