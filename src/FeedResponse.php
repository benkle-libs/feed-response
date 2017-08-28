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
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FeedResponse
 * @package Benkle\FeedResponse
 */
class FeedResponse extends Response
{
    /** @var  FeedMapperInterface */
    private $feedMapper;

    /** @var object[] */
    private $items = [];

    /** @var  FeedInterface */
    private $feed;

    /** @var  ObjectMapperCollection */
    private $itemMappers;

    /**
     * FeedResponse constructor.
     * @param FeedInterface $feed
     * @param \object[] $items
     * @param int $status
     * @param array $headers
     */
    public function __construct(FeedInterface $feed, array $items = [], $status = 200, $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->items = $items;
        $this->feed = $feed;
    }

    /**
     * Gets the feed mapper.
     * @return FeedMapperInterface
     */
    public function getFeedMapper()
    {
        return $this->feedMapper;
    }

    /**
     * Sets the feed mapper.
     *
     * It's not recommended to call this more than once per instance, as this sets special headers dependend on the
     * feed mapper.
     *
     * @param FeedMapperInterface $feedMapper
     * @return FeedResponse
     */
    public function setFeedMapper($feedMapper)
    {
        $this->feedMapper = $feedMapper;
        foreach ($feedMapper->getExtraHeaders() as $extraHeader => $value) {
            $this->headers->set($extraHeader, $value);
        }
        $this->headers->set('Content-Type', $feedMapper->getContentType());
        return $this;
    }

    /**
     * Get the mapper collection used for turning objects into ItemInterface instances.
     * @return ObjectMapperCollection
     */
    public function getItemMappers()
    {
        return $this->itemMappers;
    }

    /**
     * Set the mapper collection used for turning objects into ItemInterface instances.
     * @param ObjectMapperCollection $itemMappers
     * @return FeedResponse
     */
    public function setItemMappers(ObjectMapperCollection $itemMappers)
    {
        $this->itemMappers = $itemMappers;
        return $this;
    }

    /**
     * Get the feed items.
     * @return \object[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Adds another item to the feed.
     * @param $item
     * @return $this
     */
    public function addItem($item)
    {
        if (in_array($item, $this->items)) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Removes an item from the feed.
     * @param $item
     * @return bool
     */
    public function removeItem($item)
    {
        if (($key = array_search($item, $this->items)) !== false) {
            unset($this->items[$key]);
            return true;
        }
        return false;
    }

    /**
     * Get the basic feed object.
     * @return FeedInterface
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Gets the current response content.
     *
     * @return string Content
     */
    public function getContent()
    {
        foreach ($this->feed->getItems() as $item) {
            $this->feed->removeItem($item);
        }
        foreach ($this->items as $item) {
            if ($item instanceof ItemInterface) {
                $this->feed->addItem($item);
            } else {
                $feedItem = $this->itemMappers->find($item)->map($item);
                $this->feed->addItem($feedItem);
            }
        }
        $dom = $this->feedMapper->map($this->feed);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
