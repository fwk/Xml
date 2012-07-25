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
            $sxml = $file->xpath($path->getXpath());
            $value = $this->pathToValue($sxml, $path);
            
            $final[$key] = $value;
        }
        
        return $final;
    }
    
    
    protected function pathToValue($sxml, Path $path)
    {
        $key = $path->getKey();
        if (empty($key)) {
            throw new Exceptions\MappingException(
                'Invlid Path: key cannot be empty'
            );
        }
        
        $value = $path->getDefault();
        if(!count($sxml)) {
            return $value;
        }
        
        if($path->hasFilter()) {
            $filter = $path->getFilter();
        } else {
            $filter = function($value) { return $value; };
        }
        
        $value = array();
        foreach($sxml as $result) {
            $current = array();
            foreach($path->getAttributes() as $keyName => $attr) {
                $val = (isset($result[$attr]) ? trim((string)$result[$attr]) : null);
                $current[$keyName] = $filter($val);
            }
            unset($val);
            if($path->isLoop()) {
                if(!count($path->getAttributes())) {
                    $current = $filter(trim((string)$result));
                } elseif(!count($path->getChildrens()) && $path->hasValueKey()) {
                    $current[$path->getValueKey()] = $filter(trim((string)$result));
                }
                foreach ($path->getChildrens() as $child) {
                    $key = $child->getKey();
                    $csxml = $result->xpath($child->getXpath());
                    $val = $this->pathToValue($csxml, $child);
                    $current[$key] = (($val === null && $child->isLoop()) ? array() : $val);
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
                foreach ($path->getChildrens() as $child) {
                    $key = $child->getKey();
                    $csxml = $result->xpath($child->getXpath());
                    $current[$key] = $this->pathToValue($csxml, $child);
                }
                if(!count($path->getAttributes()) && !count($path->getChildrens())) {
                    $current = $filter(trim((string)$result));
                    if(empty($current)) {
                        $current = null;
                    }
                } else {
                    foreach($path->getAttributes() as $keyName => $attr) {
                        $val = (isset($result[$attr]) ? (string)trim($result[$attr]) : null);
                        $current[$keyName] = $filter($val);
                    }
                }
                $value = $current;
            }
        }
        
        return $value;
    }
}