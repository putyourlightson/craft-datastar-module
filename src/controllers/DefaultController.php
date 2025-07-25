<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\DatastarEventStream;
use putyourlightson\datastar\helpers\RequestHelper;
use putyourlightson\datastar\models\ConfigModel;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    use DatastarEventStream;

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
        return Datastar::getInstance()->sse->getStreamedResponse(function() {
            $hashedConfig = $this->request->getParam('config');
            $config = ConfigModel::fromHashed($hashedConfig);
            if ($config === null) {
                $this->throwException('Submitted data was tampered.');
            }

            Craft::$app->getSites()->setCurrentSite($config->siteId);

            $this->processRoute($config->route, $config->params);
        });
    }

    /**
     * Processes a route.
     */
    protected function processRoute(string $route, array $params = []): void
    {
        if (str_starts_with($route, 'actions/')) {
            $route = substr($route, strlen('actions/'));
            RequestHelper::runAction($route, $params);
        } else {
            Datastar::getInstance()->sse->renderDatastarTemplate($route, $params);
        }
    }
}
