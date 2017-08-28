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

/**
 * Class AbstractCollectionItem
 * @package Benkle\FeedResponse\Collections
 * @internal
 */
abstract class AbstractCollectionItem
{
    /** @var int */
    private $priority;

    /**
     * AbstractCollectionItem constructor.
     * @param int $priority
     */
    public function __construct($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get the priority.
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Compare two item mappings by priority.
     * @param FeedItemMapperCollectionItem $itemMapping
     * @return int
     */
    public function compare(AbstractCollectionItem $itemMapping)
    {
        if ($this->priority < $itemMapping->priority) {
            return -1;
        } elseif ($this->priority > $itemMapping->priority) {
            return 1;
        } else {
            return 0;
        }
    }
}
