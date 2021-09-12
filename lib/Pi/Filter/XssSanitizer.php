<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Laminas\Filter\AbstractFilter;
use voku\helper\AntiXSS;

/**
 * Cross site scripting check
 *
 * @link   : http://ha.ckers.org/xss.html (old)
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class XssSanitizer extends AbstractFilter
{
    /**
     * Filter text against XSS
     *
     * Inspired by:
     *
     *  - Daniel Morris: http://www.phpclasses.org/browse/file/9402.html
     *  - kallahar@quickwired.com's RemoveXSS
     *  - htmlLawed
     *  - HTMLpurifier
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $antiXss = new AntiXSS();
        $antiXss->xss_clean($value);
        if ($antiXss->isXssFound()) {
            return false;
        }
        return true;
    }

    // This is old filter
    /* public function filter($value)
    {
        // Remove NULL bytes
        $content = str_replace("\0", '', $value);

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

        $value = preg_replace($patterns, $replaces, $content);

        return $value;
    } */
}
