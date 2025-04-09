<?php

namespace IPP\Student\Classes;

use DOMDocument;
use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Core\Interface\SourceReader;


class XMLParser {
    private DOMDocument $source;

    public function __construct(DOMDocument $source) {
        $this->source = $source;
    }
    public function parseProgram(): Program {

        $root = $this->source->documentElement;
        if ($root === null || $root->nodeName !== 'program' || $root->getAttribute('language') !== 'SOL25') {
            throw new FileStructureException('Expected: <program language="SOL25">');
        }

        $program = new Program(
            $root->getAttribute('language'),
            $root->getAttribute('description')
        );

        foreach ($root->childNodes as $classNode) {
            if ($classNode instanceof DOMElement && $classNode->nodeName === 'class') {
                $solClass = $this->parseClass($classNode);
                $program->addClass($solClass);
            }
        }
        return $program;
    }
    private function parseClass(DOMElement $classNode): SolClass {
        $className = $classNode->getAttribute("name");
        $classParent = $classNode->getAttribute("parent");

        $solClass = new SolClass($className, $classParent);

        foreach ($classNode->childNodes as $methodNode) {
            if ($methodNode instanceof DOMElement) {
                if ($methodNode->nodeName !== 'method') {
                    throw new FileStructureException("Expected: <method ...> in <class ...>\nGot: <{$methodNode->nodeName} ...>");
                }
                $method = $this->parseMethod($methodNode);
                $solClass->addMethod($method);
            }
        }
        return $solClass;
    }
    private function parseMethod(DOMElement $methodNode): Method {
        $selector = $methodNode->getAttribute("selector");
        $blockElement = $methodNode->getElementsByTagName("block")->item(0);
        if ($blockElement === null) {
            throw new FileStructureException("Expected: <block ...> in <method selector=\"$selector\">");
        }

        $block = $this->parseBlock($blockElement);

        return new Method($selector, $block);
    }
    private function parseBlock(DOMElement $blockNode): Block {
        $arity = (int)$blockNode->getAttribute("arity");
        $block = new Block($arity);

        foreach ($blockNode->childNodes as $child) {
            if ($child instanceof DOMElement) {
                switch ($child->nodeName) {
                    case 'parameter':
                        $parameter = $this->parseParameter($child);
                        $block->addParameter($parameter);
                        break;
                    case 'assign':
                        $assign = $this->parseAssignment($child);
                        $block->addAssignment($assign);
                        break;
                    default:
                        break;
                }
            }
        }
        return $block;
    }
    private function parseParameter(DOMElement $parameterNode): Parameter {
        $order = (int)$parameterNode->getAttribute("order");
        $name = $parameterNode->getAttribute("name");
        return new Parameter($order, $name);
    }
    private function parseAssignment(DOMElement $assignNode): Assign {
        $order = (int)$assignNode->getAttribute("order");

        $varNode = null;
        $exprNode = null;
        foreach ($assignNode->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->nodeName === 'var') {
                    $varNode = new VarNode($child->getAttribute("name"));
                } elseif ($child->nodeName === 'expr') {
                    $exprNode = $this->parseExpr($child);
                }
            }
        }
        if ($varNode === null) {
            throw new FileStructureException("Missing <var> element in <assign>");
        }
        if ($exprNode === null) {
            throw new FileStructureException("Missing <expr> element in <assign>");
        }

        return new Assign($order, $varNode, $exprNode);
    }
    private function parseExpr(DOMElement $exprElement): Expr {
        foreach ($exprElement->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }
            switch ($child->nodeName) {
                case 'literal':
                    $literal = $this->parseLiteral($child);
                    return new Expr($literal);
                case 'send':
                    $send = $this->parseSend($child);
                    return new Expr(null, $send);
                case 'block':
                    $block = $this->parseBlock($child);
                    return new Expr(null, null, $block);
                case 'var':
                    $varName = $child->getAttribute('name');
                    return new Expr(null, null, null, new VarNode($varName));
                default:
                    throw new FileStructureException("Neznámý element <{$child->nodeName}> uvnitř <expr>");
            }
        }
        return new Expr();
    }
    private function parseLiteral(DOMElement $literalElement): Literal {
        $classType = $literalElement->getAttribute('class');
        $value = $literalElement->getAttribute('value');
        return new Literal($classType, $value);
    }
    private function parseSend(DOMElement $sendElement): Send {
        $selector = $sendElement->getAttribute('selector');
        $receiver = null;
        $arguments = [];
        foreach ($sendElement->childNodes as $sendChild) {
            if (!$sendChild instanceof DOMElement) {
                continue;
            }
            if ($sendChild->nodeName === 'expr') {
                $receiver = $this->parseExpr($sendChild);
            } elseif ($sendChild->nodeName === 'arg') {
                $arguments[] = $this->parseArg($sendChild);
            }
        }
        return new Send($selector, $receiver, $arguments);
    }
    private function parseArg(DOMElement $argElement): Arg {
        $order = (int)$argElement->getAttribute('order');
        $argExpr = null;
        foreach ($argElement->childNodes as $argChild) {
            if ($argChild instanceof DOMElement && $argChild->nodeName === 'expr') {
                $argExpr = $this->parseExpr($argChild);
                break;
            }
        }
        return new Arg($order, $argExpr);
    }
}