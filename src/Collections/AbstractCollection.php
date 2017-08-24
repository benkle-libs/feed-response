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
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;

/**
 * Class AbstractCollection
 * @package Benkle\FeedResponse\Collections
 */
abstract class AbstractCollection
{
    /**
     * @var AbstractCollectionItem[]
     */
    private $items = [];

    /**
     * Add a new item.
     * @param string $class
     * @param ItemMapperInterface $mapper
     * @param int $priority
     * @return AbstractCollection
     */
    public function addItem($class, AbstractCollectionItem $item)
    {
        $this->items[$class] = $item;
        uasort(
            $this->items, function (ItemMapperCollectionItem $a, ItemMapperCollectionItem $b) {
            return $a->compare($b);
        }
        );
        return $this;
    }

    /**
     * Remove an item.
     * @param string $class
     * @return AbstractCollectionItem
     */
    public function removeItem($class)
    {
        if (!isset($this->items[$class])) {
            throw new MapperNotFoundException($class);
        }
        $result = $this->items[$class];
        unset($this->items[$class]);
        return $result;
    }

    /**
     * Find a mapper for a feed item.
     * @param Object $item
     * @return AbstractCollectionItem
     * @throws MapperNotFoundException
     */
    public function findItem($item)
    {
        foreach ($this->items as $itemClass => $itemMapping) {
            if ($item instanceof $itemClass) {
                return $itemMapping;
            }
        }
        throw new MapperNotFoundException(get_class($item));
    }
}
