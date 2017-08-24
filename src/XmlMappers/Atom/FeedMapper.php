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


use Benkle\FeedInterfaces\FeedInterface;
use Benkle\FeedResponse\Interfaces\FeedMapperInterface;
use Benkle\FeedResponse\Interfaces\HasMapperCollectionInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;
use Benkle\FeedResponse\Traits\HasMapperCollectionTrait;
use Benkle\FeedResponse\Traits\XMLUtilitiesTrait;

/**
 * Class FeedMapper
 * @package Benkle\FeedResponse\XmlMappers\Atom
 */
class FeedMapper implements HasMapperCollectionInterface, FeedMapperInterface
{
    use XMLUtilitiesTrait, HasMapperCollectionTrait;

    /**
     * Map a feed to a DOM document.
     * @param FeedInterface $feed
     * @return \DOMDocument
     */
    public function map(FeedInterface $feed)
    {
        $result = new \DOMDocument();
        $root = $result->createElementNS('http://www.w3.org/2005/Atom', 'feed');
        $result->appendChild($root);

        $this->addSimpleTag($result, $root, 'title', $feed->getTitle());
        $this->addSimpleTag($result, $root, 'id', $feed->getPublicId());
        $this->addSimpleTag($result, $root, 'updated', $feed->getLastModified()->format(\DateTime::ATOM));

        $description = $feed->getDescription();
        if (isset($description)) {
            $this->addSimpleTag($result, $root, 'subtitle', $description);
        }

        foreach ($feed->getRelations() as $relation => $url) {
            $this->addSimpleTag(
                $result,
                $root,
                'link',
                null,
                null,
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
                $root->appendChild($mapper->map($result, $item));
            }
        }

        return $result;
    }
}
