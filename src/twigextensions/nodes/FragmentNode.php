<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use Twig\Compiler;
use Twig\Node\Node;

class FragmentNode extends Node
{
    use NodeTrait;

    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler): void
    {
        $this->compileMethod($compiler, 'mergeFragments');
    }
}
