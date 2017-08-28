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
use Benkle\FeedResponse\Interfaces\FeedMapperInterface;
use Benkle\FeedResponse\Interfaces\HasMapperCollectionInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Traits\HasMapperCollectionTrait;
use Benkle\FeedResponse\Traits\WithZeroExtraHeadersTrait;
use Benkle\FeedResponse\Traits\XMLUtilitiesTrait;

/**
 * Class FeedMapper
 * @package Benkle\FeedResponse\XmlMappers
 */
class FeedMapper implements HasMapperCollectionInterface, FeedMapperInterface
{
    use XMLUtilitiesTrait, HasMapperCollectionTrait, WithZeroExtraHeadersTrait;

    /**
     * Map a feed to a DOM document.
     * @param FeedInterface $feed
     * @return \DOMDocument
     */
    public function map(FeedInterface $feed)
    {
        $result = new \DOMDocument();
        $root = $result->createElement('rss');
        $root->setAttribute('version', '2.0');
        $result->appendChild($root);
        $channel = $result->createElement('channel');
        $root->appendChild($channel);

        $this->mapChannelMetadata($feed, $result, $channel);

        foreach ($feed->getRelations() as $relation => $url) {
            $this->addSimpleTag(
                $result,
                $channel,
                'atom:link',
                null,
                'http://www.w3.org/2005/Atom',
                [
                    'rel' => $relation,
                    'href' => $url,
                ]
            );
        }

        foreach ($feed->getItems() as $item) {
            /** @var ItemMapperInterface $mapper */
            $mapper = $this->getMapperCollection()->find($item);
            if ($mapper) {
                $channel->appendChild($mapper->map($result, $item));
            }
        }

        return $result;
    }

    /**
     * @param FeedInterface $feed
     * @param \DOMDocument $doc
     * @param \DOMNode $channel
     */
    protected function mapChannelMetadata(FeedInterface $feed, \DOMDocument $doc, \DOMNode $channel)
    {
        $this->addSimpleTag($doc, $channel, 'title', $feed->getTitle());
        $this->addSimpleTag($doc, $channel, 'link', $feed->getLink());
        $this->addSimpleTag($doc, $channel, 'description', $feed->getDescription());
        $this->addSimpleTag($doc, $channel, 'lastBuildDate', $feed->getLastModified()->format(\DateTime::RSS));
    }

    /**
     * Get the feeds content type.
     * @return string
     */
    public function getContentType()
    {
        return 'application/rss+xml';
    }

    /**
     * @param \DOMDocument $doc
     * @param \DOMNode $itemNode
     * @param ItemInterface $item
     */
    protected function mapItem(\DOMDocument $doc, \DOMNode $itemNode, ItemInterface $item)
    {
        $this->addSimpleTag($doc, $itemNode, 'title', $item->getTitle());
        $this->addSimpleTag($doc, $itemNode, 'description', $item->getDescription());
        $this->addSimpleTag($doc, $itemNode, 'link', $item->getLink());
        $this->addSimpleTag($doc, $itemNode, 'guid', $item->getPublicId());
        $this->addSimpleTag($doc, $itemNode, 'pubDate', $item->getLastModified()->format(\DateTime::RSS));
    }
}
