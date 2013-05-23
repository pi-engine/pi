<?php
/**
 * Action controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Front;
use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Public action controller
 */
class TestController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->view()->setTemplate(false);
    }

    /**
     * Audit log test
     *
     * @return ViewModel
     */
    public function auditAction()
    {
        $args = array(rand(), 'var1', 'var, val and exp');
        Pi::service('audit')->log('full', $args);
        Pi::service('audit')->log('csv', $args);
        Pi::service('audit')->log('lean', $args);
        Pi::service('audit')->log('test', $args);

        $args = array(rand(), 'var2', 'var, val and exp');
        Pi::service('audit')->log('full', $args);
        Pi::service('audit')->log('csv', $args);
        Pi::service('audit')->log('lean', $args);
        Pi::service('audit')->log('test', $args);

        $this->view()->setTemplate(false);
    }

    /**
     * Mail service test
     */
    public function mailAction()
    {
        $this->view()->setTemplate(false);

        $vars = array(
            'username'      => 'Tester',
            'sendername'    => 'Pi Admin',
            'sn'            => _date(),
        );
        $result = Pi::service('mail')->template('mail-html', $vars);

        // Set subject and body
        $subject = $result['subject'];
        $body = $result['body'];
        $type = $result['format'];
        // Set message
        $message = Pi::service('mail')->message($subject, $body, $type);

        $message->addTo(Pi::config('from', 'mail'), Pi::config('fromname', 'mail'));
        $message->addTo(array(
            'infomax@gmail.com'             => 'Pi GMail',
            'taiwenjiang@tsinghua.org.cn'   => 'Pi THU',
        ));

        /*
        $options = array(
            'name'              => '[name]',
            'host'              => '[smtp host]',
            'port'              => 25,
            'connection_class'  => 'login',
            'connection_config' => array(
                'username' => '[username]',
                'password' => '[userpass]',
            ),
        );

        $transport = Pi::service('mail')->loadTransport('smtp', $options);

        // Send mail through service
        Pi::service('mail')->setTransport($transport);
        */

        // Send mail
        $result = Pi::service('mail')->send($message);

        return $result ? 'Mail sent successfully:' . _date(time()) : 'Failed.';
    }
}
