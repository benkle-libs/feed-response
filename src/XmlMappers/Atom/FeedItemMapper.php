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


use Benkle\FeedInterfaces\ItemInterface;
use Benkle\FeedResponse\Interfaces\HasMapperCollectionInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Traits\HasMapperCollectionTrait;
use Benkle\FeedResponse\Traits\ItemMapperTrait;
use Benkle\FeedResponse\Traits\XMLUtilitiesTrait;

/**
 * Class FeedItemMapper
 * @package Benkle\FeedResponse\XmlMappers\Atom
 */
class FeedItemMapper implements ItemMapperInterface, HasMapperCollectionInterface
{
    use XMLUtilitiesTrait, ItemMapperTrait, HasMapperCollectionTrait;

    /**
     * Map a feed item to a DOM node.
     * @param \DOMDocument $doc
     * @param ItemInterface $item
     * @return \DOMNode
     */
    private function _map(\DOMDocument $doc, ItemInterface $item)
    {
        $itemNode = $doc->createElement('entry');

        $this->addSimpleTag($doc, $itemNode, 'title', $item->getTitle());
        $this->addSimpleTag($doc, $itemNode, 'id', $item->getPublicId());
        $this->addSimpleTag($doc, $itemNode, 'updated', $item->getLastModified()->format(\DateTime::ATOM));

        $description = $item->getDescription();
        if (isset($description)) {
            $this->addSimpleTag($doc, $itemNode, 'summary', $description);
        }

        $this->addSimpleTag(
            $doc,
            $itemNode,
            'link',
            null,
            null,
            [
                'rel' => 'self',
                'href' => $item->getLink()
            ]
        );

        if (is_array($item->getEnclosures())) {
            foreach ($item->getEnclosures() as $enclosure) {
                $mapper = $this->getMapperCollection()->find($enclosure);
                $itemNode->appendChild($mapper->map($doc, $enclosure));
            }
        }

        if (is_array($item->getRelations())) {
            foreach ($item->getRelations() as $relation => $url) {
                $this->addSimpleTag(
                    $doc,
                    $itemNode,
                    'link',
                    null,
                    null,
                    [
                        'rel' => $relation,
                        'href' => $url,
                    ]
                );
            }
        }

        return $itemNode;
    }
}
