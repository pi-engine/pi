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

/**
 * Cluster element handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Cluster extends CustomCommonHandler
{
    /**
     * {@inheritDoc}
     */
    public function getModel()
    {
        return Pi::model('cluster_article', $this->module);
    }
    
    /**
     * Insert asset into table
     * 
     * @param int  $id
     * @param array|string|int $data
     * @return bool
     */
    public function add($id, $data)
    {
        $data  = (array) $data;
        
        $table = $this->getModel()->getTable();
        $sql   = "INSERT INTO `{$table}` (`article`, `cluster`) VALUES ";
        foreach ($data as $row) {
            $sql .=<<<EOD
($id, $row),
EOD;
        }
        $sql = rtrim($sql, ',') . ';';
        
        try {
            Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $result = array();
        
        if (empty($value)) {
            return $result;
        }
        
        $result = Pi::api('cluster', $this->module)->getList(array(
            'id' => (array) $value,
        ));
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function encode($id)
    {
        $data = $this->get($id);
        
        $form = Pi::api('form', $this->module)->loadForm('draft');
        if ($form->has($this->name)) {
            $isMultiple = $form->get($this->name)->getOption('is_multiple');
        }
        
        if (!$isMultiple) {
            return array($this->name => $data[0]['cluster']);
        }
        
        $result = array();
        array_walk($data, function($value) use (&$result) {
            $result[] = $value['cluster'];
        });
        
        return array($this->name => $result);
    }
}
