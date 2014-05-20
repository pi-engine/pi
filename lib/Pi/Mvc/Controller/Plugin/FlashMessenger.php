<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
     * @param string|string[] $message
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
        $messages = (array) $message;
        foreach ($messages as $msg) {
            $this->addMessage($msg);
        }

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
            $namespaces = array_keys($this->messages);
            $namespace = array_shift($namespaces);
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
