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


namespace Benkle\FeedResponse\Traits;

/**
 * Trait XMLUtilitiesTrait
 * @package Benkle\FeedResponse\Traits
 */
trait XMLUtilitiesTrait
{
    /**
     * Add a simple tag to a DOM node.
     * @param \DOMDocument $doc
     * @param \DOMNode $parent
     * @param string $name
     * @param string $value
     * @param null|string $namespace
     * @param string[] $attributes
     * @return \DOMElement
     */
    private function addSimpleTag(\DOMDocument $doc, \DOMNode $parent, $name, $value, $namespace = null, $attributes = [])
    {
        $node = is_string($namespace) ? $doc->createElementNS($namespace, $name) : $doc->createElement($name);
        foreach ($attributes as $attribute => $attributeValue) {
            $node->setAttribute($attribute, $attributeValue);
        }
        if (isset($value)) {
            $value = $doc->createTextNode($value);
            $node->appendChild($value);
        }
        $parent->appendChild($node);
        return $node;
    }
}
