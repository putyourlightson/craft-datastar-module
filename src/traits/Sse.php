<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\traits;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;

trait Sse
{
    /**
     * Returns the `SseService` instance.
     */
    protected function sse(): SseService
    {
        return Datastar::getInstance()->sse;
    }
}
