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

/**
 * Class FeedResponseFactory
 * @package Benkle\FeedResponse
 */
class FeedResponseFactory
{
    /** @var  ObjectMapperCollection */
    private $objectMappers;

    /** @var  RssMapper */
    private $rssMapper;

    /** @var  AtomMapper */
    private $atomMapper;

    /** @var  FeedInterface */
    private $feedPrototype;

    /** @var  RelationLinkInterface */
    private $relationLinkPrototype;

    /**
     * Get the prototype used to produce relation links.
     * @return RelationLinkInterface
     */
    public function getRelationLinkPrototype()
    {
        return $this->relationLinkPrototype;
    }

    /**
     * Set the prototype used to produce relation links.
     * @param RelationLinkInterface $relationLinkPrototype
     * @return $this
     */
    public function setRelationLinkPrototype($relationLinkPrototype)
    {
        $this->relationLinkPrototype = $relationLinkPrototype;
        return $this;
    }

    /**
     * Get the prototype used to produce feed bases.
     * @return FeedInterface
     */
    public function getFeedPrototype()
    {
        return $this->feedPrototype;
    }

    /**
     * Set the prototype used to produce feed bases.
     * @param FeedInterface $feedPrototype
     * @return $this
     */
    public function setFeedPrototype(FeedInterface $feedPrototype)
    {
        $this->feedPrototype = $feedPrototype;
        return $this;
    }

    /**
     * Get the object mappers.
     * @return ObjectMapperCollection
     */
    public function getObjectMappers()
    {
        return $this->objectMappers;
    }

    /**
     * Set the object mappers.
     * @param ObjectMapperCollection $objectMappers
     * @return FeedResponseFactory
     */
    public function setObjectMappers($objectMappers)
    {
        $this->objectMappers = $objectMappers;
        return $this;
    }

    /**
     * Get the feed mapper for RSS feeds.
     * @return RssMapper
     */
    public function getRssMapper()
    {
        return $this->rssMapper;
    }

    /**
     * Set the feed mapper for RSS feeds.
     * @param RssMapper $rssMapper
     * @return FeedResponseFactory
     */
    public function setRssMapper(RssMapper $rssMapper)
    {
        $this->rssMapper = $rssMapper;
        return $this;
    }

    /**
     * Get the feed mapper for Atom feeds.
     * @return AtomMapper
     */
    public function getAtomMapper()
    {
        return $this->atomMapper;
    }

    /**
     * Set the feed mapper for Atom feeds.
     * @param AtomMapper $atomMapper
     * @return FeedResponseFactory
     */
    public function setAtomMapper(AtomMapper $atomMapper)
    {
        $this->atomMapper = $atomMapper;
        return $this;
    }

    /**
     * Produce an RSS response.
     * @param FeedInterface|array $feedHead
     * @param object[] $items
     * @param string[] $relations
     * @return FeedResponse
     */
    public function rss($feedHead, $items = [], $relations = [])
    {
        $feed = $this->prepareFeed($feedHead, $relations);
        $response = new FeedResponse($feed, $items);
        $response
            ->setFeedMapper($this->rssMapper)
            ->setItemMappers($this->objectMappers);

        return $response;
    }

    /**
     * Prepare the feed used as base for the FeedResponse.
     * @param array|FeedInterface $feedHead
     * @param array $relations
     * @return FeedInterface
     */
    private function prepareFeed($feedHead, $relations)
    {
        if ($feedHead instanceof FeedInterface) {
            $feed = $feedHead;
        } else {
            $feed = clone $this->feedPrototype;
            $feed
                ->setPublicId(isset($feedHead['id']) ? $feedHead['id'] : '')
                ->setLink(isset($feedHead['link']) ? $feedHead['link'] : '')
                ->setLastModified(isset($feedHead['modified']) ? $feedHead['modified'] : new \DateTime())
                ->setDescription(isset($feedHead['description']) ? $feedHead['description'] : '')
                ->setTitle(isset($feedHead['title']) ? $feedHead['title'] : '')
                ->setUrl(isset($feedHead['url']) ? $feedHead['url'] : '');

            if (!isset($relations['self'])) {
                $relations['self'] = $this->fetchElement($feedHead, ['link', 'url']);
            }
        }

        foreach ($relations as $relationType => $relationData) {
            /** @var RelationLinkInterface $relation */
            if ($relationData instanceof RelationLinkInterface) {
                $relation = $relationData;
            } elseif (is_array($relationData)) {
                $relation = clone $this->relationLinkPrototype;
                $relation
                    ->setUrl($this->fetchElement($relationData, ['url', 'href']))
                    ->setRelationType($this->fetchElement($relationData, ['relationType', 'rel']))
                    ->setMimeType($this->fetchElement($relationData, ['mimeType', 'mime', 'type']))
                    ->setTitle($this->fetchElement($relationData, ['title']));
            } else {
                $relation = clone $this->relationLinkPrototype;
                $relation
                    ->setUrl($relationData)
                    ->setRelationType($relationType);
            }
            $feed->setRelation($relation);
        }

        return $feed;
    }

    /**
     * Get an element from an array.
     * @param array $array
     * @param string|string[] $keys
     * @param mixed|null $default
     */
    private function fetchElement(array $array, $keys, $default = null)
    {
        $keys = is_array($keys) ? $keys : [$keys];
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }
        return $default;
    }

    /**
     * Produce an Atom response.
     * @param FeedInterface|array $feedHead
     * @param object[] $items
     * @param string[] $relations
     * @return FeedResponse
     */
    public function atom($feedHead, $items = [], $relations = [])
    {
        $feed = $this->prepareFeed($feedHead, $relations);
        $response = new FeedResponse($feed, $items);
        $response
            ->setFeedMapper($this->atomMapper)
            ->setItemMappers($this->objectMappers);

        return $response;
    }
}
