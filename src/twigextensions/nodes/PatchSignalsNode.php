<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use Twig\Compiler;
use Twig\Node\Node;

class PatchSignalsNode extends Node
{
    /**
     * @uses SseService::patchSignals()
     */
    public function compile(Compiler $compiler): void
    {
        $signals = $this->getNode('signals');
        $options = $this->hasNode('options') ? $this->getNode('options') : null;

        $compiler
            ->addDebugInfo($this)
            ->write("\$signals = ")
            ->subcompile($signals)
            ->raw(";\n")
            ->write("\$options = ");

        if ($options) {
            $compiler->subcompile($options);
        } else {
            $compiler->raw('[]');
        }

        $compiler
            ->raw(";\n")
            ->write(Datastar::class . "::getInstance()->sse->patchSignals(\$signals, \$options);\n");
    }
}
