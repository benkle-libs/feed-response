An extendable, auto mapping, PSR-7 compliant feed generator
===========================================================



Requirements
------------

 * PHP 5.6+
 * [benkle/feed-interfaces](https://packagist.org/packages/benkle/feed-interfaces) 1.1+
 * [symfony/http-foundation](https://packagist.org/packages/symfony/http-foundation) 3.3+
 * [psr/http-message](https://packagist.org/packages/psr/http-message) 1.0+

Installation
------------

_FeedResponse_ can be included in the usual way with composer:

```sh
    composer require benkle/feed-response
```

Usage
-----

Feed responses are built using a factory, which requires a bit of a setup:

```php
use \Benkle\FeedInterfaces as FeedInterfaces;
use \Benkle\FeedResponse\XmlMappers\Atom as Atom;
use \Benkle\FeedResponse\XmlMappers\RSS20 as RSS20;
use \Benkle\FeedResponse\Collections as Collections;
use \Benkle\FeedResponse\FeedResponseFactory;

// You need two feed mappers, each with a collection of mappers
// for specific parts of the feed.

$atomMappers = new Collections\FeedItemMapperCollection();
$atomMappers
    ->add(ItemInterface::class, new Atom\FeedItemMapper())
    ->add(EnclosureInterface::class, new Atom\EnclosureMapper())
    ->add(RelationLinkInterface::class, new Atom\RelationLinkMapper());
$atomMapper = new Atom\FeedMapper();
$atomMapper->setMapperCollection($atomMappers);

$rssMappers = new Collections\FeedItemMapperCollection();
$rssMappers
    ->add(ItemInterface::class, new RSS20\FeedItemMapper())
    ->add(EnclosureInterface::class, new RSS20\EnclosureMapper())
    ->add(RelationLinkInterface::class, new Atom\RelationLinkMapper(true));
$rssMapper = new RSS20\FeedMapper();
$rssMapper->setMapperCollection($rssMappers);

$feedResponseFactory = new FeedResponseFactory();
$feedResponseFactory
    ->setAtomMapper($atomMapper)
    ->setObjectMappers($objectMappers)
    ->setRssMapper($rssMapper)
    ->setFeedPrototype(/* Insert an instance of \Benkle\FeedInterfaces\FeedInterface here */)
    ->setRelationLinkPrototype(/* Insert an instance of \Benkle\FeedInterfaces\RelationLinkInterface here */);
```

Afterwards you can simply create a response object with the apropriate methods:

```php
$atomFeed = $feedResponseFactory->atom($head, $items, $relations); // For an Atom feed
$rssFeed = $feedResponseFactory->rss($head, $items, $relations); // For an RSS 2.0 feed
```

These methods allow for some variation on it's three parameters:

 * `$head` can be...
   * an associative array with the following keys:
     | key         | RSS tag         | Atom tag         |
     |-------------|-----------------|------------------|
     | title       | `title`         | `title`          |
     | description | `description`   | `subtitle`       |
     | modified    | `lastBuildDate` | `updated`        |
     | link        | `link`          | `link[rel=self]` |
     | id          | `guid`          | `id`             |
   * an instance of `\Benkle\FeedInterfaces\FeedInterface`
 * `$items` is an array of any type of object.
   Instances of `\Benkle\FeedInterfaces\FeedItemInterface` will be handled directly,
   for any other type of object you'll also need a matching object mapper.
 * `$relations` is an array where elements can be any of these:
   * An instance of `\Benkle\FeedInterfaces\RelationLinkInterface`
   * A key-value pair that maps to this tag: `<link rel="key" href="value"/>`
   * an associative array with the following keys:
     | key                  | tag attribute |
     |----------------------|---------------|
     | url, href            | `href`        |
     | relationType, rel    | `rel`         |
     | mimeType, mime, type | `type`        |
     | title                | `title`       |

TODO
----

 * Moved included collection classes to a utility package
   (combine with `PriorityList` from [benkle/feed-parser](https://packagist.org/packages/benkle/feed-parser))
