<?php
/**
 * Security service
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
 * @package         Pi\Application
 * @subpackage      Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Security\Security as SecurityUtility;
use Zend\Escaper\Escaper;

class Security extends AbstractService
{
    protected $fileIdentifier = 'security';
    protected $paths;

    /**
     * Header outputs on deny
     *
     * @param string $message The message to be displayed
     * @return void
     */
    public function deny($message = '')
    {
        if (!headers_sent()) {
            if (substr(PHP_SAPI, 0, 3) == 'cgi') {
                header('Status: 403 Forbidden');
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
        }
        exit('Access denied' . ($message ? ': ' . $message : '.'));
    }

    /**
     * Remove path prefix for security considerations
     *
     * @param string $str
     * @return string
     */
    public function path($str)
    {
        if (!isset($this->paths)) {
            // Loads all path settings from host data
            $paths = Pi::host()->get('path');
            $lengths = array();
            foreach ($paths as $root => $v) {
                $lengths[] = strlen($v);
            }
            // Sort the paths by their lengths in reverse
            array_multisort($lengths, SORT_NUMERIC, SORT_DESC, $paths);
            $this->paths = $paths;
        }
        if (DIRECTORY_SEPARATOR != '/') {
            $str = str_replace(DIRECTORY_SEPARATOR, '/', $str);
        }
        foreach ($this->paths as $root => $v) {
            if (empty($v) || empty($root)) {
                continue;
            }
            // Replace full path with relative path to prevent path disclosure
            $str  = str_replace(array($v . '/', realpath($v) . '/'), $root . '/', $str);
        }

        return $str;
    }

    /**
     * Remove DB database name and table prefix for security considerations
     *
     * @param string $str
     * @return string
     */
    public function db($str)
    {
        $pattern = '/\b' . preg_quote(Pi::db()->getTablePrefix()) . '/i';
        $return = preg_replace($pattern, '', $str);
        return $return;
    }

    /**
     * Get escaper, and escape HTML content if specified
     *
     * @param string|null $content
     * @return Escaper|string
     */
    public function escape($content = null)
    {
        $escaper = new Escaper(Pi::config('charset'));
        if (null === $content) {
            return $escaper;
        }
        return $escaper->escapeHtml($content);
    }

    /**#@++
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */

    /**
     * Check for IPs
     */
    public function ip($options = null)
    {
        if (!is_array($options) && isset($this->options['ip'])) {
            $options = $this->options['ip'];
        }
        return SecurityUtility::ip($options);
    }

    /**
     * Check for super globals
     */
    public function globals($options = null)
    {
        if (!is_array($options) && isset($this->options['globals'])) {
            $options = $this->options['globals'];
        }
        return SecurityUtility::globals($options);
    }

    /**
     * Magic method to access custom security settings
     *
     * @param string $method The security setting to be checked
     * @param array  $args  arguments for the setting
     */
    public function __call($method, $args = array())
    {
        $options = $args ? $args[0] : null;
        if (!is_array($options) && isset($this->options[$method])) {
            $options = $this->options[$method];
        }
        return SecurityUtility::$method($options);
    }
    /*#@-*/

}
