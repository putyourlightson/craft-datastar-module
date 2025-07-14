<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\datastar\twigextensions\tokenparsers;

use putyourlightson\datastar\twigextensions\nodes\PatchElementsNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class PatchElementsTokenParser extends AbstractTokenParser
{
    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return 'patchelements';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): PatchElementsNode
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();
        $expressionParser = $parser->getExpressionParser();

        $nodes = [];

        if ($stream->test(Token::NAME_TYPE, 'remove')) {
            $stream->next();
            $nodes['selector'] = $expressionParser->parseExpression();
        } else {
            if ($stream->test(Token::NAME_TYPE, 'with')) {
                $stream->next();
                $nodes['options'] = $expressionParser->parseExpression();
            }

            $stream->expect(Token::BLOCK_END_TYPE);
            $nodes['body'] = $parser->subparse([$this, 'decideEnd'], true);
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new PatchElementsNode($nodes, [], $lineno);
    }

    public function decideEnd(Token $token): bool
    {
        return $token->test('end' . $this->getTag());
    }
}
