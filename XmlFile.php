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
 * Represents an existing XML file on filesystem. Uses SimpleXML.
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
        $rpt = \realpath($this->path);

        return ($rpt === false ? $this->path : $rpt);
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
                $error = sprintf(
                    "%s [%s]",
                    libxml_get_last_error()->message,
                    \libxml_get_last_error()->code
                );

                throw new Exceptions\XmlError($error);
            }
        }

        return $this->xml;
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

    /**
     *
     * @return \SimpleXMLElement
     */
    public function getSimpleXml()
    {
        return $this->open();
    }
}