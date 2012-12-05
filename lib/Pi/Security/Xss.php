<?php
/**
 * Security check for Pi Engine
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           1.0
 * @package         Security
 * @version         $Id$
 */

namespace Pi\Security;

class Xss extends AbstractSecurity
{
    const MESSAGE = "Access denied by XSS check";
    protected static $filter = true;
    protected static $length = 0;

    /**
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */
    public static function check($options = null)
    {
        $filter = isset($options['filter']) ? $options['filter'] : static::$filter;
        static::$length = isset($options['length']) ? $options['length'] : static::$length;

        //$filter = false;
        if (!empty($options['post']) && !empty($_POST)) {
            if (static::checkXssRecursive($_POST, $filter) && !$filter) {
                return false;
            }
        }
        if (!empty($options['get']) && !empty($_GET)) {
            if (static::checkXssRecursive($_GET, $filter) && !$filter) {
                return false;
            }
        }

        return null;
    }

    public static function test()
    {
        $test = array(
            'xxx= "javascript: test',
            'yyy = \'vbscript: test',
            'zzz = "-moz-binding: test',
            'test @import(ssss)',
            '<test style="\'ase soee\' script:  ( seee )" good>',
        );
        static::$length = 1;
        static::checkXssRecursive($test);
    }

    public static function checkXssRecursive(& $content, $filter = true)
    {
        if (is_array($content)) {
            foreach ($content as $key => &$val) {
                if (static::checkXssRecursive($val, $filter) && !$filter) {
                    return true;
                }
            }
        } else {
            return static::checkXss($content, $filter);
        }
    }

    /**
     * Check XSS code
     *
     * @link: http://ha.ckers.org/xss.html
     * Inspired by:
     *  4images: http://phpxref.com/xref/4images/global.php.source.txt
     *  Daniel Morris: http://www.phpclasses.org/browse/file/9402.html
     *  kallahar@quickwired.com's RemoveXSS
     *  htmlLawed
     *  HTMLpurifier
     *  etc.
     *
     * @param string    $content the text to be checked
     * @param bool      $filter to filter malicious code or just return status
     * @return mixed
     */
    public static function checkXss(&$content, $filter = true)
    {
        if (!is_string($content) || (static::$length && strlen($content) < static::$length)) {
            return $filter ? $content : null;
        }

        // convert decimal
        $patterns[] = '/&#(\d+)/me';
        $replaces[] = "chr(\\1)";

        // convert hex
        $patterns[] = '/&#x([a-f0-9]+)/mei';
        $replaces[] = "chr(0x\\1)";

        $patterns[] = '/(&#*\w+)[\x00-\x20]+;/U';
        $replaces[] = "\\1;";

        // Remove any attribute starting with "on" or xmlns
        $patterns[] = '/(<[^>]+[\x01-\x20\"\'])(on|xmlns)[^>]*>/iU';
        $replaces[] = "\\1>";

        // Remove all control (i.e. with ASCII value lower than 0x20 (space),
        // except of 0x09 (tabulator) and 0x0A (line feed)
        $patterns[] = '/([\x00-\x08][\x0b-\x0c][\x0e-\x1f])/';
        $replaces[] = '';

        $c = "[\x01-\x20]*";
        // Remove javascript:, vbscript:, about:, moz-binding and xss: protocol
        $script = "j{$c}a{$c}v{$c}a{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t";
        $patterns[] = "/([a-z]*){$c}={$c}([\`\'\"]*){$c}{$script}{$c}:/iU";
        $replaces[] = '\\1=\\2noscript:';
        $script = "v{$c}b{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t|a{$c}b{$c}o{$c}u{$c}t|x{$c}s{$c}s|-moz-binding";
        $patterns[] = "/([a-z]*){$c}={$c}([\'\"]*){$c}({$script}){$c}:/iU";
        $replaces[] = '\\1=\\2noscript:';

        // @import
        $patterns[] = "/([a-z]*){$c}([\\\]*){$c}@([\\\]*){$c}i([\\\]*){$c}m([\\\]*){$c}p([\\\]*){$c}o([\\\]*){$c}r([\\\]*){$c}t/iU";
        $replaces[] = '\\1@noimport';

        // <span style="width: expression|behaviour( ... );"></span>
        // for ie
        //$patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]*).*(e{$c}x{$c}p{$c}r{$c}e{$c}s{$c}s{$c}i{$c}o{$c}n|b{$c}e{$c}h{$c}a{$c}v{$c}i{$c}o{$c}u{$c}r){$c}\([^>]*>/iU";
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*(e{$c}x{$c}p{$c}r{$c}e{$c}s{$c}s{$c}i{$c}o{$c}n|b{$c}e{$c}h{$c}a{$c}v{$c}i{$c}o{$c}u{$c}r){$c}\(.*\\2(.*)>/iU";
        $replaces[] = "\\1\\4>";

        // <span style="script: "></span>
        //$patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]*).*s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}:*[^>]*>/iU";
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}: .*\\2(.*)>/iU";
        $replaces[] = "\\1\\3>";

        if ($filter) {
            $content = preg_replace($patterns, $replaces, $content);
            return $content;
        } else {
            return null;
        }
    }
}
