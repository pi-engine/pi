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
     * {@inheritDoc}
     */
    public function upload($meta, $options = array())
    {
        if (!is_array($meta)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        
        $uri = $this->getConfig('url', 'upload');
        $params = array();
        $params['meta'] = json_encode($meta);
        if (!empty($options)) {
            $params['options'] = json_encode($options);
        }
        
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        if ($result['absolute_path']) {
            $dispatch = $this->getDispatch();
            if ($dispatch->copy($meta['source'], $result['absolute_path'])) {
                $result['url'] = $result['relative_path'];
                $this->update($result['id'], $result);
            }
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, $data)
    {
        if (!$id || is_array($id)) {
            return false;
        }
        
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $params = array(
            'id'    => $id,
        );
        $params['data'] = json_encode($data);
        $uri = $this->getConfig('url', 'update');
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function activate($id)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate($id)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes($id, $attribute)
    {
        if (!$id) {
            return false;
        }
        if (is_scalar($id)) {
            $uri = $this->getConfig('url', 'get_attributes');
        } else {
            $uri = $this->getConfig('url', 'mget_attributes');
            $id = implode(',', $id);
        }
        $params = array(
            'id'    => $id,
        );
        $params['attribute'] = implode(',', (array) $attribute);
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributesList(array $ids, $attribute)
    {
        $result = $this->getAttributes($ids, $attribute);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats($id, $statistics)
    {
        if (!$id) {
            return false;
        }
        if (is_scalar($id)) {
            $uri = $this->getConfig('url', 'get_statistics');
        } else {
            $uri = $this->getConfig('url', 'get_statistics_list');
            $id = implode(',', $id);
        }
        $params = array(
            'id'    => $id,
        );
        $params['statistics'] = implode(',', (array) $statistics);
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids, $statistics)
    {
        $result = $this->getStatistics($ids, $statistics);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getFileIds(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        if (!is_array($condition)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $uri = $this->getConfig('url', 'get_file_ids');
        $params = array();
        if ($condition) {
            $params['query'] = json_encode($condition);
        }
        if ($limit) {
            $params['limit'] = $limit;
        }
        if ($offset) {
            $params['offset'] = $offset;
        }
        if ($order) {
            $params['order'] = $order;
        }
        
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getList(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        if (!is_array($condition)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $uri = $this->getConfig('url', 'get_list');
        $params = array();
        if ($condition) {
            $params['query'] = json_encode($condition);
        }
        if ($limit) {
            $params['limit'] = $limit;
        }
        if ($offset) {
            $params['offset'] = $offset;
        }
        if ($order) {
            $params['order'] = $order;
        }
        
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(array $condition = array())
    {
        if (!is_array($condition)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $uri = $this->getConfig('url', 'get_count');
        $params = array();
        if ($condition) {
            $params['query'] = json_encode($condition);
        }
        
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return array_shift($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($id)
    {
        if (!$id) {
            return false;
        }
        if (is_scalar($id)) {
            $uri = $this->getConfig('url', 'get_url');
        } else {
            $uri = $this->getConfig('url', 'mget_url');
            $id = implode(',', $id);
        }
        $params = array(
            'id'    => $id,
        );
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return array_shift($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlList(array $ids)
    {
        $rowset = $this->getUrl($ids);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row['id']] = $row['url'];
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function download(array $ids)
    {
        if (!$ids) {
            return false;
        }
        if (!is_scalar($ids)) {
            $id = implode(',', $ids);
        }
        $uri = $this->getConfig('url', 'download');
        $location = sprintf('location: %s/id-%s', $uri, $id);
        header($location);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(array $ids)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidator($adapter = null)
    {
        $uri = $this->getConfig('url', 'get_validator');
        $params = array();
        if (!empty($adapter)) {
            $params['adapter'] = $adapter;
        }
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri, $params);
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getServerConfig()
    {
        $uri = $this->getConfig('url', 'get_config');
        $result = Pi::service('remote')
            ->setAuthorization($this->getConfig('authorization'))
            ->get($uri);
        
        return $result;
    }
}
