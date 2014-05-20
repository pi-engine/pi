<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller\Plugin;

use Pi;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Http\Response;

/**
 * Jump to a page going through a transition page
 *
 * Sample code:
 *
 * ```
 *  // Jump to a direct URL
 *  $this->jump(<URI>, <Message>, <type: error, success, info, default>);
 *
 *  // Jump to a routed URL
 *  $this->jump(array('route' => <route-name>,
 *      'controller' => <controller-name>), <Message>);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Jump extends AbstractPlugin
{
    /**
     * Jump to a page with message
     *
     * @param string|array $params URI or params to assemble URI
     * @param string $message Message to display on transition page
     * @param string $namespace success, error, info, default
     * @param bool $allowExternal Allow external links
     *
     * @return void
     */
    public function __invoke(
        $params,
        $message        = '',
        $namespace      = '',
        $allowExternal  = false
    ) {
        if (is_array($params)) {
            if (!isset($params['route'])) {
                $route = '';
            } else {
                $route = $params['route'];
                unset($params['route']);
            }
            $url = Pi::service('url')->assemble($route, $params, true);
        } else {
            $url = $params;
            if (preg_match('/^(http[s]?:\/\/|\/\/)/i', $url)) {
                if (!$allowExternal
                    && '' !== stristr($url, Pi::url('www'), true)
                ) {
                    $url = Pi::url('www');
                }
            } elseif ('/' != $url[0]) {
                $url = Pi::url('www') . '/' . $url;
            }
        }

        $controller = $this->getController();
        if ($message) {
            $messenger = $controller->plugin('flashMessenger');
            if ($namespace && is_string($namespace)) {
                $messenger->setNamespace($namespace);
            }
            $messenger->addMessage($message);
        }
        $controller->plugin('redirect')->toUrl($url);

        exit;
    }
}
