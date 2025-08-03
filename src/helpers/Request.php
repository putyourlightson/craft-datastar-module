<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\helpers;

use Craft;
use starfederation\datastar\ServerSentEventGenerator;
use yii\web\Response;

class Request
{
    /**
     * Reads and returns the signals passed into the request.
     */
    public static function readSignals(): array
    {
        return ServerSentEventGenerator::readSignals();
    }

    /**
     * Runs an action and returns the response.
     */
    public static function runAction(string $route, array $params = []): Response
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
