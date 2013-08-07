<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Debug
{
    use Pi;

    /**
     * Pi Debugger
     *
     * Syntactic sugar for debug APIs
     *
     * Display a var
     *
     *  ```
     *      d($var);
     *  ```
     *
     * Display call backtrace
     *
     *  ```
     *      b();
     *  ```
     *
     * Contitional display of a var
     *
     *  ```
     *      $var = 'something ...';
     *
     *      dc($var);   // No output
     *
     *      denable();
     *
     *      dc($var);   // Output: something ...
     *
     *      denable(false);
     *
     *      dc($var);   // No putput
     *  ```
     *
     * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
     */
    class Debug
    {
        /** @var bool|null In the process of conditional debug */
        protected static $inProcess = null;

        /**
         * Loads debugger, nothing to do at this moment
         *
         * @return void
         */
        public static function load()
        {}

        /**
         * Enable/Disable conditional debugging
         *
         * @param bool $flag
         * @return void
         */
        public static function enable($flag = true)
        {
            static::$inProcess = $flag;
            $message = static::render(sprintf(
                'Conditional debug %s',
                $flag ? 'enabled' : 'disabled'),
                2
            );
            Pi::service('log')->debug($message);
        }

        /**
         * Renders a variable or an object during conditional period
         *
         * @param mixed $data a variable or an object
         * @param int   $skip steps to skip
         * @return string|null
         */
        public static function conditional($data, $skip = 0)
        {
            if (true !== static::$inProcess) {
                return null;
            }

            return static::render($data, $skip);
        }

        /**
         * Syntatic sugar for displaying debugger information
         *
         * @param mixed $data
         * @return void
         */
        public static function e($data)
        {
            //echo static::render($data);
            $message = static::render($data);
            Pi::service('log')->debug($message);
        }

        /**
         * Syntatic sugar for render()
         *
         * @param mixed $data
         * @return string
         */
        public static function _($data)
        {
            return static::render($data);
        }

        /**
         * Displays debugger information
         *
         * @param mixed $data
         * @return void
         */
        public static function display($data)
        {
            $message = static::render($data);
            Pi::service('log')->debug($message);
        }

        /**
         * Renders a variable or an object
         *
         * @param mixed $data a variable or an object
         * @param int   $skip steps to skip
         * @return string
         */
        public static function render($data, $skip = 0)
        {
            $time = microtime(true);
            $location = date('H:i:s', $time)
                      . substr($time, strpos($time, '.'), 5) . ' ';
            $list = debug_backtrace();
            foreach ($list as $item) {
                if ($skip-- > 0) continue;
                $file = Pi::service('security')->path($item['file']);
                $location .= $file . ':' . $item['line'];
                break;
            }

            if (PHP_SAPI === 'cli') {
                $result = PHP_EOL;
                if (is_array($data) || is_object($data)) {
                    $result .= $location;
                    $result .= PHP_EOL;
                    $result .= print_r($data, true);
                    $result .= PHP_EOL;
                } else {
                    $result .= $data;
                    $result .= ' [' . $location . ']';
                }
                $result .= PHP_EOL;
            } else {
                $result = '<div style="padding: .8em;'
                        . ' margin-bottom: 1em; border: 2px solid #ddd;">';
                if (is_array($data) || is_object($data)) {
                    $result .= $location;
                    $result .= '<div><pre>';
                    $result .= print_r($data, true);
                    $result .= '</pre></div>';
                } else {
                    $result .= sprintf(
                        '<div>%s<pre>%s</pre></div>',
                        $location,
                        $data
                    );
                }
                $result .= '</div>';
            }

            return $result;
        }

        /**
         * Displays formatted backtrace information
         *
         * @param bool  $display To display or return as a string
         * @param int   $skip steps to skip
         * @return void|string
         */
        public static function backtrace($display = true, $skip = 0)
        {
            $list = debug_backtrace();
            $list = array_slice($list, $skip);
            $list = array_reverse($list);

            if (PHP_SAPI === 'cli') {
                $bt = PHP_EOL;
                $bt .= 'Backtrace at: ' . microtime(true) . PHP_EOL . PHP_EOL;
                foreach ($list as $backtrace) {
                    $location = empty($backtrace['file'])
                        ? 'Internal'
                        : Pi::service('security')->path($backtrace['file'])
                            . '(' . $backtrace['line'] . ')';
                    $bt .= $location . ': '
                         . (empty($backtrace['class'])
                            ? '' : $backtrace['class'] . '::')
                         . $backtrace['function'] . '()' . PHP_EOL;
                }
                $bt .= PHP_EOL;
            } else {
                $bt = '<pre>';
                $bt .= '<strong>Backtrace at: ' . microtime(true)
                    . '</strong><ul>';
                foreach ($list as $backtrace) {
                    $location = empty($backtrace['file'])
                        ? 'Internal'
                        : Pi::service('security')->path($backtrace['file'])
                            . '(' . $backtrace['line'] . ')';
                    $bt .= '<li>' . $location . ': '
                         . (empty($backtrace['class'])
                            ? '' : $backtrace['class'] . '::')
                         . $backtrace['function'] . '()</li>';
                }
                $bt .= '</ul>';
                $bt .= '</pre>';
            }

            if ($display) {
                if (Pi::service()->hasService('log')) {
                    Pi::service('log')->debug($bt);
                } else {
                    echo $bt;
                }
            } else {
                return $bt;
            }
        }

        /**
         * Debug helper function
         *
         * This is a wrapper for var_dump() that adds the <pre /> tags,
         * cleans up newlines and indents, and runs
         * htmlspecialchars() before output.
         *
         * @see Zend\Debug::dump()
         * @param mixed     $var        The variable to dump.
         * @param bool      $display    OPTIONAL echo output if true.
         * @param int       $skip       steps to skip
         * @return string|void
         */
        public static function dump($var, $display = true, $skip = 1)
        {
            $time = microtime(true);
            $location = date('H:i:s', $time)
                      . substr($time, strpos($time, '.'), 5) . ' ';
            $list = debug_backtrace();
            foreach ($list as $item) {
                if ($skip-- > 0) continue;
                $file = Pi::service('security')->path($item['file']);
                $location .= $file . ':' . $item['line'];
                break;
            }

            // var_dump the variable into a buffer and keep the output
            ob_start();
            var_dump($var);
            $output = ob_get_clean();

            // neaten the newlines and indents
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            if (PHP_SAPI === 'cli') {
                $result = PHP_EOL . $location . PHP_EOL . $output . PHP_EOL;
            } else {
                if (!extension_loaded('xdebug')) {
                    $output = htmlspecialchars($output, ENT_QUOTES);
                }
                $result = '<div style="padding: .8em;'
                        . ' margin-bottom: 1em; border: 2px solid #ddd;">';
                $result .= $location;
                $result .= '<div><pre>';
                $result .= $output;
                $result .= '</pre></div>';
                $result .= '</div>';
            }

            if ($display) {
                Pi::service('log')->debug($result);
            } else {
                return $result;
            }
        }
    }
}

