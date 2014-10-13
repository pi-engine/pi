<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;
use Module\Article\Model\Article as ModelArticle;

/**
 * Related handler
 *
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Related extends CustomCompoundHandler
{
    /** @var string Field name and table name */
    protected $name = 'related';

    /** @var string Form class */
    protected $form = '';

    /** @var string File to form template */
    protected $template = '';

    /** @var string Form filter class */
    protected $filter = '';
    
    /**
     * {@inheritDoc}
     */
    public function resolve($data, $options = array())
    {
        $result = array();
        
        foreach ($data as $row) {
            array_walk($row, function($value, $key) use (&$result) {
                $method = 'resolve' . ucfirst(strtolower($key));
                $result = array_merge($result, $this->$method($value));
            });
        }
        
        return $result;
    }
    
    /**
     * Get article title, url by article ID
     * 
     * @param string|array $value  Article ID, or article IDs combine by ','
     * @return array
     */
    public function resolveRelated($value)
    {
        if (is_string($value) && !is_numeric($value)) {
            $value = explode(',', $value);
        } else {
            $value = (array) $value;
        }
        
        $result = array();
        if (!empty($value)) {
            $where   = array(
                'id'        => $value,
                'status'    => ModelArticle::FIELD_STATUS_PUBLISHED,
                'time_publish < ?' => time(),
                'active'    => 1,
            );
            $columns = array('id', 'subject', 'time_publish');

            $model  = Pi::model('article', $this->module);
            $select = $model->select()->columns($columns)->where($where);
            $rowset = $model->selectWith($select);
            
            foreach ($rowset as $row) {
                $item = $row->toArray();
                $item['url'] = Pi::api('api', $this->module)->getUrl(
                    'detail',
                    array(
                        'time' => date('Ymd', $row->time_publish),
                        'id'   => $row->id,
                    ),
                    $item
                );
                $result[$row->id] = $item;
            }
        }
        
        return $result;
    }
    
    /**
     * Add related article into related table
     * 
     * @param int   $id
     * @param array $data
     */
    public function add($id, $data)
    {
        $article = array();
        
        foreach ($data as $row) {
            $items   = explode(',', $row['related']);
            $article = array_merge($article, $items);
        }
        $article = array_filter($article);
        
        $rows = array();
        foreach ($article as $value) {
            $rows[] = array('related' => $value);
        }
        
        parent::add($id, $rows);
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $this->getModel()->delete(array('article' => (array) $id));

        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function encode($id)
    {
        $rows = parent::encode($id);
        $articleIds = array();
        array_walk($rows, function($value) use (&$articleIds) {
            $articleIds[] = $value['related'];
        });
        $result[0] = array('related' => implode(',', $articleIds));
        
        return array($this->name => $result);
    }
}
