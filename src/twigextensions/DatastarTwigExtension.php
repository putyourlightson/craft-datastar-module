<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions;

use putyourlightson\datastar\twigextensions\tokenparsers\ExecuteScriptTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\LocationTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\PatchElementsTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\PatchSignalsTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\RemoveElementsTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\SleepTokenParser;
use putyourlightson\datastar\variables\DatastarVariable;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class DatastarTwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @inerhitdoc
     */
    public function getGlobals(): array
    {
        return [
            'datastar' => new DatastarVariable(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTokenParsers(): array
    {
        return [
            new PatchElementsTokenParser(),
            new RemoveElementsTokenParser(),
            new PatchSignalsTokenParser(),
            new ExecuteScriptTokenParser(),
            new LocationTokenParser(),
            new SleepTokenParser(),
        ];
    }
}
