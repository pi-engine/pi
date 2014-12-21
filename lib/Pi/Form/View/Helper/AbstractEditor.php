<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Pi;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormTextarea;

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

    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load config from file
     */
    protected function loadConfig()
    {
        if (!empty($this->configFile)) {
            $this->options = Pi::config()->load($this->configFile);
        }
    }
}
