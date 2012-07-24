<?php
namespace Fwk\Xml;

class XmlFile
{
    /**
     * @var string 
     */
    protected $path;

    /**
     * @var SimpleXMLElement
     */
    protected $xml;
    
    /**
     *
     * @param string $filePath
     *
     * @return void
     */
    public function __construct($filePath)
    {
        if(is_dir($filePath)) {
            throw new \InvalidArgumentException(
                sprintf("File %s is a directory", $filePath)
            );
        }

        if (\function_exists('libxml_use_internal_errors')) {
            \libxml_use_internal_errors(true);
        }
        
        $this->path = $filePath;
    }

    /**
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getRealPath());
    }

    /**
     *
     * @return string 
     */
    public function getRealPath()
    {
        $rp = \realpath($this->path);

        return ($rp === false ? $this->path : $rp);
    }
    
    /**
     *
     * @return boolean
     */
    public function isReadable()
    {
        return ($this->exists() ? is_readable($this->path) : false);
    }
    
    /**
     * 
     * @return \SimpleXMLElement
     */
    public function open()
    {
        if (!isset($this->xml)) {
            if (!$this->isReadable()) {
                throw new Exceptions\FileNotFound(
                    "XML file not found/readable: ". $this->path
                );
            }
            
            if (!$this->xml = simplexml_load_file($this->getRealPath())) {
                if (libxml_get_last_error() === false) {
                    $error = 'Unknown Error';
                } else {
                    $error = sprintf(
                        "%s [%s]", 
                        libxml_get_last_error()->message, 
                        \libxml_get_last_error()->code
                    );
                }

                throw new Exceptions\XmlError($error);
            }
        }
        
        return $this->xml;
    }
    
    /**
     * validateSchema
     * validate XML against a given schema
     *
     * @param string $schema path to schema
     * 
     * @return boolean
     */
    public function validateSchema($schema)
    {
        $dom = new \DOMDocument();
        if(!$dom->loadXML($this->open()->asXML())) {
            throw new Exception('Unable to import XML into DOMDocument');
        }
        
        try {
            $result = (is_file($schema) ?
                $dom->schemaValidate($schema) :
                $dom->schemaValidateSource($schema));

        } catch(\Exception $e) {
            throw new Exceptions\ValidationError($e->getMessage());
        }

        if($result != true) {
            $last = \libxml_get_last_error();

            // DIRTY !
            if(strpos($last->message, 'No matching global declaration available for the validation root') === false) {
                throw new Exceptions\ValidationError($this->cleanErrorMessage(\libxml_get_last_error()->message .' (line '. libxml_get_last_error()->line .')'));
            }
        }

        return true;
    }
    
    
    /**
     *
     * @param string $schemasDir
     * 
     * @return boolean
     */
    public function validateSchemas($schemasDir)
    {
        $dir = \rtrim($schemasDir, '/');
        $ns = $this->open()->getNamespaces(true);
        if(!is_array($ns)) {
            return true;
        }

        $valid = true;
        foreach($ns as $schema) {
            if(\strpos($schema, '.xsd') != false) {
                $look = \preg_match('/([a-zA-Z0-9\_\.\/])+\.xsd/i', $schema, $matches);
                $theSchema = (isset($matches[0]) ? $matches[0] : null);

                if(empty($theSchema)) {
                    continue;
                }

                try {
                    $this->validateSchema($dir . \DIRECTORY_SEPARATOR . $theSchema);
                } catch(Exceptions\ValidationError $e) {
                    $valid = false;
                }
            }
        }

        if(!$valid) {
            throw new Exceptions\ValidationError($this->cleanErrorMessage(\libxml_get_last_error()->message .' (line '. libxml_get_last_error()->line .')'));
        }
        
        return true;
    }
    
    /**
     *
     * @param string $message
     *
     * @return string
     */
    protected function cleanErrorMessage($message)
    {
        // remove schema's path to get readable message
        $schema = preg_match('/([a-zA-Z0-9\_\.])+\.xsd/im', $message, $matches);
        $schemaName = (isset($matches[0]) ? $matches[0] : 'unknown-schema');
        $msg = \preg_replace('/\{[a-zA-Z0-9\/\:\.\_]+\}/im',  '', $message);

        return '['. $schemaName .'] ' . trim($msg);
    }
    
    

    /**
     *
     * @param integer $level
     * 
     * @return array
     */
    public function getAllValidationErrors($level = 0)
    {
        $errors = \libxml_get_errors();
        if(!\is_array($errors)) {
            return array();
        }

        $err = array();
        foreach($errors as $msg) {
            if($msg->level >= $level) {
                $err[] = $this->cleanErrorMessage($msg->message .' (line '. $msg->line .')');
            }
        }

        return $err;
    }

    /**
     *
     * @return string : XML
     */
    public function __toString()
    {
        return $this->open()->asXML();
    }
    
    /**
     *
     * @param string $var Element XML
     *
     * @return mixed
     */
    public function __get($var)
    {
        return $this->open()->{$var};
    }

    /**
     *
     * @param string $path
     * 
     * @return mixed
     */
    public function xpath($path)
    {
        return $this->open()->xpath($path);
    }
} 