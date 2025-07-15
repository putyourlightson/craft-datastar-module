<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\tokenparsers;

use putyourlightson\datastar\twigextensions\nodes\RemoveElementsNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class RemoveElementsTokenParser extends AbstractTokenParser
{
    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return 'removeelements';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): RemoveElementsNode
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();
        $expressionParser = $parser->getExpressionParser();

        $nodes = [];
        $nodes['selector'] = $expressionParser->parseExpression();

        if ($stream->test(Token::NAME_TYPE, 'with')) {
            $stream->next();
            $nodes['options'] = $expressionParser->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new RemoveElementsNode($nodes, [], $lineno);
    }
}
