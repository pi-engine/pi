<?php
/**
 * Pi Engine Editor CKeditor
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
 * @since           3.0
 * @package         Editor\Ckeditor
 * @version         $Id$
 */

namespace Editor\Ckeditor;

use CKEditor as CkBase;

class Ckeditor extends CkBase
{
    protected static $editorInitialized = false;

    /**
     * An array that holds event listeners.
     */
    protected $events = array();
    /**
     * An array that holds global event listeners.
     */
    protected $globalEvents = array();

    /**
     * Creates a %CKEditor instance.
     * In incompatible browsers %CKEditor will downgrade to plain HTML &lt;textarea&gt; element.
     *
     * @param $name (string) Name of the %CKEditor instance (this will be also the "name" attribute of textarea element).
     * @param $value (string) Initial value (optional).
     * @param $config (array) The specific configurations to apply to this editor instance (optional).
     * @param $events (array) Event listeners for this editor instance (optional).
     *
     * Example usage:
     * @code
     * $CKEditor = new CKEditor();
     * $CKEditor->editor("field1", "<p>Initial value.</p>");
     * @endcode
     *
     * Advanced example:
     * @code
     * $CKEditor = new CKEditor();
     * $config = array();
     * $config['toolbar'] = array(
     *     array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
     *     array( 'Image', 'Link', 'Unlink', 'Anchor' )
     * );
     * $events['instanceReady'] = 'function (ev) {
     *     alert("Loaded: " + ev.editor.name);
     * }';
     * $CKEditor->editor("field1", "<p>Initial value.</p>", $config, $events);
     * @endcode
     */
    public function editor($name, $value = "", $config = array(), $events = array())
    {
        $attr = "";
        foreach ($this->textareaAttributes as $key => $val) {
            $attr.= " " . $key . '="' . str_replace('"', '&quot;', $val) . '"';
        }
        $out = "<textarea name=\"" . $name . "\"" . $attr . ">" . htmlspecialchars($value) . "</textarea>\n";
        if (!$this->initialized) {
            $out .= $this->init();
        }

        $_config = $this->configSettings($config, $events);

        /**#@+
         * Use ID to allow for multiple editors on one page
         */
        $name = empty($this->textareaAttributes['id']) ? $name : $this->textareaAttributes['id'];
        /**#@-*/

        $js = $this->returnGlobalEvents();
        if (!empty($_config))
            $js .= "CKEDITOR.replace('".$name."', ".$this->jsEncode($_config).");";
        else
            $js .= "CKEDITOR.replace('".$name."');";

        $out .= $this->script($js);

        if (!$this->returnOutput) {
            print $out;
            $out = "";
        }

        return $out;
    }

    /**#@+
     * Solely for private methods access
     */
    /**
     * Prints javascript code.
     *
     * @param string $js
     */
    protected function script($js)
    {
        $out = "<script type=\"text/javascript\">";
        $out .= "//<![CDATA[\n";
        $out .= $js;
        $out .= "\n//]]>";
        $out .= "</script>\n";

        return $out;
    }

    /**
     * Returns the configuration array (global and instance specific settings are merged into one array).
     *
     * @param $config (array) The specific configurations to apply to editor instance.
     * @param $events (array) Event listeners for editor instance.
     */
    protected function configSettings($config = array(), $events = array())
    {
        $_config = $this->config;
        $_events = $this->events;

        if (is_array($config) && !empty($config)) {
            $_config = array_merge($_config, $config);
        }

        if (is_array($events) && !empty($events)) {
            foreach ($events as $eventName => $code) {
                if (!isset($_events[$eventName])) {
                    $_events[$eventName] = array();
                }
                if (!in_array($code, $_events[$eventName])) {
                    $_events[$eventName][] = $code;
                }
            }
        }

        if (!empty($_events)) {
            foreach($_events as $eventName => $handlers) {
                if (empty($handlers)) {
                    continue;
                }
                else if (count($handlers) == 1) {
                    $_config['on'][$eventName] = '@@'.$handlers[0];
                }
                else {
                    $_config['on'][$eventName] = '@@function (ev){';
                    foreach ($handlers as $handler => $code) {
                        $_config['on'][$eventName] .= '('.$code.')(ev);';
                    }
                    $_config['on'][$eventName] .= '}';
                }
            }
        }

        return $_config;
    }

