<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\datastar\Datastar;
use starfederation\datastar\ServerSentEventGenerator;
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

    public function actionIndex(): Response
    {
        $config = $this->request->getParam('config');
        $signals = ServerSentEventGenerator::readSignals();

        if (strtolower($this->request->getContentType()) === 'application/json') {
            // Clear out params to prevent them from being processed by controller actions.
            $this->request->setQueryParams([]);
            $this->request->setBodyParams([]);
        }

        // Set the response headers for the event stream.
        foreach (ServerSentEventGenerator::HEADERS as $name => $value) {
            $this->response->getHeaders()->set($name, $value);
        }

        $this->response->format = Response::FORMAT_RAW;

        // Stream the response.
        $this->response->stream = function() use ($config, $signals) {
            return Datastar::getInstance()->sse->stream($config, $signals);
        };

        return $this->response;
    }
}
