<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\datastar\DatastarEventStream;
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
        return $this->getStreamedResponse(function() {
            $hashedConfig = $this->request->getParam('config');
            $config = ConfigModel::fromHashed($hashedConfig);
            if ($config === null) {
                $this->throwException('Submitted data was tampered.');
            }

            Craft::$app->getSites()->setCurrentSite($config->siteId);

            $this->renderDatastarTemplate($config->template, $config->variables);
        });
    }
}
