<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Filter
 */

namespace Pi\Utility
{
    use Pi;

    /**
     * Filter handling
     *
     * Syntactic sugar for system APIs
     *
     * - Retrieve a request variable
     *
     * ```
     *  $paramGet = _get('var', 'int');
     *  $paramPost = _post('var', 'email');
     * ```
     *
     * - Filter a value
     *
     * ```
     *  $paramFiltered = _filter('1234.5', 'int');
     *  $paramFiltered = _filter('+1234.5', 'float');
     *  $paramFiltered = _filter('+1234.5', 'float',
     *      FILTER_FLAG_ALLOW_THOUSAND);
     *  $paramFiltered = _filter('+1234.5', 'float', 'allow_thousand');
     *  $paramFiltered = _filter('+1234.5', 'float',
     *      array('flags' => FILTER_FLAG_ALLOW_THOUSAND));
     *  $paramFiltered = _filter('+1234.5', 'float',
     *      array('flags' => 'allow_thousand'));
     * ```
     *
     * - Filter a value with regexp,
     *      only alphabetic and numeric characters are allowed
     *
     * ```
     *  $paramFiltered = _filter($paramRaw, 'regexp',
     *      array('regexp' => '/^[a-z0-9]+$/'));
     *  $paramFiltered = _get($paramName, 'regexp',
     *      array('regexp' => '/^[a-z0-9]+$/'));
     * ```
     *
     * - Sanitize a value:
     *
     * ```
     *  $paramSanitized = _sanitize('1234.5', 'int');
     *  $paramSanitized = _sanitize('+1234.5', 'float',
     *      FILTER_FLAG_ALLOW_FRACTION);
     * ```
     *
     * - Escape a string
     *
     * ```
     *  $stringEscaped = _escape('<p>Text demo</demo>');
     *  $stringEscaped = _escape('<p>Text demo</demo>', 'html');
     *  $stringEscaped = _escape('http://www.pialog.org/demo', 'url');
     * ```
     *
     * - Strip a string
     *
     * ```
     *  $stringStripped = _strip('<p>Text &^#%demo</demo>@!');
     *
     *  // For slug generation
     *  $text = strtolower(trim($stringStripped));
     *  $words = array_filter(explode(' ', $text));
     *  $slug = implode('-', $words);
     *
     *  // For keywords generation
     *  $text = strtolower(trim($stringStripped));
     *  $words = array_unique(array_filter(explode(' ', $text)));
     *  $keywords = implode(',', $words);
     *
     *  // For description generation
     *  $text = strtolower(trim($stringStripped));
     *  $description = preg_replace('/[\s]+/', ' ', $text);
     * ```
     *
     * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
     * @link http://www.php.net/manual/en/filter.filters.validate.php
     * @link http://www.php.net/manual/en/filter.filters.sanitize.php
     * @link http://www.php.net/manual/en/filter.filters.flags.php
     */
    class Filter
    {
        /** @var bool Content-type is in JSON */
        protected static $isJson;

        /** @var array PUT params */
        protected static $putParams;

        /** @var array POST params */
        protected static $postParams;

        /**
         * Constructor
         *
         * Solely to avoid conflict with method of `filter`
         */
        public function __construct()
        {}

        /**
         * Loads filter methods, nothing to do at this moment
         *
         * @return void
         */
        public static function load()
        {}

        /**
         * Filter value with filter_var
         *
         * @param mixed $value
         * @param int|string $filter
         * @param mixed $options
         * @return mixed
         */
        protected static function filterValue(
            $value,
            $filter,
            $options = null
        ) {
            if (empty($filter)) {
                return $value;
            }

            // Canonize filter flag
            $filterFlag = function ($name) {
                $flag = null;
                $filterName = 'FILTER_FLAG_' . strtoupper($name);
                if (defined($filterName)) {
                    $flag = constant($filterName);
                }

                return $flag;
            };

            if ($options) {
                // Filter flag is passed as a direct string
                if (is_string($options)) {
                    $options = $filterFlag($options);
                } elseif (is_array($options)) {
                    // Flags are passed in an array
                    if (isset($options['flags'])) {
                        if (is_string($options['flags'])) {
                            $options['flags'] = $filterFlag($options['flags']);
                        }
                    // Options are passed in an array
                    } elseif (isset($options['options'])) {
                        if (is_string($options['options'])) {
                            $options['options'] = $filterFlag(
                                $options['options']
                            );
                        }
                    // Options are passed directly
                    } else {
                        $options = array(
                            'options'   => $options,
                        );
                    }
                }
            }

            // Canonize filter
            if (is_string($filter)) {
                $filter = filter_id($filter);
            }

            // Perform filtering
            if ($filter) {
                $value = filter_var($value, $filter, $options);
            } else {
                $value = false;
            }

            return $value;
        }

