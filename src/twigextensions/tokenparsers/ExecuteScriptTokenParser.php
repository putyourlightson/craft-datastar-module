<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\tokenparsers;

use putyourlightson\datastar\twigextensions\nodes\FragmentNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class ExecuteScriptTokenParser extends AbstractTokenParser
{
    /**
     * @return string
     */
    public function getTag(): string
    {
        return 'executeScript';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): FragmentNode
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();
        $expressionParser = $parser->getExpressionParser();

        $nodes = [];

        if ($stream->test(Token::NAME_TYPE, 'with')) {
            $stream->next();
            $nodes['options'] = $expressionParser->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $nodes['body'] = $parser->subparse([$this, 'decideFragmentEnd'], true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new FragmentNode($nodes, [], $lineno, $this->getTag());
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function decideFragmentEnd(Token $token): bool
    {
        return $token->test('endfragment');
    }
}