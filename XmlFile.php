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

/**
 * XmlFile 
 * 
 * Represents an existing XML file on filesystem. Uses mostly SimpleXML except
 * for schemas validation (DOM).
 * 
 * @category Library
 * @package  Fwk\Xml
 * @author   Julien Ballestracci <julien@nitronet.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://www.phpfwk.com
 */
class XmlFile
{
    /**
     * Path to XML file
     * 
     * @var string 
     */
    protected $path;

    /**
     * Root SimpleXML node
     * 
     * @var SimpleXMLElement
     */
    protected $xml;
    
    /**
     * Constructor
     * 
     * @param string $filePath Path to XML file
     *
     * @throws \InvalidArgumentException if path links to a directory
     * @return void
     */
    public function __construct($filePath)
    {
        if (is_dir($filePath)) {
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
     * Tells if the file exists
     * 
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getRealPath());
    }

    /**
     * Try to return the real path of the file. If not possible,
     * returns user-submitted path (@see __construct)
     * 
     * @return string 
     */
    public function getRealPath()
    {
        $rp = \realpath($this->path);

        return ($rp === false ? $this->path : $rp);
    }
    
    /**
     * Tells if the file is readable
     * 
     * @return boolean
     */
    public function isReadable()
    {
        return ($this->exists() ? is_readable($this->path) : false);
    }
    
    /**
     * Opens the XML file (if not already done) and return the SimpleXML root
     * node.
     * 
     * @throws Exceptions\FileNotFound If file not found/readable
     * @throws Exceptions\XmlError     If XML errors were found (libxml)
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
     * @throws Exception                  If unable to load DOM document
     * @throws Exceptions\ValidationError If validation errors were found 
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
     * @throws Exception                  If unable to load DOM document
     * @throws Exceptions\ValidationError If validation errors were found 
     * @return boolean
     */
    public function validateSchemas($schemasDir)
    {
        $dir = \rtrim($schemasDir, '/');
        $ns = $this->open()->getNamespaces(true);
        if (!is_array($ns)) {
            return true;
        }

        $valid = true;
        foreach ($ns as $schema) {
            if (\strpos($schema, '.xsd') != false) {
                $look = \preg_match(
                    '/([a-zA-Z0-9\_\.\/])+\.xsd/i', 
                    $schema, 
                    $matches
                );
                $theSchema = (isset($matches[0]) ? $matches[0] : null);

                if (empty($theSchema)) {
                    continue;
                }

                try {
                    $this->validateSchema(
                        $dir . \DIRECTORY_SEPARATOR . $theSchema
                    );
                } catch(Exceptions\ValidationError $e) {
                    $valid = false;
                }
            }
        }

        if(!$valid) {
            throw new Exceptions\ValidationError(
                $this->cleanErrorMessage(
                    sprintf("%s (line %u)", 
                        libxml_get_last_error()->message, 
                        libxml_get_last_error()->line
                    )
                )
            );
        }
        
        return true;
    }
    
    /**
     * Cleans a libxml error message
     * 
     * @param string $message raw libxml error message
     *
     * @internal
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
     * Returns all validation errors >= $level
     * 
     * @param integer $level Error level (0 -> 3)
     * 
     * @return array
     */
    public function getAllValidationErrors($level = 0)
    {
        $errors = libxml_get_errors();
        if (!\is_array($errors)) {
            return array();
        }

        $err = array();
        foreach ($errors as $msg) {
            if ($msg->level >= $level) {
                $err[] = $this->cleanErrorMessage(
                    sprintf("%s (line %u)", $msg->message, $msg->line)
                );
            }
        }

        return $err;
    }

    /**
     * Returns the contents of the XML file
     *  
     * @return string
     */
    public function __toString()
    {
        return $this->open()->asXML();
    }
    
    /**
     * SimpleXML wrapper to access XML nodes the simple way: $file->node->name
     * 
     * @param string $var XML node name
     *
     * @return mixed
     */
    public function __get($var)
    {
        return $this->open()->{$var};
    }

    /**
     * SimpleXML wrapper to do a Xpath query on the file.
     * 
     * @param string $path Path to XML node
     * 
     * @return mixed
     */
    public function xpath($path)
    {
        return $this->open()->xpath($path);
    }
}