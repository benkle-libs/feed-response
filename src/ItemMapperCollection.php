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

use Benkle\FeedInterfaces\NodeInterface;
use Benkle\FeedResponse\Exceptions\MapperNotFoundException;
use Benkle\FeedResponse\Interfaces\HasMapperCollectionInterface;
use Benkle\FeedResponse\Interfaces\ItemMapperInterface;

/**
 * Class ItemMapperCollection
 * @package Benkle\FeedResponse
 */
class ItemMapperCollection
{
    /**
     * @var ItemMapperInterface[]
     */
    private $itemMappers = [];

    /**
     * Add a new item mapper.
     * @param string $class
     * @param ItemMapperInterface $mapper
     * @return $this
     */
    public function add($class, ItemMapperInterface $mapper)
    {
        $this->itemMappers[$class] = $mapper;
        if ($mapper instanceof HasMapperCollectionInterface) {
            $mapper->setMapperCollection($this);
        }
        return $this;
    }

    /**
     * Remove an item mapper.
     * @param $class
     * @return $this
     */
    public function remove($class)
    {
        if (!isset($this->itemMappers[$class])) {
            throw new MapperNotFoundException($class);
        }
        if ($this->itemMappers[$class] instanceof HasMapperCollectionInterface) {
            $this->itemMappers[$class]->setMapperCollection(null);
        }
        unset($this->itemMappers[$class]);
        return $this;
    }

    /**
     * Find a mapper for a feed item.
     * @param NodeInterface $item
     * @return ItemMapperInterface
     * @throws MapperNotFoundException
     */
    public function find(NodeInterface $item)
    {
        $class = get_class($item);
        while ($class) {
            if (isset($this->itemMappers[$class])) {
                return $this->itemMappers[$class];
            }
            $class = get_parent_class($class);
        }
        $interfaces = class_implements($item);
        foreach ($interfaces as $interface) {
            if (isset($this->itemMappers[$interface])) {
                return $this->itemMappers[$interface];
            }
        }
        throw new MapperNotFoundException(get_class($item));
    }
}
