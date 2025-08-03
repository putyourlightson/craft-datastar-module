<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\Sse;
use Twig\Compiler;
use Twig\Node\Node;

class RemoveElementsNode extends Node
{
    /**
     * @uses Sse::removeElements()
     */
    public function compile(Compiler $compiler): void
    {
        $selector = $this->getNode('selector');
        $options = $this->hasNode('options') ? $this->getNode('options') : null;

        $compiler
            ->addDebugInfo($this)
            ->write("\$selector = ")
            ->subcompile($selector)
            ->raw(";\n")
            ->write("\$options = ");

        if ($options) {
            $compiler->subcompile($options);
        } else {
            $compiler->raw('[]');
        }

        $compiler
            ->raw(";\n")
            ->write(Datastar::class . "::getInstance()->sse->removeElements(\$selector, \$options);\n");
    }
}
