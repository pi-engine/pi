<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Debug\Debug;

/**
 * Test cases controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TestController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return string
     */
    public function indexAction()
    {
        //$this->view()->setTemplate(false);
        $content = array();

        $url = Pi::service('url')->assemble('', array('action' => 'index'), array('fragment' => '!/test'));
        echo $url;

        $text = <<<EOT
        Test for user and tag:
            @admin tested tag #good# ok?
EOT;
        $content['User and tag'] = Pi::service('markup')->render($text);

        $url = '/user/profile';
        $routeMatch = Pi::service('url')->route($url);
        $content['URL'] = $routeMatch;

        $content['site name'] = __('site name');
        $content['locale'] = Pi::service('i18n')->locale
                           . ' ' . Pi::service('i18n')->charset;

        //Pi::service('user')->test('ss');

        $display = '';
        foreach ($content as $title => $data) {
            $string = $title && is_string($title)
                ? '<dt style="margin-top: 1em;text-decoration: underline;">'
                    . '<strong>' . $title . '</strong></dt>'
                : '';
            if (is_scalar($data)) {
                $string .= $data;
            } else {
                ob_start();
                var_dump($data);
                $string .= '<pre>' . ob_get_clean() . '</pre>';
            }
            $display .= $string;
        }

        echo $display;
    }

    /**
     * Audit log test
     *
     * @return void
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

        Pi::service('audit')->attach('custom', array(
            'file'  => Pi::path('log') . '/custom.csv'
        ));
        Pi::service('audit')->log('custom', $args);

        $this->view()->setTemplate(false);
    }

    /**
     * Mail service test
     *
     * @return string
     */
    public function mailAction()
    {
        $this->view()->setTemplate(false);

        $to = array(
            Pi::config('adminmail') => Pi::config('adminname'),
            'infomax@gmail.com'             => 'Pi GMail',
            'taiwenjiang@tsinghua.org.cn'   => 'Pi THU',
        );
        $vars = array(
            'username'      => 'Pier',
            'sn'            => _date(),
        );

        // Load from text template
        $data = Pi::service('mail')->template('mail-text', $vars);

        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];
        // Set message
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);

        // Enable following code to use instant custom transport
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


        // Load from HTML template
        $data = Pi::service('mail')->template('mail-html', $vars);
        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];
        // Set message
        $message = Pi::service('mail')->message();
        $message->addTo($to);
        $message->addTo('piengine@163.com', 'Netease');
        $message->setSubject($subject);
        $body = Pi::service('mail')->mimeMessage($body, $type);
        $message->setBody($body);
        // Send mail
        $result = Pi::service('mail')->send($message);


        // Load from raw body template
        $data = Pi::service('mail')->template('mail-body', $vars);
        // Set subject and body
        $subject = sprintf(__('Greetings in raw body %d'), time());
        $body = $data['body'];
        $type = '';
        // Set message
        $message = Pi::service('mail')->message();
        $message->addTo($to);
        $message->addTo('piengine@163.com', 'Netease');
        $message->setSubject($subject);
        $body = Pi::service('mail')->mimeMessage($body, $type);
        $message->setBody($body);
        // Send mail
        $result = Pi::service('mail')->send($message);

        return $result ? 'Mail sent successfully:' . _date(time()) : 'Failed.';
    }
}
