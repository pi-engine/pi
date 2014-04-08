<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractContent;

/**
 * Public API for content fetch
 * 
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Content extends AbstractContent
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'article';

    /**
     * {@inheritDoc}
     */
    protected $table = 'article';

    /**
     * {@inheritDoc}
     */
    protected $meta = array(
        'id'            => 'id',
        'subject'       => 'title',
        'summary'       => 'content',
        'time_publish'  => 'time',
        'uid'           => 'uid',
    );

    /**
     * {@inheritDoc}
     */
    public function ____getList(
        array $variables,
        array $conditions,
        $limit  = 0,
        $offset = 0,
        $order  = array()
    ) {
        $result = array();
        $model = Pi::model('article', $this->module);
        $select = $model->select();
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        $select->columns($this->canonizeVariables($variables));
        $select->where($this->canonizeConditions($conditions));
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $item = $row->toArray();
            $item['title'] = $item['subject'];
            unset($item['subject']);
            $item['url'] = $this->buildUrl($item);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Create link
     *
     * @param array $item
     *
     * @return string
     */
    protected function buildUrl(array $item)
    {
        $link = Pi::service('url')->assemble(
            'article-article',
            array('id' => $item['id'])
        );

        return $link;
    }
}