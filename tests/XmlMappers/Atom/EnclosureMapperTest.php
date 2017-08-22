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

class EnclosureMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $doc = new \DOMDocument();
        $enclosureMock = $this->createMock(EnclosureInterface::class);
        $enclosureMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('url');
        $enclosureMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn('type');
        $enclosureMock
            ->expects($this->once())
            ->method('getLength')
            ->willReturn(0);

        $mapper = new EnclosureMapper();

        $node = $mapper->map($doc, $enclosureMock);

        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals(
            '<link rel="enclosure" href="url" length="0" type="type"/>', $doc->saveXML($node));
    }

    public function testMapEnclosureWithTitle()
    {
        $doc = new \DOMDocument();
        $enclosureMock = $this->createMock(EnclosureInterface::class);
        $enclosureMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('url');
        $enclosureMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn('type');
        $enclosureMock
            ->expects($this->once())
            ->method('getLength')
            ->willReturn(0);
        $enclosureMock
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('title');

        $mapper = new EnclosureMapper();

        $node = $mapper->map($doc, $enclosureMock);

        $this->assertInstanceOf(\DOMNode::class, $node);
        $this->assertEquals(
            '<link rel="enclosure" href="url" length="0" type="type" title="title"/>', $doc->saveXML($node));
    }
}
