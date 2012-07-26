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
 * @category   Utilities
 * @package    Fwk\Xml
 * @subpackage Maps
 * @author     Julien Ballestracci <julien@nitronet.org>
 * @copyright  2011-2012 Julien Ballestracci <julien@nitronet.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpfwk.com
 */
namespace Fwk\Xml\Maps;

use Fwk\Xml\Map;
use Fwk\Xml\Path;

/**
 * Rss Xml Map
 * 
 * This Map helps the parsing of RSS files.
 * 
 * @category   Library
 * @package    Fwk\Xml
 * @subpackage Maps
 * @author     Julien Ballestracci <julien@nitronet.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpfwk.com
 */
class Rss extends Map
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->add(
            Path::factory('/rss/channel', 'channel')
            ->addChildren(Path::factory('title', 'title'))
            ->addChildren(Path::factory('link', 'link'))
            ->addChildren(Path::factory('description', 'description'))
            ->addChildren(Path::factory('language', 'language'))
            ->addChildren(Path::factory('lastBuildDate', 'lastBuildDate'))
            ->addChildren(Path::factory('sy:updatePeriod', 'updatePeriod'))
            ->addChildren(Path::factory('sy:updateFrequency', 'updateFrequency'))
            ->addChildren(Path::factory('generator', 'generator'))
            ->addChildren(
                Path::factory('image', 'image')
                ->addChildren(Path::factory('link', 'link'))
                ->addChildren(Path::factory('url', 'url'))
                ->addChildren(Path::factory('title', 'title'))
            )
        );

        $this->add(
            Path::factory('/rss/channel/item', 'items')
            ->loop(true)
            ->addChildren(Path::factory('title', 'title'))
            ->addChildren(Path::factory('link', 'link'))
            ->addChildren(Path::factory('comments', 'comments'))
            ->addChildren(Path::factory('pubDate', 'pubDate'))
            ->addChildren(Path::factory('dc:creator', 'creator'))
            ->addChildren(Path::factory('category', 'categories')->loop(true))
            ->addChildren(
                Path::factory('guid', 'guid')
                ->attribute('isPermaLink', 'permalink')
                ->value('value')
            )->addChildren(Path::factory('description', 'description'))
            ->addChildren(
                Path::factory('media:thumbnail', 'thumbnail')
                ->attribute('url')
            )
        );
    }
}