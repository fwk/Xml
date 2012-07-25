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
 * @category  Utilities
 * @package   Fwk\Xml
 * @author    Julien Ballestracci <julien@nitronet.org>
 * @copyright 2011-2012 Julien Ballestracci <julien@nitronet.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://www.phpfwk.com
 */
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
    
    protected $valueKey;
    
    public function __construct($xpath, $key, $default = null, \Closure $filter = null)
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
    
    public function value($keyName)
    {
        $this->valueKey = $keyName;
        
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
    
    public function hasValueKey()
    {
        return (isset($this->valueKey) && !empty($this->valueKey));
    }
    
    public function hasFilter()
    {
        return is_callable($this->filter);
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
    
    public function attribute($attrName, $key = null) {
        if(null === $key) {
            $key = $attrName;
        }
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
    
    public function getValueKey()
    {
        return $this->valueKey;
    }
}