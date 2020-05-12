<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Storage;

use Pi;
use Laminas\Authentication\Storage\Session as ZendSession;

/**
 * Session storage for authentication
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Session extends ZendSession
{
    /** @var array Options */
    protected $options = [];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $namespace      = $options['namespace'];
        $member         = $options['member'];
        $sessionManager = isset($options['session_manager'])
            ? $options['session_manager'] : Pi::service('session')->manager();
        parent::__construct($namespace, $member, $sessionManager);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions($options = [])
    {
        $this->options = $options;
    }
}
