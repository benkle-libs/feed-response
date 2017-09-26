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


namespace Benkle\FeedResponse\XmlMappers\RSS20;


use Benkle\FeedInterfaces\FeedInterface;
use Benkle\FeedInterfaces\ItemInterface;
use Benkle\FeedInterfaces\RelationLinkInterface;
use Benkle\FeedResponse\Collections\FeedItemMapperCollection;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;

class FeedMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testContentType()
    {
        $feedMapper = new FeedMapper();
        $this->assertEquals('application/rss+xml', $feedMapper->getContentType());
    }

    public function testMapping()
    {
        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');
        $feedMock
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn(new \DateTime('2017-09-26T19:09:29+02:00'));
        $feedMock
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn(null);
        $feedMock
            ->expects($this->once())
            ->method('getRelations')
            ->willReturn([]);
        $feedMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);


        $feedMapper = new FeedMapper();
        $doc = $feedMapper->map($feedMock);
        $xpath = new \DOMXPath($doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc);
        $this->assertEquals('title', $xpath->query('./channel/title')->item(0)->textContent);
        $this->assertEquals(
            'Tue, 26 Sep 2017 19:09:29 +0200', $xpath->query('./channel/lastBuildDate')->item(0)->textContent
        );
        $this->assertEquals('', $xpath->query('./channel/description')->item(0)->textContent);
        $this->assertEquals(1, $xpath->query('./channel/link')->length);
        $this->assertEquals(0, $xpath->query('./channel/entry')->length);
    }

    public function testMappingWithDescription()
    {
        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');
        $feedMock
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn(new \DateTime('2017-09-26T19:09:29+02:00'));
        $feedMock
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');
        $feedMock
            ->expects($this->once())
            ->method('getRelations')
            ->willReturn([]);
        $feedMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $feedMapper = new FeedMapper();
        $doc = $feedMapper->map($feedMock);
        $xpath = new \DOMXPath($doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc);
        $this->assertEquals('title', $xpath->query('./channel/title')->item(0)->textContent);
        $this->assertEquals(
            'Tue, 26 Sep 2017 19:09:29 +0200', $xpath->query('./channel/lastBuildDate')->item(0)->textContent
        );
        $this->assertEquals('description', $xpath->query('./channel/description')->item(0)->textContent);
        $this->assertEquals(1, $xpath->query('./channel/link')->length);
        $this->assertEquals(0, $xpath->query('./channel/entry')->length);
    }

    public function testMappingWithRelations()
    {
        $relationMock = $this->createMock(RelationLinkInterface::class);
        $relationMapperMock = $this->createMock(ItemMapperInterface::class);
        $relationMapperMock
            ->expects($this->atLeastOnce())
            ->method('map')
            ->with($this->isInstanceOf(\DOMDocument::class), $relationMock)
            ->willReturnCallback(
                function (\DOMDocument $doc, $mock) {
                    return $doc->createElement('link');
                }
            );
        $mapperCollection = $this->createMock(FeedItemMapperCollection::class);
        $mapperCollection
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with($relationMock)
            ->willReturn($relationMapperMock);
        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');
        $feedMock
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn(new \DateTime('2017-09-26T19:09:29+02:00'));
        $feedMock
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');
        $feedMock
            ->expects($this->once())
            ->method('getRelations')
            ->willReturn([$relationMock, $relationMock]);
        $feedMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $feedMapper = new FeedMapper();
        $feedMapper->setMapperCollection($mapperCollection);
        $doc = $feedMapper->map($feedMock);
        $xpath = new \DOMXPath($doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc);
        $this->assertEquals('title', $xpath->query('./channel/title')->item(0)->textContent);
        $this->assertEquals(
            'Tue, 26 Sep 2017 19:09:29 +0200', $xpath->query('./channel/lastBuildDate')->item(0)->textContent
        );
        $this->assertEquals('description', $xpath->query('./channel/description')->item(0)->textContent);
        $this->assertEquals(1, $xpath->query('./channel/link')->length);
        $this->assertEquals(0, $xpath->query('./channel/entry')->length);
    }

    public function testMappingWithItems()
    {
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMapperMock = $this->createMock(ItemMapperInterface::class);
        $itemMapperMock
            ->expects($this->once())
            ->method('map')
            ->with($this->isInstanceOf(\DOMDocument::class), $itemMock)
            ->willReturnCallback(
                function (\DOMDocument $doc, $mock) {
                    return $doc->createElement('entry');
                }
            );
        $mapperCollection = $this->createMock(FeedItemMapperCollection::class);
        $mapperCollection
            ->expects($this->once())
            ->method('find')
            ->with($itemMock)
            ->willReturn($itemMapperMock);
        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');
        $feedMock
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn(new \DateTime('2017-09-26T19:09:29+02:00'));
        $feedMock
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');
        $feedMock
            ->expects($this->once())
            ->method('getRelations')
            ->willReturn([]);
        $feedMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$itemMock]);

        $feedMapper = new FeedMapper();
        $feedMapper->setMapperCollection($mapperCollection);
        $doc = $feedMapper->map($feedMock);
        $xpath = new \DOMXPath($doc);
        $this->assertInstanceOf(\DOMDocument::class, $doc);
        $this->assertEquals('title', $xpath->query('./channel/title')->item(0)->textContent);
        $this->assertEquals(
            'Tue, 26 Sep 2017 19:09:29 +0200', $xpath->query('./channel/lastBuildDate')->item(0)->textContent
        );
        $this->assertEquals('description', $xpath->query('./channel/description')->item(0)->textContent);
        $this->assertEquals(1, $xpath->query('./channel/link')->length);
        $this->assertEquals(1, $xpath->query('./channel/entry')->length);
    }
}
