<?php
/*
 * Copyright (c) 2017 Benjamin Kleiner
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


namespace Benkle\FeedResponse\XmlMappers\Atom;


use Benkle\FeedInterfaces\RelationLinkInterface;

class RelationLinkMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $relationMock = $this->mockLink(null, null, 'aaa', 'bbb');
        $doc = new \DOMDocument();
        $mapper = new RelationLinkMapper();

        $node = $mapper->map($doc, $relationMock);
        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals('aaa', $node->attributes->getNamedItem('rel')->textContent);
        $this->assertEquals('bbb', $node->attributes->getNamedItem('href')->textContent);
    }

    /**
     * @param $title
     * @param $mimeType
     * @param $relationType
     * @param $url
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockLink($title, $mimeType, $relationType, $url)
    {
        $relationMock = $this->createMock(RelationLinkInterface::class);
        $relationMock
            ->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn($title);
        $relationMock
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn($mimeType);
        $relationMock
            ->expects($this->once())
            ->method('getRelationType')
            ->willReturn($relationType);
        $relationMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        return $relationMock;
    }

    public function testTitle()
    {
        $relationMock = $this->mockLink('ccc', null, 'aaa', 'bbb');
        $doc = new \DOMDocument();
        $mapper = new RelationLinkMapper();

        $node = $mapper->map($doc, $relationMock);
        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals('aaa', $node->attributes->getNamedItem('rel')->textContent);
        $this->assertEquals('bbb', $node->attributes->getNamedItem('href')->textContent);
        $this->assertEquals('ccc', $node->attributes->getNamedItem('title')->textContent);
    }

    public function testMime()
    {
        $relationMock = $this->mockLink(null, 'ccc', 'aaa', 'bbb');
        $doc = new \DOMDocument();
        $mapper = new RelationLinkMapper();

        $node = $mapper->map($doc, $relationMock);
        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals('aaa', $node->attributes->getNamedItem('rel')->textContent);
        $this->assertEquals('bbb', $node->attributes->getNamedItem('href')->textContent);
        $this->assertEquals('ccc', $node->attributes->getNamedItem('type')->textContent);
    }

    public function testNamespaced()
    {
        $relationMock = $this->mockLink(null, null, 'aaa', 'bbb');
        $doc = new \DOMDocument();
        $mapper = new RelationLinkMapper(true);

        $node = $mapper->map($doc, $relationMock);
        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals('<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="aaa" href="bbb"/>', $doc->saveXML($node));
    }
}
