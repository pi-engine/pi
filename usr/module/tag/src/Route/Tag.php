<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * Tag route
 *
 * Use cases
 *
 * - Term page
 *   - /term/<term-text>[/m/<module>]
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'tag',
        'controller'    => 'index',
        'action'        => 'list'
    );

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = array();

        $parts = array();
        if ($path) {
            $parts = explode($this->paramDelimiter, $path);
            $matches['tag'] = $this->decode(array_shift($parts));
        }
        if ($parts) {
            $matches = array_merge(
                (array) $matches,
                $this->parseParams($parts)
            );
        }
        if (isset($matches['module'])) {
            $matches['m'] = $matches['module'];
            unset($matches['module']);
        }

        $matches = array_merge($this->defaults, $matches);

        return $matches;
    }

    /**
     * {@inheritDoc}
     */
    protected function assembleParams(array $params)
    {
        $term = '';
        if (isset($params['term'])) {
            $term = $params['term'];
            unset($params['term']);
        }
        if (isset($params['tag'])) {
            $term = $params['tag'];
            unset($params['tag']);
        }
        $url = $this->encode($term);
        foreach ($params as $key => $value) {
            if (in_array($key, array('module', 'controller', 'action'))) {
                continue;
            }
            if (null === $value || '' === $value) {
                continue;
            }
            $url .= $this->paramDelimiter . $this->encode($key)
                . $this->keyValueDelimiter . $this->encode($value);
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    protected function assembleStructure(array $params, $url = '')
    {
        return $url;
    }
}