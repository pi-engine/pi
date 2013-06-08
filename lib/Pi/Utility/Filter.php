<?php
/**
 * Kernel filter
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
 * @package         Pi
 */

namespace Pi\Utility
{
    use Pi;

    class Filter
    {
        /**
         * Loads filter methods, nothing to do at this moment
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
        protected static function filterValue($value, $filter, $options = null)
        {
            if (empty($filter)) {
                return $value;
            }

            // Canonize filter flag
            $filterFlag = function ($name)
            {
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
                            $options['options'] = $filterFlag($options['options']);
                        }
                    // Options are passed directly
                    } else {
                        $options = array(
                            'options'   => $options,
                        );
                    }
                }
            }

            // Canonize fitler
            if (is_string($filter)) {
                $filter = filter_id($filter);
            }

            // Performe filtering
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
         */
        public static function filter($value, $filter = '', $options = null)
        {
            // See @link http://www.php.net/manual/en/filter.filters.validate.php
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
         * @param int|string $filter    Filter name or id, default as 'full_special_chars'
         * @param mixed $options
         * @return mixed
         */
        public static function sanitize($value, $filter = '', $options = null)
        {
            // See @link http://www.php.net/manual/en/filter.filters.sanitize.php
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
         * @return \Zend\Stdlib\RequestInterface
         */
        protected static function getRequest()
        {
            $event = Pi::engine()->application()->getMvcEvent();
            return $event ? $event->getRequest() : null;
        }

        /**
         * Get routeMatch
         *
         * @return
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
         * @param string $variable
         * @param int|string $filter
         * @param mixed $options
         * @return mixed
         */
        public static function fromPost($variable, $filter, $options = null)
        {
            $request = static::getRequest();
            $value = $request ? $request->getPost($variable) : null;

            return static::filter($value, $filter, $options);
        }
    }
}

/**#@+
 * Syntactic sugar for system API
 *
 * Retrieve a request variable:
 * <code>
 *  $paramGet = _get('var', 'int');
 *  $paramPost = _post('var', 'email');
 * </code>
 *
 * Filter a value:
 * <code>
 *  $paramFiltered = _filter('1234.5', 'int');
 *  $paramFiltered = _filter('+1234.5', 'float');
 *  $paramFiltered = _filter('+1234.5', 'float', FILTER_FLAG_ALLOW_THOUSAND);
 *  $paramFiltered = _filter('+1234.5', 'float', 'allow_thousand');
 *  $paramFiltered = _filter('+1234.5', 'float', array('flags' => FILTER_FLAG_ALLOW_THOUSAND));
 *  $paramFiltered = _filter('+1234.5', 'float', array('flags' => 'allow_thousand'));
 * </code>
 *
 * Sanitize a value:
 * <code>
 *  $paramSanitized = _sanitize('1234.5', 'int');
 *  $paramSanitized = _sanitize('+1234.5', 'float', FILTER_FLAG_ALLOW_FRACTION);
 * </code>
 */
namespace
{
    use Pi\Utility\Filter as FilterManager;

    /**#@+
     * Retrieve request params with PHP filter_var
     */
    /**
     * Retrieve a variable from query
     *
     * @param string $variable
     * @param int|string $filter
     * @param mixed $options
     * @return mixed
     */
    function _get($variable, $filter = '', $options = null)
    {
        $value = FilterManager::fromGet($variable, $filter, $options);
        return $value;
    }

    /**
     * Retrieve a variable from POST
     *
     * @param string $variable
     * @param int|string $filter
     * @param mixed $options
     * @return mixed
     */
    function _post($variable, $filter = '', $options = null)
    {
        $value = FilterManager::fromPost($variable, $filter, $options);
        return $value;
    }

    /**
     * Filter a value with PHP filter_var
     *
     * @param string $variable
     * @param int|string $filter
     * @param mixed $options
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
     * @param string $variable
     * @param int|string $filter    Filter name or id, default as 'full_special_chars'
     * @param mixed $options
     * @return mixed
     */
    function _sanitize($value, $filter = '', $options = null)
    {
        $value = FilterManager::sanitize($value, $filter, $options);
        return $value;
    }
}
/**#@-*/
