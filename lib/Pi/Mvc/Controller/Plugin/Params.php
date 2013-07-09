<?php
/**
 * Controller plugin URL class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\Params as ZendParams;

/**#@+
 * Syntactic sugar for params
 *
 * Retrieve a request variable:
 * <code>
 *  $paramGet = $this->params()->get('var', 'int');
 *  $paramPost = $this->params()->post('var', 'email');
 *  $paramPut = $this->params()->put('var', 'url');
 *  $paramAuto = $this->params()->request('var', 'ip');
 * </code>
 *
 * Filter a value:
 * <code>
 *  $paramFiltered = $this->params()->filter('+1234.5', 'float');
 * </code>
 */
class Params extends ZendParams
{
    protected $putParams;
    protected $variablesOrder = array(
        'query', 'request'
    );

    /**
     * Grabs a param from route match by default.
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function __invoke($param = null, $default = null)
    {
        $result = parent::__invoke($param, null);
        if ($result !== null) {
            return $result;
        }

        $value = $default;
        foreach ($this->variablesOrder as $method) {
            if (method_exists($this, 'from' . $method)) {
                $val = $this->{'from' . $method}($param);
                if (null !== $val) {
                    $value = $val;
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * Return all put parameters or a single put parameter.
     *
     * @param string $param Parameter name to retrieve, or null to get all.
     * @param mixed $default Default value to use when the parameter is missing.
     * @return mixed
     */
    public function fromPut($param = null, $default = null)
    {
        if (null === $this->putParams) {
            $request = $this->getController()->getRequest();
            $content = $request->getContent();
            if ($request->getHeaders('accept')->match('application/json')) {
                $this->putParams = json_decode($content, true);
            } else {
                parse_str($content, $this->putParams);
            }
        }
        if ($param === null) {
            return $this->putParams;
        } else {
            return isset($this->putParams[$param]) ? $this->putParams[$param] : $default;
        }
    }

    /**
     * Return all parameters or a single parameter according to request method
     *
     * @param string $param Parameter name to retrieve, or null to get all.
     * @param mixed $default Default value to use when the parameter is missing.
     * @return mixed
     */
    public function fromRequest($param = null, $default = null)
    {
        $method = $this->getController()->getRequest()->getMethod();
        if (method_exists($this, 'from' . $method)) {
            return $this->{'from' . $method}($param, $default);
        }

        return null;
    }

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
    public function get($variable, $filter, $options = null)
    {
        $value = $this->fromRoute($variable);
        if (null === $value) {
            $value = $this->fromQuery($variable);
        }
        if (null !== $value) {
            $value = $this->filter($value, $filter, $options);
        }

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
    public function post($variable, $filter, $options = null)
    {
        $value = $this->fromPost($variable);
        if (null !== $value) {
            $value = $this->filter($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Retrieve a variable from PUT
     *
     * @param string $variable
     * @param int|string $filter
     * @param mixed $options
     * @return mixed
     */
    public function put($variable, $filter, $options = null)
    {
        $value = $this->fromPut($variable);
        if (null !== $value) {
            $value = $this->filter($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Retrieve a variable from current HTTP method: get, post, put
     *
     * @param string $variable
     * @param int|string $filter
     * @param mixed $options
     * @return mixed
     */
    public function request($variable, $filter, $options = null)
    {
        $value = $this->fromRequest($variable);
        if (null !== $value) {
            $value = $this->filter($value, $filter, $options);
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
    public function filter($value, $filter, $options = null)
    {
        $value = _filter($value, $filter, $options);
        return $value;
    }
    /**#@-*/
}
