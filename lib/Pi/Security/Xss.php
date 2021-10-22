<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Security;

//use Pi\Filter\XssSanitizer;
use voku\helper\AntiXSS;

/**
 * Cross site scripting check
 *
 * @link   : http://ha.ckers.org/xss.html
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Xss extends AbstractAdapter
{
    /** @var string */
    const MESSAGE = 'Access denied by XSS check';

    /**
     * To filter malicious code
     *
     * @var bool
     */
    //protected static $filter = true;

    /**
     * Minimum length of content to check
     *
     * @var int
     */
    //protected static $length = 0;

    /**
     * {@inheritDoc}
     */
    public static function check($options = [])
    {
        //$filter         = isset($options['filter']) ? $options['filter'] : static::$filter;
        //static::$length = isset($options['length']) ? $options['length'] : static::$length;

        if (isset($options['post']) && !empty($options['post']) && isset($_POST) && !empty($_POST)) {
            return static::checkXssRecursive($_POST/*, $filter*/);
        }
        if (isset($options['get']) && !empty($options['get']) && isset($_GET) && !empty($_GET)) {
            return static::checkXssRecursive($_GET/*, $filter*/);
        }

        return true;
    }

    /**
     * Recursive check against XSS
     *
     * @return void
     */
    /* public static function test()
    {
        $test           = [
            'xxx= "javascript: test',
            'yyy = \'vbscript: test',
            'zzz = "-moz-binding: test',
            'test @import(ssss)',
            '<test style="\'ase soee\' script:  ( seee )" good>',
        ];
        static::$length = 1;
        static::checkXssRecursive($test);
    } */

    /**
     * Check XSS recursively
     *
     * @param string|array $content String or associative array
     * @param bool         $filter  To filter malicious code
     *
     * @return bool
     */
    public static function checkXssRecursive(&$content/*, $filter = true*/)
    {
        if (is_array($content)) {
            foreach ($content as $key => &$val) {
                $result = static::checkXssRecursive($val/*, $filter*/);
                if (!$result) {
                    return $result;
                }
            }
            return true;
        } else {
            return static::checkXss($content/*, $filter*/);
        }
    }

    /**
     * Check XSS code
     *
     * @param string $content Text to be checked
     * @param bool   $filter  Filter malicious code or just return status
     *
     * @return string|null
     */
    public static function checkXss(&$content/*, $filter = true*/)
    {
        $antiXss = new AntiXSS();
        $antiXss->xss_clean($content);
        if ($antiXss->isXssFound()) {
            return false;
        }
        return true;

        /* if (!is_string($content) || (static::$length && strlen($content) < static::$length)) {
            return $filter ? $content : null;
        } */

        /* if ($filter) {
            $xssFilter = new XssSanitizer;
            return  $xssFilter->filter($content);
        } else {
            return;
        } */

        // Remove NULL bytes
        /* $content = str_replace("\0", '', $content);

        $patterns = [];
        $replaces = [];

        // Convert decimal
        // Disabled temporarily for issue #1144
        // on GitHub: https://github.com/pi-engine/pi/issues/1144
        //$patterns[] = '/&#(\d+)/me';
        //$replaces[] = "chr(\\1)";

        // Convert hex
        //$patterns[] = '/&#x([a-f0-9]+)/mei';
        //$replaces[] = "chr(0x\\1)";
        $content = preg_replace_callback(
            '/&#x([a-f0-9]+)/mi',
            function ($matches) {
                return "chr(0x" . $matches[1] . ")";
            },
            $content
        );

        $patterns[] = '/(&#*\w+)[\x00-\x20]+;/U';
        $replaces[] = "\\1;";

        // Remove any attribute starting with `on` or `xmlns`
        $patterns[] = '/(<[^>]+[\x01-\x20\"\'])(on|xmlns)[^>]*>/iU';
        $replaces[] = "\\1>";

        // Remove all control (i.e. with ASCII value lower than 0x20 (space),
        // except of 0x09 (tabulator) and 0x0A (line feed)
        $patterns[] = '/([\x00-\x08][\x0b-\x0c][\x0e-\x1f])/';
        $replaces[] = '';

        $c = "[\x01-\x20]*";
        // Remove `javascript:`, `vbscript:`, `about:`, `moz-binding` and `xss:` protocol
        $script     = "j{$c}a{$c}v{$c}a{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t";
        $patterns[] = "/([a-z]*){$c}={$c}([\`\'\"]*){$c}{$script}{$c}:/iU";
        $replaces[] = '\\1=\\2noscript:';
        $script     = "v{$c}b{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t|a{$c}b{$c}o{$c}u{$c}t"
            . "|x{$c}s{$c}s|-moz-binding";
        $patterns[] = "/([a-z]*){$c}={$c}([\'\"]*){$c}({$script}){$c}:/iU";
        $replaces[] = '\\1=\\2noscript:';

        // Revoke `@import`
        $patterns[] = "/([a-z]*){$c}([\\\]*){$c}@([\\\]*){$c}i([\\\]*){$c}m"
            . "([\\\]*){$c}p([\\\]*){$c}o([\\\]*){$c}r"
            . "([\\\]*){$c}t/iU";
        $replaces[] = '\\1@noimport';

        // Revoke `<span style="width: expression|behaviour( ... );"></span>`
        // For IE only
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*"
            . "(e{$c}x{$c}p{$c}r{$c}e{$c}s{$c}s{$c}i{$c}o{$c}n"
            . "|b{$c}e{$c}h{$c}a{$c}v{$c}i{$c}o{$c}u{$c}r)"
            . "{$c}\(.*\\2(.*)>/iU";
        $replaces[] = "\\1\\4>";

        // Revoke `<span style="script: "></span>`
        $patterns[] = "/(<[^>]+)style{$c}={$c}([\`\'\"]{1}).*"
            . "s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}: .*\\2(.*)>/iU";
        $replaces[] = "\\1\\3>";

        if ($filter) {
            $content = preg_replace($patterns, $replaces, $content);

            return $content;
        } else {
            return;
        } */
    }
}
