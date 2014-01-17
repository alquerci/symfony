<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CssSelector is the main entry point of the component and can convert CSS
 * selectors to XPath expressions.
 *
 * $xpath = CssSelector::toXpath('h1.foo');
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_CssSelector_CssSelector
{
    /**
     * Translates a CSS expression to its XPath equivalent.
     * Optionally, a prefix can be added to the resulting XPath
     * expression with the $prefix parameter.
     *
     * @param mixed  $cssExpr The CSS expression.
     * @param string $prefix  An optional prefix for the XPath expression.
     *
     * @return string
     *
     * @throws Symfony_Component_CssSelector_Exception_ParseException When got None for xpath expression
     *
     * @api
     */
    public static function toXPath($cssExpr, $prefix = 'descendant-or-self::')
    {
        if (is_string($cssExpr)) {
            if (!$cssExpr) {
                return $prefix.'*';
            }

            if (preg_match('#^\w+\s*$#u', $cssExpr, $match)) {
                return $prefix.trim($match[0]);
            }

            if (preg_match('~^(\w*)#(\w+)\s*$~u', $cssExpr, $match)) {
                return sprintf("%s%s[@id = '%s']", $prefix, $match[1] ? $match[1] : '*', $match[2]);
            }

            if (preg_match('#^(\w*)\.(\w+)\s*$#u', $cssExpr, $match)) {
                return sprintf("%s%s[contains(concat(' ', normalize-space(@class), ' '), ' %s ')]", $prefix, $match[1] ? $match[1] : '*', $match[2]);
            }

            $parser = new self();
            $cssExpr = $parser->parse($cssExpr);
        }

        $expr = $cssExpr->toXpath();

        // @codeCoverageIgnoreStart
        if (!$expr) {
            throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf('Got None for xpath expression from %s.', $cssExpr->__toString()));
        }
        // @codeCoverageIgnoreEnd

        if ($prefix) {
            $expr->addPrefix($prefix);
        }

        return (string) $expr->__toString();
    }

    /**
     * Parses an expression and returns the Node object that represents
     * the parsed expression.
     *
     * @param string $string The expression to parse
     *
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     *
     * @throws Exception When tokenizer throws it while parsing
     */
    public function parse($string)
    {
        $tokenizer = new Symfony_Component_CssSelector_Tokenizer();

        $stream = new Symfony_Component_CssSelector_TokenStream($tokenizer->tokenize($string), $string);

        try {
            return $this->parseSelectorGroup($stream);
        } catch (Exception $e) {
            $class = get_class($e);
            $uses = array();
            foreach ($stream->getUsed() as $used) {
                $uses[] = $used->__toString();
            }
            throw new $class(sprintf('%s at %s -> %s', $e->getMessage(), implode($uses, ''), $stream->peek())/* , 0, $e */);
        }
    }

    /**
     * Parses a selector group contained in $stream and returns
     * the Node object that represents the expression.
     *
     * @param Symfony_Component_CssSelector_TokenStream $stream The stream to parse.
     *
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     */
    private function parseSelectorGroup($stream)
    {
        $result = array();
        while (true) {
            $result[] = $this->parseSelector($stream);
            if (null !== $stream->peek() && $stream->peek()->__toString() == ',') {
                $stream->next();
            } else {
                break;
            }
        }

        if (count($result) == 1) {
            return $result[0];
        }

        return new Symfony_Component_CssSelector_Node_OrNode($result);
    }

    /**
     * Parses a selector contained in $stream and returns the Node
     * object that represents it.
     *
     * @param Symfony_Component_CssSelector_TokenStream $stream The stream containing the selector.
     *
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     *
     * @throws Symfony_Component_CssSelector_Exception_ParseException When expected selector but got something else
     */
    private function parseSelector($stream)
    {
        $result = $this->parseSimpleSelector($stream);

        while (true) {
            $peek = $stream->peek();
            if (null === $peek || ',' == $peek->__toString()) {
                return $result;
            } elseif (in_array($peek->__toString(), array('+', '>', '~'))) {
                // A combinator
                $combinator = $stream->next();
                $combinator = null === $combinator ? '' : $combinator->__toString();

                // Ignore optional whitespace after a combinator
                while (null !== $stream->peek() && ' ' == $stream->peek()->__toString()) {
                    $stream->next();
                }
            } else {
                $combinator = ' ';
            }
            $consumed = count($stream->getUsed());
            $nextSelector = $this->parseSimpleSelector($stream);
            if ($consumed == count($stream->getUsed())) {
                throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected selector, got '%s'", null === $stream->peek() ? '' : $stream->peek()->__toString()));
            }

            $result = new Symfony_Component_CssSelector_Node_CombinedSelectorNode($result, $combinator, $nextSelector);
        }

        return $result;
    }

    /**
     * Parses a simple selector (the current token) from $stream and returns
     * the resulting Node object.
     *
     * @param Symfony_Component_CssSelector_TokenStream $stream The stream containing the selector.
     *
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     *
     * @throws Symfony_Component_CssSelector_Exception_ParseException When expected symbol but got something else
     */
    private function parseSimpleSelector($stream)
    {
        $peek = $stream->peek();
        if ('*' != $peek->__toString() && !$peek->isType('Symbol')) {
            $element = $namespace = '*';
        } else {
            $next = $stream->next();
            if ('*' != $next->__toString() && !$next->isType('Symbol')) {
                throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected symbol, got '%s'", $next->__toString()));
            }

            if (null !== $stream->peek() && $stream->peek()->__toString() == '|') {
                $namespace = $next;
                $stream->next();
                $element = $stream->next();
                if ('*' != $element->__toString() && !$next->isType('Symbol')) {
                    throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected symbol, got '%s'", $next->__toString()));
                }
            } else {
                $namespace = '*';
                $element = $next;
            }
        }

        $result = new Symfony_Component_CssSelector_Node_ElementNode($namespace, $element);
        $hasHash = false;
        while (true) {
            $peek = $stream->peek();
            if (null === $peek) {
                break;
            } elseif ('#' == $peek->__toString()) {
                if ($hasHash) {
                    /* You can't have two hashes
                        (FIXME: is there some more general rule I'm missing?) */
                    // @codeCoverageIgnoreStart
                    break;
                    // @codeCoverageIgnoreEnd
                }
                $stream->next();
                $result = new Symfony_Component_CssSelector_Node_HashNode($result, $stream->next());
                $hasHash = true;

                continue;
            } elseif ('.' == $peek->__toString()) {
                $stream->next();
                $result = new Symfony_Component_CssSelector_Node_ClassNode($result, $stream->next());

                continue;
            } elseif ('[' == $peek->__toString()) {
                $stream->next();
                $result = $this->parseAttrib($result, $stream);
                $next = $stream->next();
                if (null === $next || ']' != $next->__toString()) {
                    throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("] expected, got '%s'", null === $next ? '' : $next->__toString()));
                }

                continue;
            } elseif (':' == $peek->__toString() || '::' == $peek->__toString()) {
                $type = $stream->next();
                $ident = $stream->next();
                if (!$ident || !$ident->isType('Symbol')) {
                    throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected symbol, got '%s'", null === $ident ? '' : $ident->__toString()));
                }

                if ($stream->peek()->__toString() == '(') {
                    $stream->next();
                    $peek = $stream->peek();
                    if ($peek->isType('String')) {
                        $selector = $stream->next();
                    } elseif ($peek->isType('Symbol') && is_int($peek)) {
                        $selector = intval($stream->next());
                    } else {
                        // FIXME: parseSimpleSelector, or selector, or...?
                        $selector = $this->parseSimpleSelector($stream);
                    }
                    $next = $stream->next();
                    if (null === $next || ')' != $next->__toString()) {
                        throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected ')', got '%s' and '%s'", null === $next ? '' : $next->__toString(), ((is_object($selector) && method_exists($selector, '__toString')) ? $selector->__toString() : $selector)));
                    }

                    $result = new Symfony_Component_CssSelector_Node_FunctionNode($result, $type, $ident, $selector);
                } else {
                    $result = new Symfony_Component_CssSelector_Node_PseudoNode($result, $type, $ident);
                }

                continue;
            } else {
                if (' ' == $peek->__toString()) {
                    $stream->next();
                }

                break;
            }
            // FIXME: not sure what "negation" is
        }

        return $result;
    }

    /**
     * Parses an attribute from a selector contained in $stream and returns
     * the resulting AttribNode object.
     *
     * @param Symfony_Component_CssSelector_Node_NodeInterface $selector The selector object whose attribute
     *                                      is to be parsed.
     * @param Symfony_Component_CssSelector_TokenStream $stream The container token stream.
     *
     * @return Symfony_Component_CssSelector_Node_AttribNode
     *
     * @throws Symfony_Component_CssSelector_Exception_ParseException When encountered unexpected selector
     */
    private function parseAttrib($selector, $stream)
    {
        $attrib = $stream->next();
        if (null !== $stream->peek() && $stream->peek()->__toString() == '|') {
            $namespace = $attrib;
            $stream->next();
            $attrib = $stream->next();
        } else {
            $namespace = '*';
        }

        if (null !== $stream->peek() && $stream->peek()->__toString() == ']') {
            return new Symfony_Component_CssSelector_Node_AttribNode($selector, $namespace, $attrib, 'exists', null);
        }

        $op = $stream->next();
        if (null === $op || !in_array($op->__toString(), array('^=', '$=', '*=', '=', '~=', '|=', '!='))) {
            throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Operator expected, got '%s'", null === $op ? '' : $op->__toString()));
        }

        $value = $stream->next();
        if (!$value->isType('Symbol') && !$value->isType('String')) {
            throw new Symfony_Component_CssSelector_Exception_ParseException(sprintf("Expected string or symbol, got '%s'", $value->__toString()));
        }

        return new Symfony_Component_CssSelector_Node_AttribNode($selector, $namespace, $attrib, $op, $value);
    }
}