        /**
         * Filter value with filter_var
         *
         * @param mixed $value
         * @param int|string $filter
         * @param mixed $options
         * @return mixed
         * @link http://www.php.net/manual/en/filter.filters.validate.php
         */
        public static function filter($value, $filter = '', $options = null)
        {
            if (is_string($filter)) {
                switch ($filter) {
                    case 'email':
                    case 'ip':
                    case 'url':
                    case 'regexp':
                        $filter = 'validate_' . $filter;
                        break;
                    default:
                        break;
                }
            }
            $value = static::filterValue($value, $filter, $options);

            return $value;
        }

        /**
         * Sanitize value with filter_var
         *
         * @param mixed $value
         * @param int|string $filter
         *      Filter name or id, default as 'full_special_chars'
         * @param mixed $options
         * @return mixed
         * @link http://www.php.net/manual/en/filter.filters.sanitize.php
         */
        public static function sanitize($value, $filter = '', $options = null)
        {
            if (!is_int($filter)) {
                switch ($filter) {
                    case 'float':
                    case 'int':
                        $filter = 'number_' . $filter;
                        break;
                    case '':
                        $filter = 'full_special_chars';
                    default:
                        break;
                }
            }
            $value = static::filterValue($value, $filter, $options);

            return $value;
        }

        /**
         * Get request container
         *
         * @return \Zend\Stdlib\RequestInterface|null
         */
        protected static function getRequest()
        {
            $event = Pi::engine()->application()->getMvcEvent();

            return $event ? $event->getRequest() : null;
        }

        /**
         * Check if request content-type is in JSON format
         *
         * @return bool
         */
        protected static function isRequestJson()
        {
            if (null === static::$isJson) {
                static::$isJson = false;
                $ContentType = static::getRequest()->getHeaders('Content-Type');
                if ($ContentType) {
                    $value = $ContentType->getFieldValue();
                    static::$isJson = false !== stripos($value, 'application/json');
                }
            }

            return static::$isJson;
        }

        /**
         * Get RouteMatch
         *
         * @return \Zend\Mvc\Router\RouteMatch|null
         */
        protected static function getRouteMatch()
        {
            $event = Pi::engine()->application()->getMvcEvent();

            return $event ? $event->getRouteMatch() : null;
        }

        /**
         * Retrieve a variable from query
         *
         * @param string $variable
         * @param int|string $filter
         * @param mixed $options
         * @return mixed
         */
        public static function fromGet($variable, $filter, $options = null)
        {
            $route = static::getRouteMatch();
            $value = $route ? $route->getParam($variable) : null;

            if (null === $value) {
                $request = static::getRequest();
                $value = $request ? $request->getQuery($variable) : null;
            }

            return static::filter($value, $filter, $options);
        }

        /**
         * Retrieve a variable from POST
         *
         * @param string $param
         * @param int|string $filter
         * @param mixed $options
         *
         * @return mixed
         */
        public static function fromPost(
            $param      = null,
            $filter     = null,
            $options    = null
        ) {
            if (null === static::$postParams) {
                $request = static::getRequest();
                if (static::isRequestJson()) {
                    $content = $request->getContent();
                    static::$postParams = json_decode($content, true);
                } else {
                    static::$postParams = $request->getPost()->toArray();
                }
            }
            if ($param === null) {
                $result = static::$postParams;
            } elseif (isset(static::$postParams[$param])) {
                $result = $filter
                    ? static::filter(
                        static::$postParams[$param],
                        $filter,
                        $options
                    )
                    : static::$postParams[$param];
            } else {
                $result = null;
            }

            return $result;
        }

