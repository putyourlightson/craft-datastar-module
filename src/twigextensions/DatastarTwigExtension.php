<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions;

use putyourlightson\datastar\twigextensions\tokenparsers\ExecuteScriptTokenParser;
use putyourlightson\datastar\twigextensions\tokenparsers\FragmentTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class DatastarTwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('datastar', [DatastarFunctions::class, 'datastar']),
            new TwigFunction('datastarUrl', [DatastarFunctions::class, 'datastarUrl']),
            new TwigFunction('datastarStore', [DatastarFunctions::class, 'datastarStore']),
            new TwigFunction('datastarStoreFromClass', [DatastarFunctions::class, 'datastarStoreFromClass']),
        ];
    }

    /**
     * @inerhitdoc
     */
    public function getGlobals(): array
    {
        return [
            'datastar' => new DatastarGlobal(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTokenParsers(): array
    {
        return [
            new FragmentTokenParser(),
            new ExecuteScriptTokenParser(),
        ];
    }
}
