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
    public function sse(string $template, array $options = []): string
    {
        $url = Datastar::getInstance()->sse->getUrl($template, $options);
        unset($options['variables']);

        $args = ["'$url'"];
        if (!empty($options)) {
            $args[] = json_encode($options);
        }

        return 'sse(' . implode(', ', $args) . ')';
    }

    /**
     * Returns a Datastar URL endpoint.
     */
    public function getUrl(string $template, array $options = []): string
    {
        return Datastar::getInstance()->sse->getUrl($template, $options);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->sse->runAction($route, $params);
    }
}
