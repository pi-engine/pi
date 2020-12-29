<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Route;

use Pi\Mvc\Router\Http\Standard;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\Stdlib\RequestInterface as Request;

/**
 * Sample for lean URL
 *
 * Highly customized, solely for demonstration
 *
 * ID: url/$id
 * Slug: url/$slug
 * Slug & ID: url/$id-$slug
 */
class Slug extends Standard
{
    protected $prefix = '/demo-route';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults
        = [
            'module'     => 'demo',
            'controller' => 'route',
            'action'     => 'slug',
        ];

    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;
        if (empty($path)) {
            return null;
        }

        list($id, $slug) = [null, null];
        if (false === ($pos = strpos($path, '-'))) {
            if (is_numeric($path)) {
                $id = $path;
            } else {
                $slug = $path;
            }
        } else {
            list($id, $slug) = explode('-', $path, 2);
            if (!is_numeric($id)) {
                $id   = null;
                $slug = $path;
            }
        }

        $matches = [
            'action' => (null === $slug) ? 'id' : 'slug',
            'id'     => $id,
            'slug'   => $this->decode($slug),
        ];

        return new RouteMatch(
            array_merge($this->defaults, $matches),
            $pathLength
        );
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = [])
    {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }
        $url = isset($mergedParams['id']) ? intval($mergedParams['id']) : '';
        if (isset($mergedParams['slug'])) {
            $url .= ($url ? '-' : '') . $this->encode($mergedParams['slug']);
        }

        return $this->paramDelimiter
            . trim($this->prefix, $this->paramDelimiter)
            . $this->paramDelimiter . $url;
    }
}
