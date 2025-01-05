<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\nodes;

use putyourlightson\datastar\Datastar;
use putyourlightson\datastar\services\SseService;
use Twig\Compiler;

trait CompileWithOptionsTrait
{
    /**
     * Compiles a node with options.
     *
     * @uses SseService::setSseInProcess()
     */
    public function compileWithOptions(Compiler $compiler, string $method): void
    {
        $options = $this->hasNode('options') ? $this->getNode('options') : null;

        $compiler
            ->addDebugInfo($this)
            ->write(Datastar::class . "::getInstance()->sse->setSseInProcess('$method');\n")
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("\$content = ob_get_clean();\n")
            ->write("\$options = ");

        if ($options) {
            $compiler->subcompile($options);
        } else {
            $compiler->raw('[]');
        }

        $compiler
            ->raw(";\n")
            ->write(Datastar::class . "::getInstance()->sse->$method(\$content, \$options);\n");
    }
}
