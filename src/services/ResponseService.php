<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\ConfigModel;
use putyourlightson\datastar\models\StoreModel;
use starfederation\datastar\ServerSentEventGenerator;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ResponseService extends Component
{
    /**
     * The server sent event generator.
     */
    private ServerSentEventGenerator|null $sse = null;

    /**
     * The CSRF token to include in the request.
     */
    private ?string $csrfToken = null;

    /**
     * Merges HTML fragments into the DOM.
     */
    public function mergeFragments(string $data, array $options = []): void
    {
        // Merge and remove empty values
        $options = array_filter(array_merge(
            Datastar::getInstance()->settings->defaultFragmentOptions,
            $options
        ));

        $this->getSse()->mergeFragments($data, $options);
    }

    /**
     * Removes HTML fragments from the DOM.
     */
    public function removeFragments(string $selector, array $options = []): void
    {
        $this->getSse()->removeFragments($selector, $options);
    }

    /**
     * Merges signals into the store.
     */
    public function mergeSignals(array $signals, array $options = []): void
    {
        $this->getSse()->mergeSignals(Json::encode($signals), $options);
    }

    /**
     * Removes signal paths from the store.
     */
    public function removeSignals(array $paths): void
    {
        $this->getSse()->removeSignals($paths);
    }

    /**
     * Executes JavaScript in the browser.
     */
    public function executeScript(string $script, array $options = []): void
    {
        $this->getSse()->executeScript($script, $options);
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        $request = Craft::$app->getRequest();
        $request->getHeaders()->set('Accept', 'application/json');

        if ($this->csrfToken !== null) {
            $params[$request->csrfParam] = $this->csrfToken;
        }

        if ($request->getIsGet()) {
            $request->setQueryParams($params);
        } else {
            $request->setBodyParams($params);
        }

        $response = Craft::$app->runAction($route);

        $request->setQueryParams([]);
        $request->setBodyParams([]);

        return $response;
    }

    /**
     * Streams the response and returns an empty array.
     */
    public function stream(string $config, array $store): array
    {
        $config = $this->getConfigForResponse($config);
        Craft::$app->getSites()->setCurrentSite($config->siteId);
        $this->csrfToken = $config->csrfToken;

        $store = new StoreModel($store);
        $variables = array_merge(
            [Datastar::getInstance()->settings->storeVariableName => $store],
            $config->variables,
        );

        $this->renderTemplate($config->template, $variables);

        return [];
    }

    private function getSse(): ServerSentEventGenerator
    {
        if ($this->sse === null) {
            $this->sse = new ServerSentEventGenerator();
        }

        return $this->sse;
    }

    private function getConfigForResponse(string $config): ConfigModel
    {
        $data = Craft::$app->getSecurity()->validateData($config);
        if ($data === false) {
            $this->throwException('Submitted data was tampered.');
        }

        return new ConfigModel(Json::decodeIfJson($data));
    }

    private function renderTemplate(string $template, array $variables): string
    {
        if (!Craft::$app->getView()->doesTemplateExist($template)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        try {
            return Craft::$app->getView()->renderTemplate($template, $variables);
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    private function throwException(Throwable|string $exception): void
    {
        Craft::$app->getRequest()->getHeaders()->set('Accept', 'text/html');
        Craft::$app->getResponse()->format = Response::FORMAT_HTML;

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }
}
