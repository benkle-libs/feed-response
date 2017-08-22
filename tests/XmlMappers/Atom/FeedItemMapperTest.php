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


use Benkle\FeedInterfaces\EnclosureInterface;
use Benkle\FeedInterfaces\ItemInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\ItemMapperCollection;

class FeedItemMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapping()
    {
        $doc = new \DOMDocument();
        $itemMock = $this->mockItem();

        $mapper = new FeedItemMapper();

        $node = $mapper->map($doc, $itemMock);

        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals(
            '<entry>' .
            '<title>title</title>' .
            '<id>publicId</id>' .
            '<updated>1998-12-12T12:12:12+00:00</updated>' .
            '<summary>description</summary>' .
            '<link rel="self" href="link"/>' .
            '</entry>', $doc->saveXML($node));
    }

    public function testMappingWithRelations()
    {
        $doc = new \DOMDocument();
        $itemMock = $this->mockItem();

        $itemMock
            ->expects($this->exactly(2))
            ->method('getRelations')
            ->willReturn(['self' => 'this', 'this' => 'self']);

        $mapper = new FeedItemMapper();

        $node = $mapper->map($doc, $itemMock);

        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals(
            '<entry>' .
            '<title>title</title>' .
            '<id>publicId</id>' .
            '<updated>1998-12-12T12:12:12+00:00</updated>' .
            '<summary>description</summary>' .
            '<link rel="self" href="link"/>' .
            '<link rel="self" href="this"/>' .
            '<link rel="this" href="self"/>' .
            '</entry>', $doc->saveXML($node));
    }

    public function testMappingEnclosures()
    {
        $doc = new \DOMDocument();
        $enclosureMock =$this->createMock(EnclosureInterface::class);
        $enclosureMapperMock = $this->createMock(ItemMapperInterface::class);
        $enclosureMapperMock
            ->expects($this->exactly(2))
            ->method('map')
            ->with($doc, $enclosureMock)
            ->willReturnCallback(function() use ($doc) {
                return $doc->createElement('enclosure');
            });
        $collectionMock = $this->createMock(ItemMapperCollection::class);
        $collectionMock
            ->expects($this->exactly(2))
            ->method('find')
            ->with($enclosureMock)
            ->willReturn($enclosureMapperMock);

        $itemMock = $this->mockItem();
        $itemMock
            ->expects($this->exactly(2))
            ->method('getEnclosures')
            ->willReturn([$enclosureMock, $enclosureMock]);

        $mapper = new FeedItemMapper();
        $mapper->setMapperCollection($collectionMock);

        $node = $mapper->map($doc, $itemMock);

        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals(
            '<entry>' .
            '<title>title</title>' .
            '<id>publicId</id>' .
            '<updated>1998-12-12T12:12:12+00:00</updated>' .
            '<summary>description</summary>' .
            '<link rel="self" href="link"/>' .
            '<enclosure/>' .
            '<enclosure/>' .
            '</entry>', $doc->saveXML($node));
    }

    public function testCollectionHandling()
    {
        $collectionMock = $this->createMock(ItemMapperCollection::class);

        $mapper = new FeedItemMapper();
        $mapper->setMapperCollection($collectionMock);
        $this->assertEquals($collectionMock, $mapper->getMapperCollection());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockItem()
    {
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');
        $itemMock
            ->expects($this->once())
            ->method('getPublicId')
            ->willReturn('publicId');
        $itemMock
            ->expects($this->once())
            ->method('getLink')
            ->willReturn('link');
        $itemMock
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');
        $itemMock
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn(new \DateTime('1998-12-12 12:12:12Z'));
        return $itemMock;
    }
}
