<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\services\Sse;
use Twig\Compiler;
use Twig\Node\Node;

class ExecuteScriptNode extends Node
{
    use CompileWithOptionsTrait;

    /**
     * @uses Sse::executeScript()
     */
    public function compile(Compiler $compiler): void
    {
        $this->compileWithOptions($compiler, 'executeScript');
    }
}
