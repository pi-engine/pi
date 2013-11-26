<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Captcha\AdapterInterface;

/**
 * CAPTCHA service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Captcha extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'captcha';

    /**
     * Load CAPTCHA adapter
     *
     * @param string  $type
     * @param array $options
     *
     * @return AdapterInterface
     */
    public function load($type = null, $options = array())
    {
        $type = $type ?: 'image';
        $class = 'Pi\Captcha\\' . ucfirst($type);
        if (!class_exists($class)) {
            $class = 'Pi\Captcha\\' . ucfirst($type);
        }
        if ($options) {
            $options = array_merge($this->options, $options);
        } else {
            $options = $this->options;
        }
        $captcha = new $class($options);

        return $captcha;
    }
}
