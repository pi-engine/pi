<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Command\Mvc\Router\Http;

use Traversable;
use Zend\Console\RouteMatcher\DefaultRouteMatcher;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\RouteMatcher\RouteMatcherInterface;
use Zend\Filter\FilterChain;
use Zend\Mvc\Exception\InvalidArgumentException;
use Zend\Mvc\Router\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Validator\ValidatorChain;
use Zend\Mvc\Router\Console\RouteInterface;
use Zend\Mvc\Router\Console\RouteMatch;

/**
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Standard implements RouteInterface
{
    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * @var RouteMatcherInterface
     */
    protected $matcher;
    
    /**
     * Path prefix
     * @var string
     */
    protected $prefix = '';

    /**
     * Delimiter between structured values of module, controller and action.
     * @var string
     */
    protected $structureDelimiter = '/';

    /**
     * Delimiter between keys and values.
     * @var string
     */
    protected $keyValueDelimiter = '/';

    /**
     * Delimiter before parameters.
     * @var array
     */
    protected $paramDelimiter = '/';

    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'system',
        'controller'    => 'index',
        'action'        => 'index'
    );

    /**
     * Create a new simple console route.
     *
     * @param  string|RouteMatcherInterface             $routeOrRouteMatcher
     * @param  array                                    $constraints
     * @param  array                                    $defaults
     * @param  array                                    $aliases
     * @param  null|array|Traversable|FilterChain       $filters
     * @param  null|array|Traversable|ValidatorChain    $validators
     * @throws InvalidArgumentException
     */
    public function __construct(
        $routeOrRouteMatcher,
        array $constraints = array(),
        array $defaults = array(),
        array $aliases = array(),
        $filters = null,
        $validators = null
    ) {
        if (is_string($routeOrRouteMatcher)) {
            $this->matcher = new DefaultRouteMatcher($routeOrRouteMatcher, $constraints, $defaults, $aliases);
        } elseif ($routeOrRouteMatcher instanceof RouteMatcherInterface) {
            $this->matcher = $routeOrRouteMatcher;
        } else {
            throw new InvalidArgumentException(
                "routeOrRouteMatcher should either be string, or class implementing RouteMatcherInterface. "
                . gettype($routeOrRouteMatcher) . " was given."
            );
        }
    }

    /**
     * factory(): defined by Route interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @throws InvalidArgumentException
     * @return self
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new InvalidArgumentException('Missing "route" in options array');
        }

        foreach (array(
            'constraints',
            'defaults',
            'aliases',
        ) as $opt) {
            if (!isset($options[$opt])) {
                $options[$opt] = array();
            }
        }

        if (!isset($options['validators'])) {
            $options['validators'] = null;
        }

        if (!isset($options['filters'])) {
            $options['filters'] = null;
        }


        return new static(
            $options['route'],
            $options['constraints'],
            $options['defaults'],
            $options['aliases'],
            $options['filters'],
            $options['validators']
        );
    }

    /**
     * match(): defined by Route interface.
     *
     * @see     Route::match()
     * @param   Request             $request
     * @param   null|int            $pathOffset
     * @return  RouteMatch
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!$request instanceof ConsoleRequest) {
            return null;
        }

        $params  = $request->getParams()->toArray();
        //$matches = $this->matcher->match($params);
        
        $path = array_shift($params);
        $structureParams = explode($this->structureDelimiter, $path);
        if (count($structureParams) < 3) {
            return null;
        }
        $matches = [
            'module'     => array_shift($structureParams),
            'controller' => array_shift($structureParams),
            'action'     => array_shift($structureParams),
        ];
        if (!empty($structureParams)) {
            return null;
        }
        
        $this->defaults  = array_merge($this->defaults, $matches);
        $matches['args'] = $params;
        
        if (null !== $matches) {
            return new RouteMatch($matches);
        }
        return null;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $this->assembledParams = array();
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
