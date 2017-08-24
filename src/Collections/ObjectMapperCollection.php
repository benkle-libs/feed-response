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

use Benkle\FeedResponse\Exceptions\MapperNotFoundException;
use Benkle\FeedResponse\Interfaces\ObjectMapperInterface;

/**
 * Class ObjectMapperCollection
 * @package Benkle\FeedResponse\Collections
 */
class ObjectMapperCollection extends AbstractCollection
{
    /**
     * Add a new item mapper.
     * @param string $class
     * @param ObjectMapperInterface $mapper
     * @param int $priority
     * @return ObjectMapperCollection
     */
    public function add($class, ObjectMapperInterface $mapper, $priority = 10)
    {
        $item = new ObjectMapperCollectionItem($mapper, $priority);
        $this->addItem($class, $item);
        return $this;
    }

    /**
     * Remove an item mapper.
     * @param $class
     * @return ObjectMapperCollection
     */
    public function remove($class)
    {
        $item = $this->removeItem($class);
        return $this;
    }

    /**
     * Find a mapper for a feed item.
     * @param object $node
     * @return ObjectMapperInterface
     * @throws MapperNotFoundException
     */
    public function find($node)
    {
        /** @var ObjectMapperCollectionItem $item */
        $item = parent::findItem($node);
        return $item->getMapper();
    }
}
