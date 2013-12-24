<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Media\Adapter;

use Pi;

/**
 * Remote media service provided by media module
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Remote extends AbstractAdapter
{
    /**
     * Get api handler
     *
     * @return string
     */
    protected function handler()
    {
        $authorization = $this->getOption('authorization');
        if ($authorization) {
            Pi::service('remote')->setAuthorization($authorization);
        }

        return Pi::service('remote');
    }

    /**
     * {@inheritDoc}
     */
    public function add(array $data)
    {
        $uri = $this->getOption('uri', 'add');
        $result = $this->handler()->post($uri, $data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function upload($file, array $data = array())
    {
        $uri = $this->getOption('uri', 'upload');
        $result = $this->handler()->upload($uri, $file, $data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function download($id, $file)
    {
        $uri = $this->getUrl($id);
        $result = $this->handler()->download($uri, $file);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, array $data)
    {
        $uri = $this->getOption('uri', 'update');
        $data['id'] = $id;
        $result = $this->handler()->post($uri, $data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function activate($id, $flag = true)
    {
        $result = $this->update($id, array('active' => (int) $flag));

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $attr = array())
    {
        $uri = $this->getOption('uri', 'get');
        $params = array(
            'id'    => $id,
            'field' => implode(',', (array) $attr),
        );
        $result = $this->handler()->get($uri, $params);
        if ($attr && is_scalar($attr)) {
            $result = $result[$attr];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget(array $ids, $attr = array())
    {
        $uri = $this->getOption('uri', 'mget');
        $params = array(
            'id'    => implode(',', $ids),
            'field' => implode(',', (array) $attr),
        );
        $result = $this->handler()->get($uri, $params);
        if ($attr && is_scalar($attr)) {
            array_walk($result, function (&$data) use ($attr) {
                $data = $data[$attr];
            });
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($id)
    {
        if (is_array($id)) {
            $result = $this->mget($id, 'url');
        } else {
            $result = $this->get($id, 'url');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats($id)
    {
        $uri = $this->getOption('uri', 'get_stats');
        $params = array('id' => $id);
        $result = $this->handler()->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids)
    {
        $uri = $this->getOption('uri', 'get_stats_list');
        $params = array('id' => $ids);
        $result = $this->handler()->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getIds(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        $result = $this->getList(
            $condition,
            $limit,
            $offset,
            $order,
            array('id')
        );
        array_walk($result, function (&$data) {
            return (int) $data['id'];
        });

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getList(
        array $condition,
        $limit  = null,
        $offset = null,
        $order  = null,
        array $attr = array()
    ) {
        $uri = $this->getOption('uri', 'list');
        $params = array();
        if ($condition) {
            $query = array();
            array_walk($condition, function ($value, $key) use (&$query) {
                $query[] = $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }
        if ($limit) {
            $params['limit'] = (int) $limit;
        }
        if ($offset) {
            $params['offset'] = (int) $offset;
        }
        if ($order) {
            $params['order'] = implode(',', (array) $order);
        }
        if ($attr) {
            $params['field'] = implode(',', (array) $attr);
        }
        $result = $this->handler()->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(array $condition = array())
    {
        $uri = $this->getOption('uri', 'count');
        $params = array();
        if ($condition) {
            $query = array();
            array_walk($condition, function ($value, $key) use (&$query) {
                $query[] = $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }
        $result = $this->handler()->get($uri, $params);
        $result = (int) $result['data'];

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $uri = $this->getOption('uri', 'delete');
        $data['id'] = $id;
        $result = $this->handler()->post($uri, $data);

        return $result;
    }
}
