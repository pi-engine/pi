<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup;

use Pi;

/**
 * Setup wizard
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Wizard
{
    const BASE_NAMESPACE = 'Pi\Setup';
    const DIR_CLASS = 'src';
    protected static $root;
    protected $request;
    protected $controller;
    protected $pageIndex = null;

    protected $persistentData = array();
    protected $locale = 'en';
    protected $charset = 'UTF-8';
    protected $pages = array();
    protected $configs = array();

    public $support = array(
        'url'   => 'http://pialog.org',
        'title' => 'Pi Engine',
    );

    public function __construct()
    {
        $docroot = $_SERVER['DOCUMENT_ROOT'];
        $root = str_replace('\\', '/', realpath($docroot));
        $pwd = str_replace('\\', '/', dirname(__DIR__));
        static::$root = str_replace($root, $docroot, $pwd);

        //static::$root = dirname(__DIR__);
        spl_autoload_register('static::autoload');
        $this->request = new Request();
    }

    public static function autoload($class)
    {
        if (static::BASE_NAMESPACE !==
            substr($class, 0, strlen(static::BASE_NAMESPACE))
        ) {
            return;
        }
        $class = substr($class, strlen(static::BASE_NAMESPACE) + 1);
        $classFile = static::$root . DIRECTORY_SEPARATOR . static::DIR_CLASS
                   . DIRECTORY_SEPARATOR
                   . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

        include $classFile;
    }

    public function init()
    {
        // Load persistent data
        $this->loadPersist();

        // Load the main language file
        $this->initLocale();

        // Setup pages
        $this->pages = include static::$root . '/include/page.php';

        // Load default configs
        $this->configs = include static::$root . '/include/config.php';

        if (!$this->checkAccess()) {
            return false;
        }

        return true;
    }

    public function getConfig($key = null)
    {
        if (null === $key) {
            return $this->configs;
        }
        if (isset($this->configs[$key])) {
            return $this->configs[$key];
        }

        return null;
    }

    public function getRequest()
    {
        return $this->request;
    }

    protected function checkAccess()
    {
        return true;
    }

    public function getRoot()
    {
        return static::$root;
    }

    public function initLocale($locale = null)
    {
        if (empty($locale)) {
            if (!empty($this->persistentData['locale'])) {
                $this->locale = $this->persistentData['locale'];
            }
        } else {
            $this->locale = $locale;
            $this->persistentData['locale'] = $this->locale;
        }
        $this->charset = !empty($this->persistentData['charset'])
            ? $this->persistentData['charset'] : $this->charset;
        Translator::setPath(static::$root . '/locale');
        Translator::setLocale($this->locale);
        Translator::loadDomain('setup');
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->persistentData['locale'] = $this->locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        $this->persistentData['charset'] = $this->charset;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    protected function getPage($page)
    {
        $page = (string) $page;
        $pageList = array_keys($this->pages);
        if (!isset($this->pages[$page])) {
            if (is_numeric($page)) {
                $pageIndex = (null === $this->pageIndex)
                    ? 0 : $this->pageIndex;
                if ($page{0} == '+' || $page{0} == '-') {
                    $pageIndex += intval($page);
                } else {
                    $pageIndex = intval($page);
                }
            } else {
                $pageIndex = 0;
            }
            $page = $pageList[$pageIndex];
        }

        return $page;
    }

    public function dispatch()
    {
        $page = $this->request->getParam('page', '');
        $page = $this->getPage($page);
        $this->pageIndex = array_search($page, array_keys($this->pages));

        $controllerClass = __NAMESPACE__ . '\\Controller\\' . ucfirst($page);
        $action = $this->request->getParam('action', '')
            ?: ($this->request->isPost() ? 'submit' : 'index');
        $action .= 'Action';
        $this->controller = new $controllerClass($this);
        $this->controller->$action();
    }

    public function render()
    {
        $this->savePersist();
        $status = $this->controller->getStatus();
        if ($status > 0 /*&& !$this->request->getParam('r')*/) {
            $this->gotoPage('+1');
        }
        $content = $this->controller->getContent();
        if ($this->request->isXmlHttpRequest()) {
            if ($this->controller->hasBootstrap()
                && Pi::service()->hasService('log')
            ) {
                Pi::service('log')->mute();
            } else {
                error_reporting(0);
            }
            echo $content;
            return;
        }

        $pages = $this->pages;
        $navPages = array();
        foreach ($pages as $key => &$page) {
            $page['url'] = $this->url($key);
            if (empty($page['hide'])) {
                $navPages[$key] = $page;
            }
        }
        $pageIndex = $this->pageIndex;
        $pageList = array_keys($pages);
        $locale = $this->locale;
        $charset = $this->charset;

        $currentPage = $pages[$pageList[$pageIndex]];
        $currentPage['key'] = $pageList[$pageIndex];

        $title = $currentPage['title'] . ' - ' . _s('Pi Engine Setup Wizard')
               . '(' . ($this->pageIndex + 1) . '/' . count($this->pages) . ')';
        $desc = $currentPage['desc'];

        if ($pageIndex > 0) {
            $previousUrl = $this->url('-1', array('r' => 1));
        }
        if ($status > -1 && $pageIndex < count($pages) - 1) {
            $nextUrl = $this->url('+1');
        }
        $pageHasForm = $this->controller->hasForm();
        $headContent = $this->controller->headContent();
        $footContent = $this->controller->footContent();
        $baseUrl = $this->request->getBaseUrl();

        $data = compact(
            'status', 'locale', 'charset', 'title', 'desc',
            'baseUrl', 'navPages', 'pageIndex', 'currentPage', 'previousUrl',
            'nextUrl', 'pageHasForm', 'content', 'headContent', 'footContent'
        );
        ob_start();
        include static::$root . '/include/template.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        // Prevent client caching
        header('Cache-Control: no-store, no-cache, must-revalidate', false);
        header('Pragma: no-cache');
        echo  $content;
    }

    public function url($page = '', $params = array())
    {
        $page = $this->getPage($page);
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $query = http_build_query($params);
        $url = $this->request->getBaseUrl() . ($query ? '?' . $query : '');

        return $url;
    }

    public function gotoPage($page = '', $params = array())
    {
        $url = $this->url($page, $params);
        header('Location: ' . $this->request->getScheme() . '://'
               . $this->request->getHttpHost() . $url);

        exit();
    }

    public function loadPersist()
    {
        session_start();

        $_SESSION[__CLASS__] = isset($_SESSION[__CLASS__])
            ? $_SESSION[__CLASS__] : array();
        $this->persistentData = $_SESSION[__CLASS__];
        //print_r($_SESSION);

        return;
    }

    public function savePersist()
    {
        $_SESSION[__CLASS__] = $this->persistentData;
        session_write_close();
        //print_r($_SESSION);

        return;
    }

    public function destroyPersist()
    {
        $this->persistentData = array();

        return true;
    }

    public function setPersist($key, $value)
    {
        $this->persistentData[$key] = $value;

        return $this;
    }

    public function getPersist($key)
    {
        return isset($this->persistentData[$key])
            ? $this->persistentData[$key] : null;
    }

    public function shutdown()
    {
        return;

        $this->destroyPersist();

        return true;
    }
}
