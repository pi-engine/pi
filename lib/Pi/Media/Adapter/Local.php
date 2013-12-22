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
 * Local media service provided by media module
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Local extends AbstractAdapter
{
    /**
     * Get server module name
     * 
     * @return string 
     */
    protected function getServer()
    {
        return isset($this->options['server']) 
            ? $this->options['server'] : 'media';
    }
    
    /**
     * {@inheritDoc}
     */
    public function upload($meta, $options = array())
    {
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->upload($meta, $options);
        if ($result['absolute_path']) {
            $dispatch = $this->getDispatch();
            if ($dispatch->copy($meta['source'], $result['absolute_path'])) {
                $result['url'] = $result['relative_path'];
                if (!$this->update($result['id'], $result)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function update($id, $data)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->update($id, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function activate($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->activate($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function deactivate($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->deactivate($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAttributes($id, $attribute = '')
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getAttributes($id, $attribute);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAttributesList(array $ids, $attribute = '')
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getAttributesList($ids, $attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function getStats($id, $statistics)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getStats($id, $statistics);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids, $statistics)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getStatsList($ids, $statistics);
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
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->getFileIds(
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
        $limit = null,
        $offset = null,
        $order = null
    ) {
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->getList(
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
    public function getCount(array $condition = array())
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getCount($condition);
    }
    
    /**
     * Get file url
     * 
     * @param int $id 
     * @return string
     */
    public function getUrl($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getUrl($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getUrlList(array $ids)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getUrlList($ids);
    }
    
    /**
     * {@inheritDoc}
     */
    public function download(array $ids)
    {
        $server = $this->getServer();
        Pi::api($server, 'media')->download($ids);
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete(array $ids)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->delete($ids);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getValidator($adapter = null)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getValidator($adapter);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getServerConfig()
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getServerConfig();
    }
}
