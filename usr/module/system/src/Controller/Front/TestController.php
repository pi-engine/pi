<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        $this->view()->setTemplate(false);
        $content = array();

        /*
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

        trigger_error('test notice message', E_USER_NOTICE);
        trigger_error('test warning message', E_USER_WARNING);

        $content =<<<'EOT'
# Entity meta for custom user profile fields
CREATE TABLE `{custom}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  -- Custom profile field
  `field`           varchar(64)     NOT NULL,
  `value`           text,

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `field` (`uid`, `field`)
);
EOT;
vd($content);
        //$content = \Pi\Application\Installer\SqlSchema::parseSchema($content);
        $schema = new \Pi\Application\Installer\SqlSchema;
        $content = $schema->parseContent($content);
        vd($content);

        //Pi::user()->data()->increment(1, 'test-int', 3);
        //vd(Pi::user()->data(1, 'test-int'));

        // The test path must be already created
        $testPath = Pi::path('upload/test');
        $originalImage = Pi::path('static/image/pi-ecosystem.png');
        $image = $testPath . '/source.png';
        Pi::service('image')->save($originalImage, $image);
        $child = Pi::path('static/image/module.png');
        $position = array(30, 100);
        $position = 'top-right';
        $position = '';
        $to = $testPath . '/test-watermark.jpg';
        Pi::service('image')->watermark($image, $to, '', $position);
        $to = $testPath . '/test-crop.jpg';
        Pi::service('image')->crop($image, array(30, 50), array(300, 200), $to);
        $to = $testPath . '/test-resize.jpg';
        Pi::service('image')->resize($image, array(500, 200), $to);
        $to = $testPath . '/test-resize-ratio.jpg';
        Pi::service('image')->thumbnail($image, 0.4, $to);
        $to = $testPath . '/resize-rotate.jpg';
        Pi::service('image')->rotate($image, 30, $to);
        $to = $testPath . '/test-paste.jpg';
        Pi::service('image')->paste($image, $child, array(50, 100), $to);
        $to = $testPath . '/test-thumbnail.jpg';
        Pi::service('image')->thumbnail($image, array(300, 100), $to);
        $to = $testPath . '/test-thumbnail-ratio.jpg';
        Pi::service('image')->thumbnail($image, 0.3, $to);

        $uids = Pi::user()->getUids();
        vd($uids);
        $avatars = Pi::user()->avatar->getList($uids);
        vd($avatars);
        $avatars = Pi::avatar()->getList($uids);
        vd($avatars);

        $whereA = array(
            'aa'    => 'AA',
            'ab'    => 'AB',
        );
        $whereB = array(
            'ba'    => 'BA',
            'bb'    => 'BB',
        );
        $whereC = array(
            'cc'    => 'CC',
            'cd'    => 'CD',
        );
        $whereD = array(
            'dc'    => 'DC',
            'dd'    => 'DD',
        );

        $whereX = Pi::db()->where();
        $whereX->add($whereA);
        $whereX->add($whereB, 'or');
        $whereY = Pi::db()->where($whereC);
        $whereY->add($whereX);
        $where = Pi::db()->where();
        $where->add($whereX, 'or')->add($whereY, 'or');
        vd($where->getExpressionData());

        Pi::user()->data()->set(1, 'test', 5);
        vd(Pi::user()->data(1, 'test'));
        Pi::user()->data()->set(1, 'test', 'test a');
        vd(Pi::user()->data(1, 'test'));
        Pi::user()->data()->increment(1, 'test', 5);
        vd(Pi::user()->data(1, 'test'));
        Pi::user()->data()->set(1, 'test', 'test b');
        vd(Pi::user()->data(1, 'test'));
        */
        //Pi::session()->setUser(1);
        //Pi::user()->killUser(1);
        //return $display;

        $url = '/system/user/logout';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());

        $url = '/system/user';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/home';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/123';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/home/123';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/home/123/something';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/home/name/test';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/home/identity/test';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());

        $url = '/system/user/profile';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/profile/123';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/profile/123/something';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/profile/name/test';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/profile/identity/test';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());
        $url = '/system/user/profile/misc/somea/someb';
        d($url);
        vd(Pi::service('url')->route($url, 'sysuser')->getParams());


        $params = array(
            'action'    => 'logout',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));

        $params = array(
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'home',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'uid'    => 123,
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'home',
            'uid'    => 123,
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'home',
            'name'    => 'test',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'home',
            'identity'    => 'test',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));

        $params = array(
            'controller'    => 'profile',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'profile',
            'action'    => 'index',
            'id'    => 123,
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'profile',
            'name'    => 'test',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'profile',
            'identity'    => 'test',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));
        $params = array(
            'controller'    => 'profile',
            'action'    => 'misc',
            'somea'     => 'sombe',
        );
        d($params);
        vd(Pi::service('url')->assemble('sysuser', $params));

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
            Pi::config('adminmail', 'mail') => Pi::config('adminname', 'mail'),
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
