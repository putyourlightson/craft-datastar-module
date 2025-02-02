<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use Twig\Compiler;
use Twig\Node\Node;

class SleepNode extends Node
{
    public function compile(Compiler $compiler): void
    {
        $duration = $this->getNode('duration');
        $unit = $this->hasNode('ms') ? 'ms' : 's';

        $compiler->addDebugInfo($this)
            ->write('if (')
            ->subcompile($duration)
            ->write(" > 0) {\n");

        if ($unit === 'ms') {
            $compiler
                ->addDebugInfo($this)
                ->write('usleep(')
                ->subcompile($duration)
                ->write(" * 1000);\n");
        } else {
            $compiler
                ->addDebugInfo($this)
                ->write('sleep(')
                ->subcompile($duration)
                ->write(");\n");
        }

        $compiler->write("}\n");
    }
}
