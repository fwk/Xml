<?php
namespace Fwk\Xml;


class Map
{
    /**
     *
     * @var array 
     */
    protected $paths = array();
    
    /**
     *
     * @param Path $path
     * @return Map 
     */
    public function add($path)
    {
        if (!is_array($path) && !$path instanceof Path) {
            throw new \InvalidArgumentException();
        }
        
        elseif (!is_array($path)) {
            $path = array($path);
        }
        
        foreach ($path as $current) {
            $this->paths[] = $current;
        }
        
        return $this;
    }
    
    /**
     *
     * @param Path $path
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
     *
     * @param XmlFile $file 
     * 
     * @return array
     */
    public function execute(XmlFile $file)
    {
        $paths = $this->paths;
        $final = array();
        
        foreach ($paths as $path) {
            $key = $path->getKey();
            if (empty($key)) {
                throw new Exceptions\MappingException(
                    'Invlid Path: key cannot be empty'
                );
            }
            
            $sxml = $file->xpath($path->getXpath());
            $value = $path->getDefault();
            if(count($sxml)) {
                $value = $this->pathToValue($sxml, $path);
            }
            
            $final[$key] = $value;
        }
        
        return $final;
    }
    
    /**
     *
     * @param array $sxml
     * @param Path $path
     * 
     * @return type 
     */ 
    protected function pathToValue(array $sxml, Path $path)
    {
        $value = null;
        
        foreach($sxml as $result) {
            $current = $this->getAttributesArray($path, $result);
            if($path->isLoop()) {
                if(!count($path->getAttributes())) {
                    $current = $this->getFilteredValue($path, trim((string)$result));
                } elseif(!count($path->getChildrens()) && $path->hasValueKey()) {
                    $current[$path->getValueKey()] = $this->getFilteredValue($path, trim((string)$result));
                } else {
                    $current += $this->getChildrens($path, $result);
                }
                
                $loopId = $path->getLoopId();
                if(empty($loopId)) {
                    $value[] = $current;
                } else {
                    $idValue = $result->xpath($loopId);
                    if(!count($idValue)) {
                        $value[] = $current;
                    } else {
                        $value[trim((string)$idValue[0])] = $current;
                    }
                }
                
            } else {
                if(!count($path->getAttributes()) && !count($path->getChildrens())) {
                    $current = $this->getFilteredValue($path, trim((string)$result));
                    if(empty($current)) {
                        $current = $path->getDefault();
                    }
                } else {
                    $current += $this->getChildrens($path, $result);
                }
                $value = $current;
            }
        }
        
        return $value;
    }
    
    /**
     *
     * @param Path   $path
     * @param string $value
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
     *
     * @param Path              $path
     * @param \SimpleXMLElement $node 
     * 
     * @return array
     */
    protected function getAttributesArray(Path $path, 
        \SimpleXMLElement $node)
    {
        $current = array();
        foreach($path->getAttributes() as $keyName => $attr) {
            $val = (isset($node[$attr]) ? trim((string)$node[$attr]) : null);
            $current[$keyName] = self::getFilteredValue($path, $val);
        }
        
        return $current;
    }
    
    protected function getChildrens(Path $path, \SimpleXMLElement $node)
    {
        $current = array();
        
        foreach ($path->getChildrens() as $child) {
            $key = $child->getKey();
            $csxml = $node->xpath($child->getXpath());
            $val = $this->pathToValue($csxml, $child);
            $current[$key] = (($val === null && $child->isLoop()) ? array() : $val);
        }
        
        return $current;
    }
}