    /**
     * Return global event handlers.
     */
    protected function returnGlobalEvents()
    {
        static $returnedEvents;
        $out = "";

        if (!isset($returnedEvents)) {
            $returnedEvents = array();
        }

        if (!empty($this->globalEvents)) {
            foreach ($this->globalEvents as $eventName => $handlers) {
                foreach ($handlers as $handler => $code) {
                    if (!isset($returnedEvents[$eventName])) {
                        $returnedEvents[$eventName] = array();
                    }
                    // Return only new events
                    if (!in_array($code, $returnedEvents[$eventName])) {
                        $out .= ($code ? "\n" : "") . "CKEDITOR.on('". $eventName ."', $code);";
                        $returnedEvents[$eventName][] = $code;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Initializes CKEditor (executed only once).
     */
    protected function init()
    {
        //static $initComplete;
        $out = "";

        if (static::$editorInitialized) {
            return $out;
        }

        /*
        if (!empty($initComplete)) {
            return "";
        }
        */

        if ($this->initialized) {
            static::$editorInitialized = true;
            return "";
        }

        $args = "";
        $ckeditorPath = $this->ckeditorPath();

        if (!empty($this->timestamp) && $this->timestamp != "%"."TIMESTAMP%") {
            $args = '?t=' . $this->timestamp;
        }

        // Skip relative paths...
        if (strpos($ckeditorPath, '..') !== 0) {
            $out .= $this->script("window.CKEDITOR_BASEPATH='". $ckeditorPath ."';");
        }

        $out .= "<script type=\"text/javascript\" src=\"" . $ckeditorPath . 'ckeditor.js' . $args . "\"></script>\n";

        $extraCode = "";
        if ($this->timestamp != static::timestamp) {
            $extraCode .= ($extraCode ? "\n" : "") . "CKEDITOR.timestamp = '". $this->timestamp ."';";
        }
        if ($extraCode) {
            $out .= $this->script($extraCode);
        }

        //$initComplete = true;
        $this->initialized = true;
        static::$editorInitialized = true;

        return $out;
    }

    /**
     * Return path to ckeditor.js.
     */
    protected function ckeditorPath()
    {
        if (!empty($this->basePath)) {
            return $this->basePath;
        }

        /**
         * The absolute pathname of the currently executing script.
         * Note: If a script is executed with the CLI, as a relative path, such as file.php or ../file.php,
         * $_SERVER['SCRIPT_FILENAME'] will contain the relative path specified by the user.
         */
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $realPath = dirname($_SERVER['SCRIPT_FILENAME']);
        }
        else {
            /**
             * realpath - Returns canonicalized absolute pathname
             */
            $realPath = realpath( './' ) ;
        }

        /**
         * The filename of the currently executing script, relative to the document root.
         * For instance, $_SERVER['PHP_SELF'] in a script at the address http://example.com/test.php/foo.bar
         * would be /test.php/foo.bar.
         */
        $selfPath = dirname($_SERVER['PHP_SELF']);
        $file = str_replace("\\", "/", __FILE__);

        if (!$selfPath || !$realPath || !$file) {
            return "/ckeditor/";
        }

        $documentRoot = substr($realPath, 0, strlen($realPath) - strlen($selfPath));
        $fileUrl = substr($file, strlen($documentRoot));
        $ckeditorUrl = str_replace("ckeditor_php5.php", "", $fileUrl);

        return $ckeditorUrl;
    }

    /**
     * This little function provides a basic JSON support.
     * http://php.net/manual/en/function.json-encode.php
     *
     * @param mixed $val
     * @return string
     */
    protected function jsEncode($val)
    {
        if (is_null($val)) {
            return 'null';
        }
        if ($val === false) {
            return 'false';
        }
        if ($val === true) {
            return 'true';
        }
        if (is_scalar($val))
        {
            if (is_float($val))
            {
                // Always use "." for floats.
                $val = str_replace(",", ".", strval($val));
            }

            // Use @@ to not use quotes when outputting string value
            if (strpos($val, '@@') === 0) {
                return substr($val, 2);
            }
            else {
                // All scalars are converted to strings to avoid indeterminism.
                // PHP's "1" and 1 are equal for all PHP operators, but
                // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
                // we should get the same result in the JS frontend (string).
                // Character replacements for JSON.
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));

                $val = str_replace($jsonReplaces[0], $jsonReplaces[1], $val);
                if (strtoupper(substr($val, 0, 9)) == 'CKEDITOR.') {
                    return $val;
                }

                return '"' . $val . '"';
            }
        }
        $isList = true;
        for ($i = 0, reset($val); $i < count($val); $i++, next($val))
        {
            if (key($val) !== $i)
            {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList)
        {
            foreach ($val as $v) $result[] = $this->jsEncode($v);
            return '[ ' . join(', ', $result) . ' ]';
        }
        else
        {
            foreach ($val as $k => $v) $result[] = $this->jsEncode($k).': '.$this->jsEncode($v);
            return '{ ' . join(', ', $result) . ' }';
        }
    }
    /**#@-*/
}
