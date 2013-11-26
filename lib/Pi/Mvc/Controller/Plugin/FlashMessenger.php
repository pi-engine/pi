<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\FlashMessenger as ZendFlashMessenger;

/**
 * Flash Messenger - implement session-based messages
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FlashMessenger extends ZendFlashMessenger
{
    /**
     * Add a message and return messenger
     *
     * @param string $message
     * @param string $namespace
     *
     * @return $this
     */
    public function __invoke($message = null, $namespace = null)
    {
        if (!$message) {
            return $this;
        }
        if ($namespace) {
            $this->setNamespace($namespace);
        }
        $this->addMessage($message);

        return $this;
    }

    /**
     * Load messages
     *
     * @param string $namespace
     *
     * @return array
     */
    public function load($namespace = '')
    {
        $this->getMessagesFromContainer();
        if ($namespace) {
            $result = $this->setNamespace($namespace)->getMessages();
        } elseif ($this->messages) {
            $namespace = array_pop(array_keys($this->messages));
            $messages = $this->messages[$namespace]->toArray();
            $result = array(
                'namespace' => $namespace,
                'messages'  => $messages,
            );
        } else {
            $result = array();
        }

        return $result;
    }
}
