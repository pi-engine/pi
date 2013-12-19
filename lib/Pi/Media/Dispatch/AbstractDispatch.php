<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Media\Dispatch;

use Pi;

abstract class AbstractDispatch
{
    /**
     * Options
     * @var array
     */
    protected $options = array();
    
    /**
     * Configs
     * @var array 
     */
    protected $configs;
    
    /**
     * Constructor
     * 
     * @param array $options 
     */
    public function __construct($configs, $options = array())
    {
        $this->configs = $configs;
        $this->options = $options;
    }
    
    /**
     * Copy source file to target file
     * 
     * @param string $source  source absolute path
     * @param string $target  target absolute path 
     */
    abstract public function copy($source, $target);
}
