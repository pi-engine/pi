<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    /** Persistent data groups */
    const PERSIST_ENGINE    = 'engine';
    const PERSIST_HOST      = 'host';
    const PERSIST_DB        = 'db';
    const PERSIST_SITE      = 'site';

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

            $vars = $wizard->getPersist(static::PERSIST_HOST);

            define('PI_PATH_HOST', $vars['config']['path'] . '/host.php');
            define('PI_PATH_WWW', $vars['www']['path']);
            define('PI_PATH_LIB', $vars['lib']['path']);

            $pi = PI_PATH_LIB . '/Pi.php';
            if (is_readable($pi)) {
                include $pi;

                $locale = $this->wizard->getLocale();
                $charset = $this->wizard->getCharset();
                Pi::config()->set('locale', $locale);
                Pi::config()->set('charset', $charset);

                Pi::service('i18n')->setLocale($locale);
                setlocale(\LC_ALL, $locale);
            } else {
                $this->wizard->gotoPage();
            }
        }

        $this->request = $wizard->getRequest();
        $this->init();
    }

    protected function init()
    {
        return;
    }

    public function setPersist($key, $value)
    {
        $this->wizard->setPersist($key, $value);

        return $this;
    }

    public function getPersist($key)
    {
        return $this->wizard->getPersist($key);
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

    /*
    public function hasHelp()
    {
        return $this->hasHelp ? true : false;
    }
    */

    public function hasForm()
    {
        return $this->hasForm ? true : false;
    }

    public function hasBootstrap()
    {
        return $this->hasBootstrap ? true : false;
    }

    /*
    public function hasAjax()
    {
        return $this->hasAjax ? true : false;
    }
    */

    public function getStatus()
    {
        return $this->status;
    }

    public function indexAction() {}
}
