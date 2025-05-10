<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\web;

use craft\web\Response;

class StreamedResponse extends Response
{
    /**
     * Resends headers, if not already sent.
     */
    public function resendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        parent::sendHeaders();
    }
}
