<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\models;

use putyourlightson\datastar\Datastar;

class ConsoleModel
{
    public function debug(string $message): void
    {
        $this->console($message, 'debug');
    }

    public function error(string $message): void
    {
        $this->console($message, 'error');
    }

    public function info(string $message): void
    {
        $this->console($message, 'info');
    }

    public function group(string $message): void
    {
        $this->console($message, 'group');
    }

    public function groupEnd(string $message): void
    {
        $this->console($message, 'groupEnd');
    }

    public function log(string $message): void
    {
        $this->console($message, 'log');
    }

    public function warn(string $message): void
    {
        $this->console($message, 'warn');
    }

    private function console(string $message, string $mode): void
    {
        Datastar::getInstance()->events->console($message, $mode);
    }
}
