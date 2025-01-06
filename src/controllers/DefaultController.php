<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\models\ConfigModel;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    protected int|bool|array $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if ($this->request->getIsCpRequest() && !Craft::$app->getUser()->getIdentity()->can('accessCp')) {
            throw new ForbiddenHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Default controller action.
     */
    public function actionIndex(): Response
    {
        $this->response->stream = function() {
            return $this->stream();
        };

        Datastar::getInstance()->sse->prepareResponse($this->response);

        return $this->response;
    }

    /**
     * Streams the response.
     */
    protected function stream(): array
    {
        $hashedConfig = $this->request->getParam('config');
        $config = ConfigModel::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        Craft::$app->getSites()->setCurrentSite($config->siteId);

        $template = $config->template;
        $signals = Datastar::getInstance()->sse->getSignals();
        $variables = array_merge(
            [Datastar::getInstance()->settings->signalsVariableName => $signals],
            $config->variables,
        );

        if (strtolower($this->request->getContentType()) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            $this->request->setQueryParams([]);
            $this->request->setBodyParams([]);
        }

        Datastar::getInstance()->sse->renderTemplate($template, $variables);

        // Must return an array to prevent Yii from throwing an exception.
        return [];
    }
}
