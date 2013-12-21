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
class Module extends AbstractAdapter
{
    /**
     * Current module
     * @var string 
     */
    protected $module;
    
    /**
     * Get current module name
     * @return string 
     */
    protected function getModule()
    {
        if (!$this->module) {
            $this->module = Pi::service('module')->current();
        }
        
        return $this->module;
    }
    
    /**
     * {@inheritDoc}
     */
    public function upload($meta, $options = array())
    {
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->upload($meta, $options);
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
        $module = $this->getModule();
        return Pi::api($module, 'media')->update($id, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function activate($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->activate($id);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->deactivate($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes($id, $attribute)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getAttributes($id, $attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributesList(array $ids, $attribute)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getAttributesList($ids, $attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function getStats($id, $statistics)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getStats($id, $statistics);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsList(array $ids, $statistics)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getStatsList($ids, $statistics);
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
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->getFileIds(
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
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->getList(
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
        $module = $this->getModule();
        return Pi::api($module, 'media')->getCount($condition);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getUrl($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlList(array $ids)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getUrlList($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function download(array $ids)
    {
        $module = $this->getModule();
        Pi::api($module, 'media')->download($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(array $ids)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->delete($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function getValidator($adapter = null)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getValidator($adapter);
    }

    /**
     * {@inheritDoc}
     */
    public function getServerConfig()
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getServerConfig();
    }
}
