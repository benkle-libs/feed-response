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


namespace Benkle\FeedResponse;


use Benkle\FeedInterfaces\FeedInterface;
use Benkle\FeedInterfaces\ItemInterface;
use Benkle\FeedResponse\Collections\ObjectMapperCollection;
use Benkle\FeedResponse\Interfaces\FeedMapperInterface;
use Benkle\FeedResponse\Interfaces\ObjectMapperInterface;

class FeedResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testBasics()
    {
        $feedMock = $this->createMock(FeedInterface::class);
        $headersMock = ['X-Foo' => 'Bar'];
        $itemsMock = ['Foo'];

        $response = new FeedResponse($feedMock, $itemsMock, 500, $headersMock);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($feedMock, $response->getFeed());
        $this->assertTrue($response->headers->contains('X-Foo', $headersMock['X-Foo']));
        $this->assertEquals($itemsMock, $response->getItems());
    }

    public function testFeedMapper()
    {
        $feedMock = $this->createMock(FeedInterface::class);

        $feedMapperMock = $this->createMock(FeedMapperInterface::class);
        $feedMapperMock
            ->expects($this->once())
            ->method('getExtraHeaders')
            ->willReturn(['X-Foo' => 'Bar']);
        $feedMapperMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('foo/bar');

        $response = new FeedResponse($feedMock, []);

        $this->assertEquals($response, $response->setFeedMapper($feedMapperMock));
        $this->assertTrue($response->headers->contains('X-Foo', 'Bar'));
        $this->assertTrue($response->headers->contains('Content-Type', 'foo/bar'));
        $this->assertEquals($feedMapperMock, $response->getFeedMapper());
    }

    public function testItemMappers()
    {
        $feedMock = $this->createMock(FeedInterface::class);
        $itemMappersMock = $this->createMock(ObjectMapperCollection::class);

        $response = new FeedResponse($feedMock, []);

        $this->assertEquals($response, $response->setItemMappers($itemMappersMock));
        $this->assertEquals($itemMappersMock, $response->getItemMappers());
    }

    public function testAddRemoveItem()
    {
        $feedMock = $this->createMock(FeedInterface::class);
        $itemMappersMock = $this->createMock(ObjectMapperCollection::class);

        $response = new FeedResponse($feedMock, []);

        $this->assertEquals($response, $response->addItem('foo'));
        $this->assertEquals(['foo'], $response->getItems());
        $this->assertFalse($response->removeItem('bar'));
        $this->assertEquals(['foo'], $response->getItems());
        $this->assertTrue($response->removeItem('foo'));
        $this->assertEquals([], $response->getItems());
    }

    public function testContent()
    {
        $itemMock = $this->createMock(ItemInterface::class);

        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$itemMock]);
        $feedMock
            ->expects($this->once())
            ->method('removeItem')
            ->with($itemMock);
        $feedMock
            ->expects($this->atLeastOnce())
            ->method('addItem')
            ->with($itemMock);

        $dom = new \DOMDocument();

        $feedMapperMock = $this->createMock(FeedMapperInterface::class);
        $feedMapperMock
            ->expects($this->once())
            ->method('getExtraHeaders')
            ->willReturn(['X-Foo' => 'Bar']);
        $feedMapperMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('foo/bar');
        $feedMapperMock
            ->expects($this->once())
            ->method('map')
            ->with($feedMock)
            ->willReturn($dom);

        $itemMapperMock = $this->createMock(ObjectMapperInterface::class);
        $itemMapperMock
            ->expects($this->once())
            ->method('map')
            ->with($feedMock)
            ->willReturn($itemMock);

        $itemMappersMock = $this->createMock(ObjectMapperCollection::class);
        $itemMappersMock
            ->expects($this->once())
            ->method('find')
            ->with($feedMock)
            ->willReturn($itemMapperMock);

        $response = new FeedResponse($feedMock, [$itemMock, $feedMock]);
        $response
            ->setItemMappers($itemMappersMock)
            ->setFeedMapper($feedMapperMock);
        $this->assertEquals("<?xml version=\"1.0\"?>\n", $response->getContent());
    }
}
