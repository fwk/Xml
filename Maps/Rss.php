<?php
namespace Fwk\Xml\Maps;

use Fwk\Xml\Map;
use Fwk\Xml\Path;

class Rss extends Map
{
    public function __construct()
    {
        $this->add(Path::factory('/rss/channel', 'channel')
            ->addChildren(Path::factory('title', 'title'))
            ->addChildren(Path::factory('link', 'link'))
            ->addChildren(Path::factory('description', 'description'))
            ->addChildren(Path::factory('language', 'language'))
            ->addChildren(Path::factory('lastBuildDate', 'lastBuildDate'))
            ->addChildren(Path::factory('sy:updatePeriod', 'updatePeriod'))
            ->addChildren(Path::factory('sy:updateFrequency', 'updateFrequency'))
            ->addChildren(Path::factory('generator', 'generator'))
            ->addChildren(
                Path::factory('image', 'image')
                ->addChildren(Path::factory('link', 'link'))
                ->addChildren(Path::factory('url', 'url'))
                ->addChildren(Path::factory('title', 'title'))
            )
        );

        $this->add(Path::factory('/rss/channel/item', 'items')
            ->loop(true)
            ->addChildren(Path::factory('title', 'title'))
            ->addChildren(Path::factory('link', 'link'))
            ->addChildren(Path::factory('comments', 'comments'))
            ->addChildren(Path::factory('pubDate', 'pubDate'))
            ->addChildren(Path::factory('dc:creator', 'creator'))
            ->addChildren(Path::factory('category', 'categories')->loop(true))
            ->addChildren(Path::factory('guid', 'guid')
                ->attribute('isPermaLink', 'permalink')
                ->value('value')
            )->addChildren(Path::factory('description', 'description'))
            ->addChildren(
                Path::factory('media:thumbnail', 'thumbnail')
                ->attribute('url')
            )
        );
    }
}