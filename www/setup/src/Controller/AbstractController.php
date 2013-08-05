<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup\Controller;

use Pi;
use Pi\Setup\Wizard;

/**
 * Abstract controller class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractController
{
    protected $content = '';
    protected $headContent = '';
    protected $footContent = '';
    protected $wizard;
    protected $request;
    protected $page;
    protected $hasBootstrap = false;
    protected $hasForm = false;
    protected $status = 0; // 1 - proceed; -1 - pending; 0 - regular

    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;
        if ($this->hasBootstrap) {

            $vars = $wizard->getPersist('paths');

            // Physical path to host configuration file
            // For performance consideration it is recommended to be specified
            // if there is only one host; Otherwise it will be automatically
            // looked up in central host specifications
            define('PI_PATH_HOST', $vars['config']['path'] . '/host.php');

            // Physical path to www directory WITHOUT trailing slash
            define('PI_PATH_WWW', $vars['www']['path']);

            // Physical path to default lib directory WITHOUT trailing slash
            define('PI_PATH_LIB', $vars['lib']['path']);
            /**#@-*/

            include PI_PATH_LIB . '/Pi.php';


            $locale = $this->wizard->getLocale();
            $charset = $this->wizard->getCharset();
            Pi::config()->set('locale', $locale);
            Pi::config()->set('charset', $charset);


            Pi::service('i18n')->setLocale($locale);
            \setlocale(\LC_ALL, $locale);

        }
        $this->request = $wizard->getRequest();
        $this->init();
    }

    protected function init()
    {
        return;
    }

    public function headContent()
    {
        return $this->headContent;
    }

    public function footContent()
    {
        return $this->footContent;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function hasHelp()
    {
        return $this->hasHelp ? true : false;
    }

    public function hasForm()
    {
        return $this->hasForm ? true : false;
    }

    public function hasBootstrap()
    {
        return $this->hasBootstrap ? true : false;
    }

    public function hasAjax()
    {
        return $this->hasAjax ? true : false;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function indexAction() {}
}
