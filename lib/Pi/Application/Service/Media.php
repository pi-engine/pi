<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Media\Adapter\AbstractAdapter;

/**
 * Media service
 * 
 * Media APIs
 * 
 * @method \Pi\Media\Adapter\AbstractAdapter::upload($meta, $options = array())
 * @method \Pi\Media\Adapter\AbstractAdapter::update($id, $data)
 * @method \Pi\Media\Adapter\AbstractAdapter::activate($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::deactivate($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::getAttributes($id, $attribute)
 * @method \Pi\Media\Adapter\AbstractAdapter::getAttributesList($ids, $attribute)
 * @method \Pi\Media\Adapter\AbstractAdapter::getStats($id, $statistics)
 * @method \Pi\Media\Adapter\AbstractAdapter::getStatsList($ids, $statistics)
 * @method \Pi\Media\Adapter\AbstractAdapter::getFileIds($condition, $limit = null, $offset = null, $order = null)
 * @method \Pi\Media\Adapter\AbstractAdapter::getList($condition, $limit = null, $offset = null, $order = null)
 * @method \Pi\Media\Adapter\AbstractAdapter::getCount($condition = array())
 * @method \Pi\Media\Adapter\AbstractAdapter::getUrl($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::getUrlList($ids)
 * @method \Pi\Media\Adapter\AbstractAdapter::download($ids)
 * @method \Pi\Media\Adapter\AbstractAdapter::delete($ids)
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Media extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'media';
    
    /**
     * Service handler adapter
     * 
     * @var AbstractAdapter 
     */
    protected $adapter;
    
    /**
     * Set service adapter
     * 
     * @param AbstractAdapter $adapter
     * @return self
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
        
        return $this;
    }
    
    /**
     * Get service adapter
     *
     * @param string $name
     *
     * @return AbstractAdapter
     * @throws \Exception 
     */
    public function getAdapter($name = '')
    {
        $loadAdapter = function ($name) {
            $configs = $this->getOption($name);
            $options = $configs['options'];
            $class = 'Pi\Media\Adapter\\' . ucfirst($name);
            if (!class_exists($class)) {
                throw new \Exception(sprintf('Class %s not found.', $class));
            }
            $adapter = new $class($options);

            return $adapter;
        };

        if (!$name) {
            if (!$this->adapter instanceof AbstractAdapter) {
                $name = $this->getOption('adapter');
                $this->adapter = $loadAdapter($name);
            }
            $result = $this->adapter;
        } else {
            $result = $loadAdapter($name);
        }

        return $result;
    }
    
    /**
     * Get media variables
     * 
     * @param string $var
     * @return mixed 
     */
    public function __get($var)
    {
        $result = $this->getAdapter()->{$var};
        
        return $result;
    }
    
    /**
     * Call APIs defined in media adapter
     * 
     * @param string  $method
     * @param array   $args
     * @return mixed 
     */
    public function __call($method, $args)
    {
        $result = call_user_func_array(
            array($this->getAdapter(), $method),
            $args
        );
        return $result;
    }
}
