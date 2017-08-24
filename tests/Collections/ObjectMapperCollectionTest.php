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


namespace Benkle\FeedResponse\Collections;


use Benkle\FeedInterfaces\ChannelInterface;
use Benkle\FeedInterfaces\NodeInterface;
use Benkle\FeedResponse\Interfaces\ObjectMapperInterface;

class ObjectMapperCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndFind()
    {
        $collection = new ObjectMapperCollection();
        $mapperMock = $this->createMock(ObjectMapperInterface::class);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($mapperMock, $collection->find($nodeMock));
    }

    public function testAddAndFindByInterface()
    {
        $collection = new ObjectMapperCollection();
        $mapperMock = $this->createMock(ObjectMapperInterface::class);
        $nodeMock = $this->createMock(ChannelInterface::class);
        self::assertEquals($collection, $collection->add(ChannelInterface::class, $mapperMock));
        self::assertEquals($mapperMock, $collection->find($nodeMock));
    }

    public function testAddAndFindWithPriority()
    {
        $collection = new ObjectMapperCollection();
        $mapperMock1 = $this->createMock(ObjectMapperInterface::class);
        $mapperMock2 = $this->createMock(ObjectMapperInterface::class);
        $nodeMock = $this->createMock(ChannelInterface::class);
        self::assertEquals($collection, $collection->add(ChannelInterface::class, $mapperMock1, 10));
        self::assertEquals($collection, $collection->add(NodeInterface::class, $mapperMock2, 5));
        self::assertEquals($mapperMock2, $collection->find($nodeMock));
    }

    /**
     * @expectedException \Benkle\FeedResponse\Exceptions\MapperNotFoundException
     */
    public function testFindWithNoMapperAvailable()
    {
        $collection = new ObjectMapperCollection();
        $nodeMock = $this->createMock(ChannelInterface::class);
        $collection->find($nodeMock);
    }

    public function testAddAndRemove()
    {
        $collection = new ObjectMapperCollection();
        $mapperMock = $this->createMock(ObjectMapperInterface::class);
        $nodeMock = $this->createMock(NodeInterface::class);
        self::assertEquals($collection, $collection->add(get_class($nodeMock), $mapperMock));
        self::assertEquals($collection, $collection->remove(get_class($nodeMock)));
    }

    /**
     * @expectedException \Benkle\FeedResponse\Exceptions\MapperNotFoundException
     */
    public function testRemoveWithoutAdd()
    {
        $collection = new ObjectMapperCollection();
        $nodeMock = $this->createMock(NodeInterface::class);
        $collection->remove(get_class($nodeMock));
    }
}
