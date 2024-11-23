<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions;

use putyourlightson\datastar\Datastar;
use yii\web\Response;

class DatastarGlobal
{
    /**
     * Merges HTML fragments into the DOM.
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        Datastar::getInstance()->response->mergeFragments($data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    public function removeFragments(string $selector, array $options = []): void
    {
        Datastar::getInstance()->response->removeFragments($selector, $options);
    }

    /**
     * Merges signals into the store.
     */
    public function mergeSignals(array $signals, array $options = []): void
    {
        Datastar::getInstance()->response->mergeSignals($signals, $options);
    }

    /**
     * Removes signal paths from the store.
     */
    public function removeSignals(array $paths): void
    {
        Datastar::getInstance()->response->removeSignals($paths);
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): void
    {
        Datastar::getInstance()->response->executeScript($script, $options);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        return Datastar::getInstance()->response->runAction($route, $params);
    }
}
