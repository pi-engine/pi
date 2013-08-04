<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Storage;

use Pi;
use Zend\Authentication\Storage\Session as ZendSession;

/**
 * Sessioin storage for authentication
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Session extends ZendSession
{
    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $namespace  = $options['namespace'];
        $member     = $options['member'];
        $sessionManager = isset($options['session_manager'])
            ? $options['session_manager'] : Pi::service('session')->manager();
        parent::__construct($namespace, $member, $sessionManager);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions($options = array())
    {
        $this->options = $options;
    }
}
