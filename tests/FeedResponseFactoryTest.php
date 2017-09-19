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
use Benkle\FeedInterfaces\RelationLinkInterface;
use Benkle\FeedResponse\Collections\ObjectMapperCollection;
use Benkle\FeedResponse\XmlMappers\Atom\FeedMapper as AtomMapper;
use Benkle\FeedResponse\XmlMappers\RSS20\FeedMapper as RssMapper;

class FeedResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersSetter()
    {
        $rssMapperMock = $this->createMock(RssMapper::class);
        $atomMapperMock = $this->createMock(AtomMapper::class);
        $objectMappers = $this->createMock(ObjectMapperCollection::class);
        $feedMock = $this->createMock(FeedInterface::class);
        $relationLinkMock = $this->createMock(RelationLinkInterface::class);

        $factory = new FeedResponseFactory();

        $this->assertEquals($factory, $factory->setRssMapper($rssMapperMock));
        $this->assertEquals($rssMapperMock, $factory->getRssMapper());
        $this->assertEquals($factory, $factory->setAtomMapper($atomMapperMock));
        $this->assertEquals($atomMapperMock, $factory->getAtomMapper());
        $this->assertEquals($factory, $factory->setObjectMappers($objectMappers));
        $this->assertEquals($objectMappers, $factory->getObjectMappers());
        $this->assertEquals($factory, $factory->setFeedPrototype($feedMock));
        $this->assertEquals($feedMock, $factory->getFeedPrototype());
        $this->assertEquals($factory, $factory->setRelationLinkPrototype($relationLinkMock));
        $this->assertEquals($relationLinkMock, $factory->getRelationLinkPrototype());
    }

    public function testRssWithFeed()
    {
        $rssMapperMock = $this->createMock(RssMapper::class);
        $rssMapperMock
            ->expects($this->once())
            ->method('getExtraHeaders')
            ->willReturn([]);
        $rssMapperMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('content/type');
        $objectMappers = $this->createMock(ObjectMapperCollection::class);
        $feedMock = $this->createMock(FeedInterface::class);

        $factory = new FeedResponseFactory();
        $factory
            ->setRssMapper($rssMapperMock)
            ->setObjectMappers($objectMappers);

        $response = $factory->rss($feedMock, [], []);
        $this->assertInstanceOf(FeedResponse::class, $response);
        $this->assertEquals('content/type', $response->headers->get('Content-Type'));
        $this->assertEquals($objectMappers, $response->getItemMappers());
        $this->assertEquals($rssMapperMock, $response->getFeedMapper());
        $this->assertEquals($feedMock, $response->getFeed());
    }

    public function testRssWithRelations()
    {
        $rssMapperMock = $this->createMock(RssMapper::class);
        $rssMapperMock
            ->expects($this->once())
            ->method('getExtraHeaders')
            ->willReturn([]);
        $rssMapperMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('content/type');
        $objectMappers = $this->createMock(ObjectMapperCollection::class);

        $relationLinkMock = $this->createMock(RelationLinkInterface::class);
        $relationLinkMock
            ->expects($this->atLeastOnce())
            ->method('setUrl')
            ->with('url')
            ->willReturnSelf();
        $relationLinkMock
            ->expects($this->atLeastOnce())
            ->method('setRelationType')
            ->with('relationType')
            ->willReturnSelf();
        $relationLinkMock
            ->expects($this->atLeastOnce())
            ->method('setMimeType')
            ->with('mimeType')
            ->willReturnSelf();
        $relationLinkMock
            ->expects($this->atLeastOnce())
            ->method('setTitle')
            ->with(
                $this->logicalOr(
                    $this->equalTo('title'),
                    $this->equalTo(null)
                )
            )
            ->willReturnSelf();

        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->method('setRelation')
            ->with($relationLinkMock)
            ->willReturnSelf();

        $factory = new FeedResponseFactory();
        $factory
            ->setRssMapper($rssMapperMock)
            ->setObjectMappers($objectMappers)
            ->setRelationLinkPrototype($relationLinkMock);

        $response = $factory->rss(
            $feedMock,
            [],
            [
                'rel1' => $relationLinkMock,
                'rel2' => [
                    'url'          => 'url',
                    'title'        => 'title',
                    'mimeType'     => 'mimeType',
                    'relationType' => 'relationType',
                ],
                'rel3' => [
                    'href'  => 'url',
                    'title' => 'title',
                    'mime'  => 'mimeType',
                    'rel'   => 'relationType',
                ],
                'rel4' => [
                    'url'          => 'url',
                    'title'        => 'title',
                    'type'         => 'mimeType',
                    'relationType' => 'relationType',
                    'rel'          => 'rel',
                ],
                'rel5' => [
                    'url'          => 'url',
                    'mimeType'     => 'mimeType',
                    'relationType' => 'relationType',
                ],
            ]
        );
        $this->assertInstanceOf(FeedResponse::class, $response);
        $this->assertEquals('content/type', $response->headers->get('Content-Type'));
        $this->assertEquals($objectMappers, $response->getItemMappers());
        $this->assertEquals($rssMapperMock, $response->getFeedMapper());
        $this->assertEquals($feedMock, $response->getFeed());
    }

    public function testRssWithData()
    {
        $feeData = [
            'id'          => 'id',
            'link'        => '',
            'modified'    => new \DateTime(),
            'description' => 'description',
            'title'       => 'title',
            'url'         => 'url',
        ];

        $rssMapperMock = $this->createMock(RssMapper::class);
        $rssMapperMock
            ->expects($this->once())
            ->method('getExtraHeaders')
            ->willReturn([]);
        $rssMapperMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('content/type');
        $objectMappers = $this->createMock(ObjectMapperCollection::class);

        $feedMock = $this->createMock(FeedInterface::class);
        $feedMock
            ->expects($this->once())
            ->method('setPublicId')
            ->with($feeData['id'])
            ->willReturnSelf();
        $feedMock
            ->expects($this->once())
            ->method('setLink')
            ->with($feeData['link'])
            ->willReturnSelf();
        $feedMock
            ->expects($this->once())
            ->method('setDescription')
            ->with($feeData['description'])
            ->willReturnSelf();
        $feedMock
            ->expects($this->once())
            ->method('setTitle')
            ->with($feeData['title'])
            ->willReturnSelf();
        $feedMock
            ->expects($this->once())
            ->method('setLastModified')
            ->with($feeData['modified'])
            ->willReturnSelf();

        $relationLinkMock = $this->createMock(RelationLinkInterface::class);
        $relationLinkMock
            ->expects($this->once())
            ->method('setUrl')
            ->willReturnSelf();
        $relationLinkMock
            ->expects($this->once())
            ->method('setRelationType')
            ->willReturnSelf();

        $factory = new FeedResponseFactory();
        $factory
            ->setRssMapper($rssMapperMock)
            ->setObjectMappers($objectMappers)
            ->setFeedPrototype($feedMock)
            ->setRelationLinkPrototype($relationLinkMock);

        $response = $factory->rss($feeData, [], []);
        $this->assertInstanceOf(FeedResponse::class, $response);
        $this->assertEquals('content/type', $response->headers->get('Content-Type'));
        $this->assertEquals($objectMappers, $response->getItemMappers());
        $this->assertEquals($rssMapperMock, $response->getFeedMapper());
        $this->assertEquals($feedMock, $response->getFeed());
    }
}
