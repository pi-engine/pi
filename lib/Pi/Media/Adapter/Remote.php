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
        $query = array();
        array_walk($data, function ($value, $key) use (&$query) {
            $query[] = $key . ':' . $value;
        });
        $params['query'] = implode(',', $query);
        $uri    = $this->getOption('api', 'add');
        $result = $this->handler()->post($uri, $query);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function upload($file, array $data = array())
    {
        $uri    = $this->getOption('api', 'upload');
        $result = $this->handler()->upload($uri, $file, $data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function download($id, $file = '')
    {
        $uri    = $this->getUrl($id);
        $result = $this->handler()->download($uri, $file);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, array $data)
    {
        $query = array('id' => $id);
        array_walk($data, function ($value, $key) use (&$query) {
            $query[] = $key . ':' . $value;
        });
        $params['query'] = implode(',', $query);
        $uri    = $this->getOption('api', 'update');
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
        $params = array(
            'id'    => $id,
            'field' => implode(',', (array) $attr),
        );
        $uri    = $this->getOption('api', 'get');
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
        $params = array(
            'id'    => implode(',', $ids),
            'field' => implode(',', (array) $attr),
        );
        $uri    = $this->getOption('api', 'mget');
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
        $params = array('id' => $id);
        $uri    = $this->getOption('api', 'stats');
        $result = $this->handler()->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids)
    {
        $params = array('id' => implode(',', $ids));
        $uri    = $this->getOption('api', 'mstats');
        $result = $this->handler()->get($uri, $params);

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
        $uri    = $this->getOption('api', 'list');
        $result = $this->handler()->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(array $condition = array())
    {
        $params = array();
        if ($condition) {
            $query = array();
            array_walk($condition, function ($value, $key) use (&$query) {
                $query[] = $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }
        $uri    = $this->getOption('api', 'count');
        $result = $this->handler()->get($uri, $params);
        $result = (int) $result['data'];

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $params = array('id' => $id);
        $uri    = $this->getOption('api', 'delete');
        $result = $this->handler()->post($uri, $params);

        return $result;
    }
}
