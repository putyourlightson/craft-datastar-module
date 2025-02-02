<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\tokenparsers;

use putyourlightson\datastar\twigextensions\nodes\SleepNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class SleepTokenParser extends AbstractTokenParser
{
    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return 'sleep';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): SleepNode
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();
        $expressionParser = $parser->getExpressionParser();

        $nodes = [];
        $nodes['duration'] = $expressionParser->parseExpression();

        if ($stream->test(Token::NAME_TYPE, 'ms')) {
            $stream->next();
            $nodes['ms'] = new Node();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new SleepNode($nodes, [], $lineno, $this->getTag());
    }
}
