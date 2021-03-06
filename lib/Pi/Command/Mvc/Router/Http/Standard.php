<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Command\Mvc\Router\Http;

use Traversable;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Console\RouteMatcher\DefaultRouteMatcher;
use Laminas\Console\RouteMatcher\RouteMatcherInterface;
use Laminas\Filter\FilterChain;
use Laminas\Mvc\Exception\InvalidArgumentException;
use Laminas\Mvc\Router\Console\RouteInterface;
use Laminas\Mvc\Router\Console\RouteMatch;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Validator\ValidatorChain;

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
    protected $assembledParams = [];

    /**
     * @var RouteMatcherInterface
     */
    protected $matcher;

    /**
     * Path prefix
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Delimiter between structured values of module, controller and action.
     *
     * @var string
     */
    protected $structureDelimiter = '/';

    /**
     * Delimiter between keys and values.
     *
     * @var string
     */
    protected $keyValueDelimiter = '/';

    /**
     * Delimiter before parameters.
     *
     * @var array
     */
    protected $paramDelimiter = '/';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults
        = [
            'module'     => 'system',
            'controller' => 'index',
            'action'     => 'index',
        ];

    /**
     * Create a new simple console route.
     *
     * @param string|RouteMatcherInterface          $routeOrRouteMatcher
     * @param array                                 $constraints
     * @param array                                 $defaults
     * @param array                                 $aliases
     * @param null|array|Traversable|FilterChain    $filters
     * @param null|array|Traversable|ValidatorChain $validators
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        $routeOrRouteMatcher,
        array $constraints = [],
        array $defaults = [],
        array $aliases = [],
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
     * @param array|Traversable $options
     *
     * @return self
     * @throws InvalidArgumentException
     * @see    \Laminas\Mvc\Router\RouteInterface::factory()
     */
    public static function factory($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new InvalidArgumentException('Missing "route" in options array');
        }

        foreach (
            [
                'constraints',
                'defaults',
                'aliases',
            ] as $opt
        ) {
            if (!isset($options[$opt])) {
                $options[$opt] = [];
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
     * @param Request  $request
     * @param null|int $pathOffset
     *
     * @return  RouteMatch
     * @see     Route::match()
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!$request instanceof ConsoleRequest) {
            return null;
        }

        $params = $request->getParams()->toArray();
        //$matches = $this->matcher->match($params);

        $path            = array_shift($params);
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
     * @param array $params
     * @param array $options
     *
     * @return mixed
     * @see    \Laminas\Mvc\Router\RouteInterface::assemble()
     */
    public function assemble(array $params = [], array $options = [])
    {
        $this->assembledParams = [];
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @return array
     * @see    RouteInterface::getAssembledParams
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
