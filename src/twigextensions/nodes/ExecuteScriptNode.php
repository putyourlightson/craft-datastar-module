<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use Twig\Compiler;
use Twig\Node\Node;

class ExecuteScriptNode extends Node
{
    use NodeTrait;

    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler): void
    {
        $this->compileMethod($compiler, 'executeScript');
    }
}
