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
    public function actionIndex(): ?Response
    {
        $hashedConfig = $this->request->getParam('config');
        $config = ConfigModel::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }
        Craft::$app->getSites()->setCurrentSite($config->siteId);

        $route = $config->route;
        $params = $config->params;
        $actionPrefix = Craft::$app->getConfig()->getGeneral()->actionTrigger . '/';

        if (str_starts_with($route, $actionPrefix)) {
            $route = substr($route, strlen($actionPrefix));

            return Craft::$app->runAction($route, $params);
        }

        return Datastar::getInstance()->sse->getEventStream(function() use ($route, $params) {
            Datastar::getInstance()->sse->renderDatastarTemplate($route, $params);
        });
    }
}
