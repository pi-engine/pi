<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Security;

/**
 * Cross site scripting check
 *
 * @link: http://ha.ckers.org/xss.html
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Xss extends AbstractAdapter
{
    /** @var string */
    const MESSAGE = 'Access denied by XSS check';

    /**
     * To filter malicious code
     * @var bool
     */
    protected static $filter = true;

    /**
     * Minimum length of content to check
     * @var int
     */
    protected static $length = 0;

    /**
     * {@inheritDoc}
     */
    public static function check($options = array())
    {
        $filter = isset($options['filter'])
            ? $options['filter'] : static::$filter;
        static::$length = isset($options['length'])
            ? $options['length'] : static::$length;

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

    /**
     * Recursive check against XSS
     *
     * @return void
     */
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

    /**
     * Check XSS recursively
     *
     * @param string|array  $content    String or associative array
     * @param bool          $filter     To filter malicious code
     * @return bool
     */
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
     *
     * Inspired by:
     *
     *  - 4images: http://phpxref.com/xref/4images/global.php.source.txt
     *  - Daniel Morris: http://www.phpclasses.org/browse/file/9402.html
     *  - kallahar@quickwired.com's RemoveXSS
     *  - htmlLawed
     *  - HTMLpurifier
     *
     * @param string    $content    Text to be checked
     * @param bool      $filter     Filter malicious code or just return status
     * @return string|null
     */
    public static function checkXss(&$content, $filter = true)
    {
        if (!is_string($content)
            || (static::$length && strlen($content) < static::$length)
        ) {
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
        $script = "v{$c}b{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t|a{$c}b{$c}o{$c}u{$c}t"
                . "|x{$c}s{$c}s|-moz-binding";
        $patterns[] = "/([a-z]*){$c}={$c}([\'\"]*){$c}({$script}){$c}:/iU";
        $replaces[] = '\\1=\\2noscript:';

        // @import
        $patterns[] = "/([a-z]*){$c}([\\\]*){$c}@([\\\]*){$c}i([\\\]*){$c}m"
                    . "([\\\]*){$c}p([\\\]*){$c}o([\\\]*){$c}r"
                    . "([\\\]*){$c}t/iU";
        $replaces[] = '\\1@noimport';

        // <span style="width: expression|behaviour( ... );"></span>
        // for ie
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*"
                    . "(e{$c}x{$c}p{$c}r{$c}e{$c}s{$c}s{$c}i{$c}o{$c}n"
                    . "|b{$c}e{$c}h{$c}a{$c}v{$c}i{$c}o{$c}u{$c}r)"
                    . "{$c}\(.*\\2(.*)>/iU";
        $replaces[] = "\\1\\4>";

        // <span style="script: "></span>
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*"
                    . "s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}: .*\\2(.*)>/iU";
        $replaces[] = "\\1\\3>";

        if ($filter) {
            $content = preg_replace($patterns, $replaces, $content);

            return $content;
        } else {
            return null;
        }
    }
}
