<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Media\Adapter\AbstractAdapter;

/**
 * Media service
 * 
 * Meida APIs
 * 
 * @method \Pi\Media\Adapter\AbstractAdapter::upload($meta, $options = array())
 * @method \Pi\Media\Adapter\AbstractAdapter::update($id, $data)
 * @method \Pi\Media\Adapter\AbstractAdapter::activeFile($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::deactivateFile($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::getAttributes($id, $attribute)
 * @method \Pi\Media\Adapter\AbstractAdapter::mgetAttributes($ids, $attribute)
 * @method \Pi\Media\Adapter\AbstractAdapter::getStatistics($id, $statistics)
 * @method \Pi\Media\Adapter\AbstractAdapter::mgetStatistics($ids, $statistics)
 * @method \Pi\Media\Adapter\AbstractAdapter::getFileIds($condition, $limit = null, $offset = null, $order = null)
 * @method \Pi\Media\Adapter\AbstractAdapter::getList($condition, $limit = null, $offset = null, $order = null)
 * @method \Pi\Media\Adapter\AbstractAdapter::getCount($condition = array())
 * @method \Pi\Media\Adapter\AbstractAdapter::getUrl($id)
 * @method \Pi\Media\Adapter\AbstractAdapter::mgetUrl($ids)
 * @method \Pi\Media\Adapter\AbstractAdapter::download($ids)
 * @method \Pi\Media\Adapter\AbstractAdapter::delete($ids)
 * 
 * @author Zongshu Lin <lin40553024@163.com>
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
     * @return AbstractAdapter
     * @throws \Exception 
     */
    public function getAdapter()
    {
        if (!$this->adapter instanceof AbstractAdapter) {
            $adapter = $this->getOption('adapter');
            $class = $this->getOption($adapter, 'class');
            if ($class) {
                $options = (array) $this->getOption($adapter, 'options');
            } else {
                $class   = sprintf('Pi\\Media\\Adapter\\%s', $adapter);
                $options = array();
            }
            
            if (class_exists($class)) {
                $this->adapter = new $class($options);
            } else {
                $message = sprintf('Class %s is not exists!', $class);
                throw new \Exception($message);
            }
        }
        
        return $this->adapter;
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