        /**
         * Retrieve a variable from PUT
         *
         * @param string $param
         * @param int|string $filter
         * @param mixed $options
         *
         * @return mixed
         */
        public static function fromPut(
            $param = null,
            $filter = null,
            $options = null
        ) {
            if (null === static::$putParams) {
                $request = static::getRequest();
                $content = $request->getContent();
                if (static::isRequestJson()) {
                    static::$putParams = json_decode($content, true);
                } else {
                    parse_str($content, static::$putParams);
                }
            }
            if ($param === null) {
                $result = static::$putParams;
            } elseif (isset(static::$putParams[$param])) {
                $result = $filter
                    ? static::filter(
                        static::$putParams[$param],
                        $filter,
                        $options
                    )
                    : static::$putParams[$param];
            } else {
                $result = null;
            }

            return $result;
        }
    }
}

namespace
{
    use Pi\Utility\Filter as FilterManager;
    use Zend\Escaper\Escaper;

    /**#@+
     * Retrieve request params with PHP filter_var
     */
    /**
     * Retrieve a variable from query
     *
     * @param string            $variable   Variable name
     * @param int|string        $filter     Filter name or filter_id
     * @param array|int|string  $options    Filter options or flag
     * @return mixed
     */
    function _get($variable = null, $filter = '', $options = null)
    {
        $value = FilterManager::fromGet($variable, $filter, $options);

        return $value;
    }

    /**
     * Retrieve a variable from POST
     *
     * @param string            $variable   Variable name
     * @param int|string        $filter     Filter name or filter_id
     * @param array|int|string  $options    Filter options or flag
     * @return mixed
     */
    function _post($variable = null, $filter = '', $options = null)
    {
        $value = FilterManager::fromPost($variable, $filter, $options);

        return $value;
    }

    /**
     * Filter a value with PHP filter_var
     *
     * @param string            $value      Variable name
     * @param int|string        $filter     Filter name or filter_id
     * @param array|int|string  $options    Filter options or flag
     * @return mixed
     */
    function _filter($value, $filter = '', $options = null)
    {
        $value = FilterManager::filter($value, $filter, $options);

        return $value;
    }

    /**
     * Sanitize a value with PHP filter_var
     *
     * @param                  $value
     * @param int|string       $filter Filter name or filter_id,
     *                                  default as 'full_special_chars'
     * @param array|int|string $options    Filter options or flag
     *
     * @internal param string $variable Variable name
     * @return mixed
     */
    function _sanitize($value, $filter = '', $options = null)
    {
        $value = FilterManager::sanitize($value, $filter, $options);

        return $value;
    }

    /**
     * Escape a string for corresponding context
     *
     * @see \Zend\Escaper\Escaper
     * @param string $value
     * @param string $context
     *      String context, valid value: html, htmlAttr, js, url, css
     * @return string
     */
    function _escape($value, $context = 'html')
    {
        $context = $context ? ucfirst($context) : 'Html';
        $method = 'escape' . $context;
        $escaper = new Escaper(Pi::service('i18n')->getCharset());
        if (method_exists($escaper, $method)) {
            $value = $escaper->{$method}($value);
        }

        return $value;
    }

    /**
     * Clean a string by stripping HTML tags
     * and removing unrecognizable characters
     *
     * @param string        $text           Text to be cleaned
     * @param string|null   $replacement    Replacement for stripped characters
     * @return string
     */
    function _strip($text, $replacement = null)
    {
        $pattern = array(
            "\t", "\r\n", "\r", "\n", "'", "\\",
            '&nbsp;', ',', '.', ';', ':', ')', '(',
            '"', '?', '!', '{', '}', '[', ']', '<', '>', '/', '+', '-', '_',
            '*', '=', '@', '#', '$', '%', '^', '&'
        );
        $replacement = (null === $replacement) ? ' ' : $replacement;

        // Strip HTML tags
        $text = $text ? strip_tags($text) : '';
        // Sanitize
        $text = $text ? _escape($text) : '';

        // Clean up
        $text = $text ? preg_replace('`\[.*\]`U', '', $text) : '';
        $text = $text ? preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '', $text) : '';
        $text = $text
            ? preg_replace(
                '/&([a-z])'
                . '(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);/i',
                '\\1',
                $text)
            : '';
        $text = $text ? str_replace($pattern, $replacement, $text) : '';

        return $text;
    }
}
