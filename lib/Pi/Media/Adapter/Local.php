<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Media\Adapter;

use Pi;

/**
 * Local media service provided by media module
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Local extends AbstractAdapter
{
    /**
     * Get api handler
     * 
     * @return string 
     */
    protected function handler()
    {
        return Pi::api('doc', 'media');
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options = array())
    {
        parent::setOptions($options);
        //$this->handler()->setOptions($options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function add(array $data)
    {
        $result = $this->handler()->add($data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function upload($file, array $data = array())
    {
        $data['file'] = $file;
        $result = $this->handler()->upload($data, 'MOVE');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function download($id)
    {
        $result = $this->handler()->download($id);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, array $data)
    {
        $result = $this->handler()->update($id, $data);

        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function activate($id, $flag = true)
    {
        $result = $this->handler()->activate($id, $flag);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $attr = array())
    {
        $result = $this->handler()->get($id, $attr);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget(array $ids, $attr = array())
    {
        $result = $this->handler()->mget($ids, $attr);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($id)
    {
        $result = $this->handler()->getUrl($id);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats($id)
    {
        $result = $this->handler()->getStats($id);

        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids)
    {
        $result = $this->handler()->getStatsList($ids);

        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getIds(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = ''
    ) {
        $result = $this->handler()->getIds(
            $condition,
            $limit,
            $offset,
            $order
        );
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getList(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = '',
        array $attr = array()
    ) {
        $result = $this->handler()->getList(
            $condition,
            $limit,
            $offset,
            $order,
            $attr
        );
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCount(array $condition = array())
    {
        $result = $this->handler()->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $result = $this->handler()->delete($id);

        return $result;
    }
}
