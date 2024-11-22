<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\ConsoleModel;
use yii\web\Response;

class DatastarGlobal
{
    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->response->runAction($route, $params);
    }

    /**
     * Removes all elements that match the selector from the DOM.
     */
    public function remove(string $selector): void
    {
        Datastar::getInstance()->events->remove($selector);
    }

    /**
     * Redirects the page to the provided URI.
     */
    public function redirect(string $uri): void
    {
        Datastar::getInstance()->events->redirect($uri);
    }

    /**
     * Returns a console variable for logging messages to the browser console.
     */
    public function console(): ConsoleModel
    {
        return new ConsoleModel();
    }
}
