<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\tokenparsers;

use putyourlightson\datastar\twigextensions\nodes\PatchSignalsNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class PatchSignalsTokenParser extends AbstractTokenParser
{
    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return 'patchsignals';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): PatchSignalsNode
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();
        $expressionParser = $parser->getExpressionParser();

        $nodes = [];
        $nodes['signals'] = $expressionParser->parseExpression();

        if ($stream->test(Token::NAME_TYPE, 'with')) {
            $stream->next();
            $nodes['options'] = $expressionParser->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new PatchSignalsNode($nodes, [], $lineno);
    }
}
