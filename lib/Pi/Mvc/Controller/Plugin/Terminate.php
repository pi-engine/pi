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

/**
 * Terminate an action
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Terminate extends AbstractPlugin
{
    /**
     * Terminate an action and display corresponding messages
     *
     * @param string $message   Message to display or type of messages: login, register
     * @param string $title
     * @param string $template
     *
     * @return void
     */
    public function __invoke(
        $message    = '',
        $title      = '',
        $template   = ''
    ) {
        $template = $template ?: 'error.phtml';
        switch ($message) {
            case 'login':
                $title = __('Login');
                $link = Pi::service('user')->getUrl('login');
                $message = sprintf(
                    __('Please <a href="%s" title="">login</a> and come again.'),
                    $link
                );
                break;

            default:
                break;
        }
        $view = $this->getController()->plugin('view');
        $view->setTemplate($template, false);
        $view->assign(array(
            'message'   => $message,
            'title'     => $title,
        ));

        return;
    }
}
