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

use Benkle\FeedInterfaces\RelationLinkInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Traits\ItemMapperTrait;

/**
 * Class RelationLinkMapper
 * @package Benkle\FeedResponse\XmlMappers\Atom
 */
class RelationLinkMapper implements ItemMapperInterface
{
    use ItemMapperTrait;

    /** @var bool */
    private $withNamespace = false;

    /**
     * RelationLinkMapper constructor.
     * @param bool $withNamespace
     */
    public function __construct($withNamespace = false)
    {
        $this->withNamespace = $withNamespace;
    }

    /**
     * Map a feed item to a DOM node.
     * @param \DOMDocument $doc
     * @param RelationLinkInterface $item
     * @return \DOMNode
     */
    private function _map(\DOMDocument $doc, RelationLinkInterface $item)
    {
        $linkNode = $this->withNamespace
            ? $doc->createElementNS('http://www.w3.org/2005/Atom', 'atom:link')
            : $doc->createElement('link');
        if (!is_null($item->getTitle())) {
            $linkNode->setAttribute('title', $item->getTitle());
        }
        if (!is_null($item->getMimeType())) {
            $linkNode->setAttribute('type', $item->getMimeType());
        }
        $linkNode->setAttribute('rel', $item->getRelationType());
        $linkNode->setAttribute('href', $item->getUrl());
        return $linkNode;
    }
}
