<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use Twig\Compiler;
use Twig\Node\Node;

class PatchElementsNode extends Node
{
    use CompileWithOptionsTrait;

    /**
     * @uses SseService::patchElements()
     */
    public function compile(Compiler $compiler): void
    {
        $selector = $this->hasNode('selector') ? $this->getNode('selector') : null;

        if ($selector !== null) {
            $this->removeElements($compiler, $selector);
        } else {
            $this->compileWithOptions($compiler, 'patchElements');
        }
    }

    /**
     * @uses SseService::removeElements()
     */
    private function removeElements(Compiler $compiler, Node $selector): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->write("\$selector = ")
            ->subcompile($selector)
            ->raw(";\n")
            ->write(Datastar::class . "::getInstance()->sse->removeElements(\$selector);\n");
    }
}