/**
 * Syntactic sugar for debug APIs
 *
 * Display a var
 *
 *  ```
 *      d($var);
 *  ```
 *
 * Display call backtrace
 *
 *  ```
 *      b();
 *  ```
 *
 * Contitional display of a var
 *
 *  ```
 *      $var = 'something ...';
 *      // ...
 *      dc($var);   // No output
 *      // ...
 *      denable();
 *      // ...
 *      dc($var);   // Output: something ...
 *      // ...
 *      denable(false);
 *      // ..
 *      dc($var);   // No putput
 *  ```
 */
namespace
{
    use Pi\Debug\Debug;

    /**
     * Displays a debug message
     *
     * @param mixed $data a variable or an object
     * @return void
     */
    function d($data = '')
    {
        if (Pi::service()->hasService('log')) {
            $output = Debug::render($data, 1);
            Pi::service('log')->debug($output);
        } else {
            echo Debug::render($data, 1);
        }
    }

    /**
     * Displays backtrace messages
     *
     * @return void
     */
    function b()
    {
        Debug::backtrace(true, 1);
    }

    /**
     * Displays a debug message during conditional debug
     *
     * @param mixed $data a variable or an object
     * @return void
     */
    function dc($data = '')
    {
        $output = Debug::conditional($data, 2);
        if (null !== $output) {
            Pi::service('log')->debug($output);
        }
    }

    /**
     * Enable for conditional debug
     *
     * @param bool $flag
     * @return void
     */
    function denable($flag = true)
    {
        Debug::enable($flag);
    }

    /**
     * Enable conditional debug
     *
     * @return void
     */
    function de()
    {
        Debug::enable(true);
    }

    /**
     * Disable conditional debug
     *
     * @return void
     */
    function df()
    {
        Debug::enable(false);
    }

    /**
     * Dump data with var_dump()
     *
     * @param mixed $data
     * @return string
     */
    function vd($data)
    {
        return Debug::dump($data, true, 1);
    }
}
