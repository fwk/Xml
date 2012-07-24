<?php
namespace Fwk\Xml;

class Path
{
    protected $xpath;
    
    protected $default;
    
    protected $filter;
    
    protected $key;
    
    protected $loop = false;
    
    protected $loopId;
    
    protected $childrens = array();
    
    protected $attributes = array();
    
    public function __construct($xpath, $key = null, $default = null, \Closure $filter = null)
    {
        $this->xpath    = $xpath;
        $this->default  = $default;
        $this->filter   = $filter;
        $this->key      = $key;
    }
    
    public function loop($bool, $loopId = null) 
    {
        $this->loop     = $bool;
        $this->loopId   = $loopId;
        
        return $this;
    }
    
    public function key($name)
    {
        $this->key = $name;
        
        return $this;
    }
    
    public function xpath($xpath)
    {
        $this->xpath = $xpath;
    }

    public function filter(\Closure $filter)
    {
        $this->filter = $filter;
        
        return $this;
    }
    
    public function setDefault($default)
    {
        $this->default = $default;
        
        return $this;
    }
    
    public function getXpath()
    {
        return $this->xpath;
    }
    
    public function getDefault()
    {
        return $this->default;
    }

    /**
     *
     * @return \Closure 
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     *
     * @return boolean 
     */
    public function isLoop()
    {
        return $this->loop;
    }
    
    /**
     *
     * @param string   $xpath
     * @param string   $key
     * @param mixed    $default
     * @param \Closure $filter
     * 
     * @return self 
     */
    public static function factory($xpath, $key = null, $default = null, \Closure $filter = null)
    {
        return new self($xpath, $key, $default, $filter);
    }
    
    public function addChildren(Path $path) {
        $this->childrens[] = $path;
        
        return $this;
    }
    
    public function getChildrens()
    {
        return $this->childrens;
    }
    
    public function attribute($attrName, $key) {
        $this->attributes[$key] = $attrName;
        
        return $this;
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getLoopId() {
        return $this->loopId;
    }
}