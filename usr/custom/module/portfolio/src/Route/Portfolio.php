<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Custom\Portfolio\Route;

use Pi\Mvc\Router\Http\Standard;

class Portfolio extends Standard
{

    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'partenaires',
        'controller'    => 'index',
        'action'        => 'index'
    );


    /**
     * {@inheritDoc}
     */
    protected $structureDelimiter = '/';

    protected $controllerList = array(
        'index', 'project', 'tag', 'type'
    );

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = array();
        $parts = array_filter(explode($this->structureDelimiter, $path));

        // Set controller
        $matches = array_merge($this->defaults, $matches);
        if (isset($parts[0]) && in_array($parts[0], $this->controllerList)) {
            $matches['controller'] = $this->decode($parts[0]);
        }

        // Make Match
        if (isset($matches['controller'])) {
            switch ($matches['controller']) {
                case 'index':

                    break;

                case 'tag':
                    $matches['slug'] = $parts[1] ? $this->decode($parts[1]) : null;
                    break;

                case 'type':
                    $matches['slug'] = $parts[1] ? $this->decode($parts[1]) : null;
                    break;

                case 'project':
                    $matches['slug'] = $parts[1] ? $this->decode($parts[1]) : null;
                    break;
            }    
        }
        
        return $matches;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(
        array $params = array(),
        array $options = array()
    ) {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }
        
        // Set module
        if (!empty($mergedParams['module'])) {
            $url['module'] = 'partenaires';
        }
        if (!empty($mergedParams['controller']) && $mergedParams['controller'] != 'index') {
            $url['controller'] = $mergedParams['controller'];
        }
        if (!empty($mergedParams['action']) && $mergedParams['action'] != 'index') {
            $url['action'] = $mergedParams['action'];
        }
        if (!empty($mergedParams['slug'])) {
            $url['slug'] = $mergedParams['slug'];
        }
        if (!empty($mergedParams['page'])) {
            $url['page'] = 'page' . $this->paramDelimiter . $mergedParams['page'];
        }

        // Make url
        $url = implode($this->paramDelimiter, $url);

        if (empty($url)) {
            return $this->prefix;
        }
        return $this->paramDelimiter . $url;
    }
}