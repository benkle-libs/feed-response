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


use Benkle\FeedInterfaces\ItemInterface;
use Benkle\FeedResponse\Interfaces\HasMapperCollectionInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Traits\HasMapperCollectionTrait;
use Benkle\FeedResponse\Traits\XMLMapperTrait;
use Benkle\FeedResponse\Traits\XMLUtilitiesTrait;
use Benkle\FeedResponse\ItemMapperCollection;

/**
 * Class FeedItemMapper
 * @package Benkle\FeedResponse\XmlMappers\RSS20
 */
class FeedItemMapper implements ItemMapperInterface, HasMapperCollectionInterface
{
    use XMLUtilitiesTrait, XMLMapperTrait, HasMapperCollectionTrait;

    /**
     * Map a feed item to a DOM node.
     * @param \DOMDocument $doc
     * @param ItemInterface $item
     * @return \DOMNode
     */
    private function _map(\DOMDocument $doc, ItemInterface $item)
    {
        $itemNode = $doc->createElement('item');

        $this->addSimpleTag($doc, $itemNode, 'title', $item->getTitle());
        $this->addSimpleTag($doc, $itemNode, 'guid', $item->getPublicId());
        $this->addSimpleTag($doc, $itemNode, 'link', $item->getLink());
        $this->addSimpleTag($doc, $itemNode, 'description', $item->getDescription());
        $this->addSimpleTag($doc, $itemNode, 'pubDate', $item->getLastModified()->format(\DateTime::RSS));

        return $itemNode;
    }
}
