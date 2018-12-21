<?php

use PHPHtmlParser\Dom\MockNode as Node;
use PHPHtmlParser\Exceptions\CircularException;
use PHPUnit\Framework\TestCase;

class NodeParentTest extends TestCase {

    public function testHasChild()
    {
        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $this->assertTrue($parent->hasChildren());
    }

    public function testHasChildNoChildren()
    {
        $node = new Node;
        $this->assertFalse($node->hasChildren());
    }

    public function testAddChild()
    {
        $parent = new Node;
        $child  = new Node;
        $this->assertTrue($parent->addChild($child));
    }

    public function testAddChildTwoParent()
    {
        $parent  = new Node;
        $parent2 = new Node;
        $child   = new Node;
        $parent->addChild($child);
        $parent2->addChild($child);
        $this->assertFalse($parent->hasChildren());
    }

    public function testGetChild()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        $this->assertTrue($parent->getChild($child2->id()) instanceof Node);
    }

    public function testRemoveChild()
    {
        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $parent->removeChild($child->id());
        $this->assertFalse($parent->hasChildren());
    }

    public function testRemoveChildNotExists()
    {
        $parent = new Node;
        $parent->removeChild(1);
        $this->assertFalse($parent->hasChildren());
    }

    public function testNextChild()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        
        $this->assertEquals($child2->id(), $parent->nextChild($child->id())->id());
    }

    public function testNextChildWithRemove()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $child3 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $parent->removeChild($child2->id());
        $this->assertEquals($child3->id(), $parent->nextChild($child->id())->id());
    }

    public function testPreviousChild()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        
        $this->assertEquals($child->id(), $parent->previousChild($child2->id())->id());
    }

    public function testPreviousChildWithRemove()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $child3 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $parent->removeChild($child2->id());
        $this->assertEquals($child->id(), $parent->previousChild($child3->id())->id());
    }

    public function testFirstChild()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $child3 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $this->assertEquals($child->id(), $parent->firstChild()->id());
    }

    public function testLastChild()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $child3 = new Node;
        $parent->addChild($child);
        $parent->addChild($child2);
        $parent->addChild($child3);

        $this->assertEquals($child3->id(), $parent->lastChild()->id());
    }

    public function testReplaceChild()
    {
        $parent = new Node;
        $child1 = new Node;
        $child2 = new Node;
        $child3 = new Node;
        $child4 = new Node;
        $parent->addChild($child1);
        $parent->addChild($child2);
        $parent->addChild($child3);

        self::assertAttributeEquals(
            [
                $child1->id() => [
                    'next' => $child2->id(),
                    'prev' => null,
                    'node' => $child1,
                ],
                $child2->id() => [
                    'next' => $child3->id(),
                    'prev' => $child1->id(),
                    'node' => $child2,
                ],
                $child3->id() => [
                    'next' => null,
                    'prev' => $child2->id(),
                    'node' => $child3,
                ],
            ],
            'children',
            $parent
        );

        $parent->replaceChild($child2->id(), $child4);

        $this->assertFalse($parent->isChild($child2->id()));
        $this->assertTrue($parent->isChild($child4->id()));

        self::assertAttributeEquals(
            [
                $child1->id() => [
                    'next' => $child4->id(),
                    'prev' => null,
                    'node' => $child1,
                ],
                $child3->id() => [
                    'next' => null,
                    'prev' => $child4->id(),
                    'node' => $child3,
                ],
                $child4->id() => [
                    'next' => $child3->id(),
                    'prev' => $child1->id(),
                    'node' => $child4,
                ],
            ],
            'children',
            $parent
        );
    }

    public function testSetParentDescendantException()
    {
        $this->expectException(CircularException::class);

        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $parent->setParent($child);
    }

    public function testAddChildAncestorException()
    {
        $this->expectException(CircularException::class);

        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $child->addChild($parent);
    }

    public function testAddItselfAsChild()
    {
        $this->expectException(CircularException::class);

        $parent = new Node;
        $parent->addChild($parent);
    }


    public function testIsAncestorParent()
    {
        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $this->assertTrue($child->isAncestor($parent->id()));
    }

    public function testGetAncestor()
    {
        $parent = new Node;
        $child  = new Node;
        $parent->addChild($child);
        $ancestor = $child->getAncestor($parent->id());
        $this->assertEquals($parent->id(), $ancestor->id());
    }

    public function testGetGreatAncestor()
    {
        $parent = new Node;
        $child  = new Node;
        $child2 = new Node;
        $parent->addChild($child);
        $child->addChild($child2);
        $ancestor = $child2->getAncestor($parent->id());
        $this->assertEquals($parent->id(), $ancestor->id());
    }

    public function testGetAncestorNotFound()
    {
        $parent = new Node;
        $ancestor = $parent->getAncestor(1);
        $this->assertNull($ancestor);
    }
}
