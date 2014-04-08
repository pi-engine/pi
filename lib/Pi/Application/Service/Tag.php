<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Module\Tag\Service as TagService;

/**
 * Tag service
 *
 * <code>
 *  Pi::service('tag')->add('article', 5, '', array('news', 'tech'));
 *  Pi::service('tag')->add('article', 5, '', 'news tech');
 *
 *  Pi::service('tag')->update('article', 5, '', array('news', 'tech'));
 *  Pi::service('tag')->update('article', 5, '', 'news tech');
 *  Pi::service('tag')->update('article', 5, '', array());
 *
 *  Pi::service('tag')->delete('article', 5, '');
 *  Pi::service('tag')->delete('article', 5);
 *
 *  Pi::service('tag')->get('article', 5, '');
 *
 *  Pi::service('tag')->getList('article', 'news', '', 100, 90);
 *  Pi::service('tag')->getList('article', 'news', null, 100);
 *
 *  Pi::service('tag')->getCount('article', 'news', '');
 *  Pi::service('tag')->getCount('article', 'tech');
 *
 *  Pi::service('tag')->match('n', 5, 'article');
 *  Pi::service('tag')->match('new', 5);
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends AbstractService
{
    /**
     * Whether or not the service is active, or tag module is activated
     *
     * @var bool
     */
    protected $active = null;

    /**
     * Is tag service available
     *
     * @return bool
     */
    public function active()
    {
        if (null === $this->active) {
            $this->active = Pi::service('module')->isActive('tag');
        }

        return $this->active;
    }

    /**
     * Undefined method handler allows a shortcut
     *
     * @param  string  $method  priority name
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!$this->active()) {
            return false;
        }
        if (method_exists(Pi::api('api', 'tag'), $method)) {
            return call_user_func_array(array(Pi::api('api', 'tag'), $method), $args);
        }
        
        return null;
    }
}
