<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Search\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Search controller
 *
 * @author  Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    /**
     * Search in avaliable modules
     *
     * @return void
     */
    public function indexAction()
    {
        $query  = $this->params('q');
        $module = (array) $this->params('m');

        $moduleSearch   = Pi::registry('search')->read();
        $moduleList     = Pi::registry('modulelist')->read();
        $modules        = array();
        $selected       = array();
        array_wark($moduleSearch, function ($callback, $name) use (
            &$modules,
            &$selected,
            $moduleList,
            $m
        ) {
            if (!isset($moduleList[$name])) {
                return;
            }
            $node = $moduleList[$name];
            $modules[$name] = array(
                'id'        => $node['id'],
                'title'     => $node['title'],
                'icon'      => $node['icon'],
                //'callback'  => $callback,
            );
            if (in_array($node['id'], $m)) {
                $selected[] = $name;
                $modules[$name]['selected'] = 1;
            }
        });

        if ($query) {
            $terms  = $query ? $this->parseQuery($query) : array();
            if ($terms) {
                $limit  = $this->config('leading_limit');
                $result = $this->query($terms, $selected, $limit);
            } else {
                $result = array();
            }
            $total = 0;
            foreach ($result as $name => $data) {
                $total += $data->getTotal();
            }
            $this->view()->assign(array(
                'query'     => $query,
                'result'    => $result,
                'total'     => $total,
            ));
            $this->view()->setTemplate('search-result');
        } else {
            $this->view()->setTemplate('search-form');
        }
        $this->view()->assign(array(
            'modules'   => $modules,
            'selected'  => $selected,
        ));
    }

    /**
     * Search in a specific module
     */
    public function moduleAction()
    {
        $query  = $this->params('q');
        $page   = $this->params('p');
        $module = $this->params('m');

        $moduleSearch   = Pi::registry('search')->read();
        $moduleList     = Pi::registry('modulelist')->read();
        if (!isset($moduleSearch[$module]) || !isset($moduleList[$module])) {
            $this->redirectTo(array('action' => 'index'));
            return;
        }

        if ($query) {
            $terms  = $query ? $this->parseQuery($query) : array();
            $total  = 0;
            if ($terms) {
                $limit  = $this->config('list_limit');
                $result = $this->query($terms, $module, $limit);
                $total  = $result ? $result->getTotal() : 0;
            } else {
                $result = array();
            }
            $this->view()->assign(array(
                'query'     => $query,
                'result'    => $result,
                'total'     => $total,
            ));
            $this->view()->setTemplate('search-module-result');
        } else {
            $this->view()->setTemplate('search-module-form');
        }
        $this->view()->assign(array(
            'selected'  => $module,
        ));
    }

    /**
     * Parse search terms from query string
     *
     * @param string $query
     *
     * @return array
     */
    protected function parseQuery($query = '')
    {
        $terms = array();
        $pattern = '/(["\'])(?>[^"\']|["\'](?<!\1)|(?<=\\)\1)*+\1/';
        $callback = function ($m) use (&$terms) {
            $terms[] = $m[1];
            return '';
        };
        $string = preg_replace_callback($pattern, $callback, $query);
        $list = explode(' ', $string);
        $length = $this->config('min_length');
        array_walk($list, function ($term) use (&$terms, $length) {
            if (!$length || strlen($term) >= $length) {
                $terms[] = $term;
            }
        });

        return $terms;
    }

    /**
     * Do search query
     *
     * @param array        $terms
     * @param string|array $in
     * @param int          $limit
     * @param int          $offset
     *
     * @return array
     */
    protected function query(array $terms, $in = '', $limit = 0, $offset = 0)
    {
        $moduleSearch   = Pi::registry('search')->read();
        $moduleList     = Pi::registry('modulelist')->read();
        $modules        = (array) $in;
        $result         = array();
        foreach ($moduleSearch as $name => $callback) {
            if ($modules && !in_array($name, $modules)) {
                continue;
            }
            if (!isset($moduleList[$name])) {
                continue;
            }
            $searchHandler = new $callback($name);
            $result[$name] = $searchHandler->query($terms, $limit, $offset);
        }

        if (is_scalar($in)) {
            $result = isset($result[$in]) ? $result[$in] : array();
        }

        return $result;
    }
}
