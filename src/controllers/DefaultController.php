<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\datastar\DatastarEventStream;
use putyourlightson\datastar\models\ConfigModel;
use yii\web\BadRequestHttpException;
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
    public function actionIndex(): ?Response
    {
        $hashedConfig = $this->request->getParam('config');
        $config = ConfigModel::fromHashed($hashedConfig);
        if ($config === null) {
            throw new BadRequestHttpException('Submitted data was tampered.');
        }

        Craft::$app->getSites()->setCurrentSite($config->siteId);

        if (str_starts_with($config->route, 'actions/')) {
            $route = substr($config->route, strlen('actions/'));

            return Craft::$app->runAction($route, $config->params);
        }

        return $this->getStreamedResponse(function() use ($config) {
            $this->renderDatastarTemplate($config->route, $config->params);
        });
    }
}
