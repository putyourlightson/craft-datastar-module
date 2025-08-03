<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\Sse;
use Twig\Compiler;
use Twig\Node\Node;

class LocationNode extends Node
{
    /**
     * @uses Sse::location()
     */
    public function compile(Compiler $compiler): void
    {
        $uri = $this->getNode('uri');
        $options = $this->hasNode('options') ? $this->getNode('options') : null;

        $compiler
            ->addDebugInfo($this)
            ->write("\$uri = ")
            ->subcompile($uri)
            ->raw(";\n")
            ->write("\$options = ");

        if ($options) {
            $compiler->subcompile($options);
        } else {
            $compiler->raw('[]');
        }

        $compiler
            ->raw(";\n")
            ->write(Datastar::class . "::getInstance()->sse->location(\$uri, \$options);\n");
    }
}
