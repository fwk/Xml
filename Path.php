<?php
/**
 * Fwk
 *
 * Copyright (c) 2011-2012, Julien Ballestracci <julien@nitronet.org>.
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP Version 5.3
 *
 * @category  XML
 * @package   Fwk\Xml
 * @author    Julien Ballestracci <julien@nitronet.org>
 * @copyright 2011-2014 Julien Ballestracci <julien@nitronet.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://www.nitronet.org/fwk
 */
namespace Fwk\Xml;

/**
 * Path 
 * 
 * Represents a Path (xpath) and describes how values/attributes should be used.
 * 
 * @category Library
 * @package  Fwk\Xml
 * @author   Julien Ballestracci <julien@nitronet.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://www.nitronet.org/fwk
 */
class Path
{
    /**
     * Xpath to element
     * @var string 
     */
    protected $xpath;
   
    /**
     * Default value
     * @var mixed
     */
    protected $default;
    
    /**
     * Filter function
     * @var \Closure 
     */
    protected $filter;
    
    /**
     * Key name for this Path
     * @var type 
     */
    protected $key;
    
    /**
     * Should we loop on this path ? 
     * @var type 
     */
    protected $loop = false;
    
    /**
     * Xpath to a value used as a key identifier when looping
     * @var string 
     */
    protected $loopId;
    
    /**
     * List of sub-elements (Paths)
     * @var array 
     */
    protected $childrens = array();
    
    /**
     * List of attributes to fetch
     * @var array
     */
    protected $attributes = array();
    
    /**
     * Key used for main element's value
     * @var string 
     */
    protected $valueKey;
    
    /**
     * Constructor
     * 
     * @param string   $xpath   XPath to element
     * @param string   $key     Key name
     * @param mixed    $default Default value if element not defined
     * @param \Closure $filter  Filtering function
     * 
     * @return void
     */
    public function __construct($xpath, $key, $default = null, 
        \Closure $filter = null
    ) {
        $this->xpath    = $xpath;
        $this->default  = $default;
        $this->filter   = $filter;
        $this->key      = $key;
    }
    
    /**
     * Defines looping on this path
     * 
     * @param boolean $bool   Should we loop on this element ?
     * @param string  $loopId Xpath to key identifier
     * 
     * @return Path 
     */
    public function loop($bool, $loopId = null) 
    {
        $this->loop     = $bool;
        $this->loopId   = $loopId;
        
        return $this;
    }

    /**
     * Defines a filtering function applied on the value
     * 
     * @param \Closure $filter Value filtering function
     * 
     * @return Path 
     */
    public function filter(\Closure $filter)
    {
        $this->filter   = $filter;
        
        return $this;
    }
    
    /**
     * Tells the Map to fetch the element's value, in $keyName
     * 
     * @param string $keyName Key name where the value will be stored
     * 
     * @return Path 
     */
    public function value($keyName)
    {
        $this->valueKey = $keyName;
        
        return $this;
    }
    
    /**
     * Defines a default value in case element is empty
     * 
     * @param mixed $default Default value
     * 
     * @return Path 
     */
    public function setDefault($default)
    {
        $this->default  = $default;
        
        return $this;
    }
    
    /**
     * Returns the Xpath to the element
     * 
     * @return string
     */
    public function getXpath()
    {
        return $this->xpath;
    }
    
    /**
     * Returns the defined default value (if any)
     * 
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns the filter function (if any)
     * 
     * @return \Closure 
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Return the key of this Path
     * 
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Tells if we should loop on this xpath
     * 
     * @return boolean 
     */
    public function isLoop()
    {
        return $this->loop;
    }
    
    /**
     * Tells if we should store the element's main value
     * 
     * @return boolean 
     */
    public function hasValueKey()
    {
        return (isset($this->valueKey) && !empty($this->valueKey));
    }
    
    /**
     * Tells if a filter function has been defined
     * 
     * @return boolean
     */
    public function hasFilter()
    {
        return is_callable($this->filter);
    }
    
    /**
     * Factory method (helps chaining)
     * 
     * @param string   $xpath   XPath to element
     * @param string   $key     Key name
     * @param mixed    $default Default value if element not defined
     * @param \Closure $filter  Filtering function
     * 
     * @return Path
     */
    public static function factory($xpath, $key, $default = null, 
        \Closure $filter = null
    ) {
        return new self($xpath, $key, $default, $filter);
    }
    
    /**
     * Adds a child Path
     * 
     * @param Path $path Child Path
     * 
     * @return Path 
     */
    public function addChildren(Path $path)
    {
        $this->childrens[]  = $path;
        
        return $this;
    }
    
    /**
     * Returns all child Paths 
     * 
     * @return array
     */
    public function getChildrens()
    {
        return $this->childrens;
    }
    
    /**
     * Tells the Map to fetch an attribute of this path.
     * If no $key is defined the $attrName is used.
     * 
     * @param string $attrName Attribute name
     * @param string $key      Attribute key name
     * 
     * @return Path 
     */
    public function attribute($attrName, $key = null)
    {
        if (null === $key) {
            $key = $attrName;
        }
        
        $this->attributes[$key] = $attrName;
        
        return $this;
    }
    
    /**
     * Returns all attributes
     * 
     * @return array 
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Returns the loop key identifiers
     * 
     * @return string
     */
    public function getLoopId()
    {
        return $this->loopId;
    }
    
    /**
     * Returns the key identifier where the root value is stored
     * 
     * @return string 
     */
    public function getValueKey()
    {
        return $this->valueKey;
    }
}