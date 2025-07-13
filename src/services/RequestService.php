<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\services;

use Craft;
use craft\base\Component;
use starfederation\datastar\ServerSentEventGenerator;
use yii\web\Response;

class RequestService extends Component
{
    /**
     * Returns the signals passed into the request.
     */
    public function getSignals(): array
    {
        return ServerSentEventGenerator::readSignals();
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        $request = Craft::$app->getRequest();
        $request->getHeaders()->set('Accept', 'application/json');

        if ($request->getIsGet()) {
            $requestParams = $request->getQueryParams();
            $request->setQueryParams(array_merge($requestParams, $params));
        } else {
            $requestParams = $request->getBodyParams();
            $request->setBodyParams(array_merge($requestParams, $params));
        }

        $response = Craft::$app->runAction($route);

        if ($request->getIsGet()) {
            $request->setQueryParams($requestParams);
        } else {
            $request->setBodyParams($requestParams);
        }

        return $response;
    }
}
