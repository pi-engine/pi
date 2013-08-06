<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Sample for lean URL
 *
 * Highly customized, solely for demonstration
 *
 * Compound with category, time and slug: url/$category/2012/08/23/$slug
 * Compound with category, time, ID and slug:
 *      url/$category/2012/08/23/$id-$slug
 */
class Compound extends Standard
{
    protected $prefix = '/demo-route';
    protected $dateDelimiter = '/';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults = array(
        'module'        => 'demo',
        'controller'    => 'route',
        'action'        => 'compound'
    );


    /**
     * match(): defined by Route interface.
     *
     * @see    Route::match()
     * @param  Request $request
     * @return RouteMatch
     */
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
        if (false === strpos($path, $this->dateDelimiter)) {
            return null;
        }

        list($category, $sub) = explode($this->paramDelimiter, $path);
        if (empty($sub)) {
            return null;
        }

        $pos = strrpos($sub, $this->dateDelimiter);
        if (false === $pos) {
            return null;
        }
        $dateString = substr($sub, 0, $pos);
        $slugString = substr($sub, $pos + 1);
        if (empty($slugString)) {
            return null;
        }

        // 2012/08/24
        if ($this->dateDelimiter) {
            list($y, $m, $d) = explode($this->dateDelimiter, $dateString);
        // 20120824
        } else {
            $y = substr($dateString, 0, 4);
            $m = substr($dateString, 4, 2);
            $d = substr($dateString, -2);
        }
        if (!is_numeric($y) || $y > 2050 || $y < 1970
            || !is_numeric($m) || $m < 0 || $m > 12
            || !is_numeric($d) || $d < 0 || $d > 31) {
            return null;
        }
        $time = array($y, $m, $d);

        $path = $slugString;
        list($id, $slug) = array(null, null);
        if (false === ($pos = strpos($path, '-'))) {
            if (is_numeric($path)) {
                $id = $path;
            } else {
                $slug = $path;
            }
        } else {
            list($id, $slug) = explode('-', $path, 2);
            if (!is_numeric($id)) {
                $id = null;
                $slug = $path;
            }
        }

        $matches = array(
            'action'        => 'compound',
            'category'      => urldecode($category),
            'time'          => $time,
            'id'            => $id,
            'slug'          => urldecode($slug),
        );

        return new RouteMatch(array_merge($this->defaults, $matches),
                              $pathLength);
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }
        $url = '';
        if (isset($mergedParams['id'])) {
            $url .= intval($mergedParams['id']);
        }
        if (isset($mergedParams['slug'])) {
            $url .= ($url ? '-' : '') . urlencode($mergedParams['slug']);
        }
        $timeString = date(
            'Y' . $this->dateDelimiter . 'm' . $this->dateDelimiter . 'd',
            $mergedParams['time']
        );
        $url = $timeString . $this->paramDelimiter . $url;
        $url = urlencode($mergedParams['category'])
             . $this->paramDelimiter . $url;

        return $this->paramDelimiter
            . trim($this->prefix, $this->paramDelimiter)
            . $this->paramDelimiter . $url;
    }
}
