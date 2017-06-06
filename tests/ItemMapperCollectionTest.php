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


use Benkle\FeedInterfaces\ChannelInterface;
use Benkle\FeedInterfaces\NodeInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperWithMapperCollectionInterface;

class ItemMapperCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndFind()
    {
        $collection = new ItemMapperCollection();
        $mapperMock = $this->createMock(ItemMapperInterface::class);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($mapperMock, $collection->find($nodeMock));
    }

    public function testAddAndFindByParent()
    {
        $collection = new ItemMapperCollection();
        $mapperMock = $this->createMock(ItemMapperInterface::class);
        $nodeMock = $this->createMock(ChannelInterface::class);
        self::assertEquals($collection, $collection->add(get_parent_class($nodeMock), $mapperMock));
        self::assertEquals($mapperMock, $collection->find($nodeMock));
    }

    public function testAddMapperWithCollectionAccess()
    {
        $collection = new ItemMapperCollection();
        $mapperMock = $this->createMock(ItemMapperWithMapperCollectionInterface::class);
        $mapperMock
            ->expects($this->once())
            ->method('setMapperCollection')
            ->with($collection)
            ->willReturn(null);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($mapperMock, $collection->find($nodeMock));
    }

    /**
     * @expectedException \Benkle\FeedResponse\Exceptions\MapperNotFoundException
     */
    public function testFindWithNoMapperAvailable()
    {
        $collection = new ItemMapperCollection();
        $nodeMock = $this->createMock(ChannelInterface::class);
        $collection->find($nodeMock);
    }

    public function testAddAndRemove()
    {
        $collection = new ItemMapperCollection();
        $mapperMock = $this->createMock(ItemMapperInterface::class);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($collection, $collection->remove(get_class($nodeMock)));
    }

    public function testAddAndRemoveMapperWithCollectionAccess()
    {
        $collection = new ItemMapperCollection();
        $mapperMock = $this->createMock(ItemMapperWithMapperCollectionInterface::class);
        $mapperMock
            ->expects($this->exactly(2))
            ->method('setMapperCollection')
            ->withConsecutive($collection, $this->anything())
            ->willReturn(null);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($collection, $collection->remove(get_class($nodeMock)));
    }

    /**
     * @expectedException \Benkle\FeedResponse\Exceptions\MapperNotFoundException
     */
    public function testRemoveWithoutAdd()
    {
        $collection = new ItemMapperCollection();
        $nodeMock = $this->createMock(NodeInterface::class);
        $collection->remove(get_class($nodeMock));
    }
}
