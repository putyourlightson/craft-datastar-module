<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

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
        $this->compileWithOptions($compiler, 'patchElements');
    }
}
