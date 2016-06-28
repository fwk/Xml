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

use \SimpleXMLElement;

/**
 * Map
 *
 * Once executed on a XmlFile, it transforms XML data into an Array according
 * to defined Paths.
 *
 * @category Library
 * @package  Fwk\Xml
 * @author   Julien Ballestracci <julien@nitronet.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://www.nitronet.org/fwk
 */
class Map
{
    /**
     * List of Paths
     * @var array
     */
    protected $paths = array();

    /**
     * List of registered namespaces
     * @var array
     */
    protected $namespaces = array();

    /**
     * Adds a Path to this map
     *
     * @param Path|array $path Path (or list of Paths) to be added
     *
     * @throws \InvalidArgumentException if $path is not Path and not an array
     * @return Map
     */
    public function add($path)
    {
        if (!is_array($path)) {
            $path = array($path);
        }

        foreach ($path as $current) {
            if (!$current instanceof Path) {
                throw new \InvalidArgumentException('Argument is not a Path');
            }
            $this->paths[] = $current;
        }

        return $this;
    }

    /**
     * Removes a Path from this map
     *
     * @param Path $path Path object to be removed
     *
     * @return Map
     */
    public function remove(Path $path)
    {
        $paths = array();
        foreach ($this->paths as $current) {
            if ($current !== $path) {
                array_push($paths, $current);
            }
        }

        $this->paths = $paths;

        return $this;
    }

    /**
     * Registers a namespace prefix
     * 
     * @param string $nsPrefix     NS prefix 
     * @param string $namespaceUrl URL to namespace definition
     *
     * @return Map
     */
    public function registerNamespace($nsPrefix, $namespaceUrl)
    {
        $this->namespaces[$nsPrefix] = $namespaceUrl;

        return $this;
    }

    /**
     * Executes the Map against the XmlFile and return parsed values as a PHP
     * array.
     *
     * @param XmlFile $file The XML file
     *
     * @return array
     */
    public function execute(XmlFile $file)
    {
        $paths = $this->paths;
        $final = array();

        $this->registerNamespaces($file);

        foreach ($paths as $path) {
            $key    = $path->getKey();
            $sxml   = $file->xpath($path->getXpath());
            $value  = $path->getDefault();
            if (is_array($sxml) && count($sxml)) {
                $value = $this->pathToValue($sxml, $path);
            } 
            
            $final[$key]    = $value;
        }

        return $final;
    }

    /**
     * Transform a Path to a value.
     *
     * @param array $sxml Current Xpath result
     * @param Path  $path Current Path
     *
     * @return mixed
     */
    protected function pathToValue(array $sxml, Path $path)
    {
        $value = null;

        $this->registerNamespaces(null, $sxml);

        foreach ($sxml as $result) {
            $current = $this->getAttributesArray($path, $result);
            if ($path->isLoop()) {
                if (!count($path->getAttributes())
                    && !count($path->getChildrens())
                ) {
                    $current = $this->getFilteredValue($path, trim((string)$result));
                } elseif (!count($path->getChildrens()) && $path->hasValueKey()) {
                    $val = $this->getFilteredValue($path, trim((string)$result));
                    $current[$path->getValueKey()] = $val;
                } elseif (count($path->getChildrens())) {
                    $current += $this->getChildrens($path, $result);
                }

                $loopId = $path->getLoopId();
                if (empty($loopId)) {
                    $value[] = $current;
                } else {
                    $idValue = $result->xpath($loopId);
                    if (!count($idValue)) {
                        $value[] = $current;
                    } else {
                        $value[trim((string)$idValue[0])] = $current;
                    }
                }
            } else {
                if (!count($path->getAttributes())
                    && !count($path->getChildrens())
                ) {
                    $current = $this->getFilteredValue($path, trim((string)$result));
                    if (empty($current)) {
                        $current = $path->getDefault();
                    }
                } else {
                    $current += $this->getChildrens($path, $result);
                    if ($path->hasValueKey()) {
                        $current[$path->getValueKey()] = $this->getFilteredValue(
                            $path,
                            trim((string)$result)
                        );
                    }
                }
                $value = $current;
            }
        }

        return $value;
    }

    /**
     * Returns a value, filtered if needed.
     *
     * @param Path   $path  Current Path
     * @param string $value Actual value
     *
     * @return mixed
     */
    protected function getFilteredValue(Path $path, $value)
    {
        if ($path->hasFilter()) {
            return call_user_func($path->getFilter(), $value);
        }
        
        return $value;
    }


    /**
     * Returns Path attributes list
     *
     * @param Path             $path Current Path
     * @param SimpleXMLElement $node Current SimpleXML node
     *
     * @return array
     */
    protected function getAttributesArray(Path $path,
        SimpleXMLElement $node
    ) {
        $current = array();
        foreach ($path->getAttributes() as $keyName => $attr) {
            $val = (isset($node[$attr]) ? trim((string)$node[$attr]) : null);
            $current[$keyName] = self::getFilteredValue($path, $val);
        }

        return $current;
    }

    /**
     * Returns childrens values
     *
     * @param Path             $path Current Path
     * @param SimpleXMLElement $node Current SimpleXML node
     *
     * @return array
     */
    protected function getChildrens(Path $path, SimpleXMLElement $node)
    {
        $current = array();

        foreach ($path->getChildrens() as $child) {
            $key    = $child->getKey();
            $csxml  = $node->xpath($child->getXpath());
            $val    = $child->getDefault();
            
            if (is_array($csxml) && count($csxml)) {
                $val = $this->pathToValue($csxml, $child);
            } 
            
            $current[$key]  = $val;
            if ($val === null && $child->isLoop()) {
                $current[$key] = array();
            }
        }

        return $current;
    }

    /**
     * Apply rules for registered namespaces
     * 
     * @param XmlFile $file The current XmlFile object
     * @param array   $sxml SimpleXML results
     * 
     * @return void
     */
    protected function registerNamespaces(XmlFile $file = null, 
        array $sxml = null
    ) {
        if (null === $sxml && $file instanceof XmlFile) {
            foreach ($this->namespaces as $prefix => $ns) {
                $file->open()->registerXPathNamespace($prefix, $ns);
            }
        } elseif (null !== $sxml) {
            foreach ($sxml as $node) {
                foreach ($this->namespaces as $prefix => $ns) {
                    $node->registerXPathNamespace($prefix, $ns);
                }
            }
        }
    }

    /**
     * Returns Map's Paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}