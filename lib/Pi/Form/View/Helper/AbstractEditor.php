<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Pi;
use Laminas\Form\View\Helper\FormTextarea;

/**
 * Editor element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractEditor extends FormTextarea
{
    /**
     * File name of config data
     *
     * @var string
     */
    protected $configFile = '';

    /** @var array Global options */
    protected $globalOptions = [];

    /** @var array Options */
    protected $options = [];

    /**
     * Constructor
     *
     * @param null|array $options
     */
    public function __construct($options = null)
    {
        $this->loadConfig();
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set specific options
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = array_replace($this->globalOptions, $options);
    }

    /**
     * Load config from file
     */
    protected function loadConfig()
    {
        if (!empty($this->configFile)) {
            $this->globalOptions = Pi::config()->load($this->configFile);
        }
    }
}
