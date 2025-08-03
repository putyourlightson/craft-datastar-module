<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\traits;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\Sse;

trait SseTrait
{
    /**
     * Returns the `SseService` instance.
     */
    protected function sse(): Sse
    {
        return Datastar::getInstance()->sse;
    }
}
