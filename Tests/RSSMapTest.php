<?php

namespace Fwk\Xml;

/**
 * Test class for XmlFile.
 * Generated by PHPUnit on 2012-07-25 at 22:30:05.
 */
class RSSMapTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var XmlFile
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new XmlFile(__DIR__.'/rss-techcrunch.xml');
    }

    /**
     */
    public function testExists() {
        $this->assertTrue($this->object->exists());
    }

    public function testChannelDescription()
    {
        $map = new Maps\Rss();
        $result = $map->execute($this->object);
        
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('channel', $result);
        $this->assertTrue(is_array($result['channel']));
        
        $chan = $result['channel'];
        $this->assertArrayHasKey('title', $chan);
        $this->assertEquals('TechCrunch', $chan['title']);
        
        $this->assertTrue(is_array($chan['image']));
        $this->assertArrayHasKey('link', $chan['image']);
        $this->assertArrayHasKey('url', $chan['image']);
        $this->assertArrayHasKey('title', $chan['image']);
    }
    
    public function testChannelItems()
    {
        $map = new Maps\Rss();
        $result = $map->execute($this->object);
        
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('items', $result);
        $this->assertTrue(is_array($result['items']));
        $this->assertEquals(20, count($result['items']));
        $this->assertArrayHasKey('title', $result['items'][0]);
        $this->assertArrayHasKey('link', $result['items'][0]);
        $this->assertArrayHasKey('pubDate', $result['items'][0]);
        $this->assertArrayHasKey('categories', $result['items'][0]);
        $this->assertTrue(is_array($result['items'][0]['categories']));
    }
    
    public function testWithRegisteredNamespaces()
    {
        $map = new Maps\Rss();
        $result = $map->execute($this->object);
        $map->add(Path::factory('/rss/channel/feedburner:feedFlare', 'feedFlare')->loop(true));
        
        $this->assertTrue(is_array($result));
        $this->assertFalse(array_key_exists('feedFlare', $result));
        
        $map->registerNamespace('feedburner', "http://rssnamespace.org/feedburner/ext/1.0");
        $result2 = $map->execute($this->object);
        $this->assertArrayHasKey('feedFlare', $result2);
    }
}
