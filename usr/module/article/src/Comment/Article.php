<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Comment;

use Pi;
use Pi\Application\AbstractComment;
use Module\Article\Service;

/**
 * Comment target callback handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Article extends AbstractComment
{
    /** @var string */
    protected $module = 'article';

    /**
     * Get target data
     *
     * @param int|int[] $item Item id(s)
     *
     * @return array
     */
    public function get($item)
    {
        $result = array();
        $items = (array) $item;

        $uid   = Pi::user()->id;
        $route = Service::getRouteName($this->module);
        
        // Get articles
        $where   = array('id' => $items);
        $columns = array('id', 'subject', 'time_publish');
        $modelArticle = Pi::model('article', $this->module);
        $articles = $modelArticle->getSearchRows($where, null, null, $columns);
        
        // Get article slugs
        $modelExtended = Pi::model('extended', $this->module);
        $rowset = $modelExtended->select(array('article' => $items));
        $slugs  = array();
        foreach ($rowset as $row) {
            $slugs[$row->article] = $row->slug;
        }
        
        foreach ($items as $id) {
            $result[$id] = array(
                'title' => $articles[$id]['subject'],
                'url'   => Pi::service('url')->assemble(
                    $route,
                    array(
                        'id'   => $id,
                        'slug' => $slugs[$id],
                        'time' => $articles[$id]['time_publish'],
                    )
                ),
                'uid'   => $uid,
                'time'  => time(),
            );
        }

        if (is_scalar($item)) {
            $result = $result[$item];
        }

        return $result;
    }
}
