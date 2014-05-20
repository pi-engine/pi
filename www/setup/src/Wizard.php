<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup;

use Pi;
use Locale;

/**
 * Setup wizard
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Wizard
{
    const BASE_NAMESPACE    = 'Pi\Setup';
    const DIR_CLASS         = 'src';
    const PERSIST_LOCALE    = 'locale';
    const PERSIST_CHARSET   = 'charset';

    protected static $root;

    protected $request;
    protected $controller;
    protected $pageIndex;

    protected $persist;
    protected $locale       = '';
    protected $charset      = 'UTF-8';
    protected $pages        = array();
    protected $configs      = array();
    protected $languages    = array();
    protected $tmpDir       = '';

    public $support = array(
        'url'   => 'http://pialog.org',
        'title' => 'Pi Engine',
    );

    public function __construct($tmpDir = '')
    {
        $pwd = dirname($_SERVER["SCRIPT_FILENAME"]);
        static::$root = str_replace('\\', '/', $pwd);
        spl_autoload_register('static::autoload');
        $this->tmpDir = $tmpDir;
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
        try {
            $this->loadPersist();
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        // Load the main language file
        $this->initLocale();

        // Setup pages
        $this->pages = include $this->getRoot() . '/include/page.php';

        // Load default configs
        $this->configs = include $this->getRoot() . '/include/config.php';

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
        return $this->request ?: new Request;
    }

    protected function checkAccess()
    {
        return true;
    }

    public function getRoot()
    {
        return static::$root;
    }

    public function initLocale()
    {
        // Load from persist
        $locale = $this->getPersist(static::PERSIST_LOCALE);
        if ($locale) {
            $this->locale = $locale;
        // Detect via browser
        } elseif (!$this->locale) {
            $auto   = 'en';
            $acceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
                ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
            $matched = preg_match_all(
                '/([a-z]{2,8}(-[a-z]{2,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $acceptedLanguage,
                $matches
            );
            if ($matched) {
                $languageList = $this->getLanguages();
                foreach ($matches[1] as $language) {
                    $canonized = strtolower($language);
                    if (isset($languageList[$canonized])) {
                        $auto = $canonized;
                        break;
                    } else {
                        $pos = strpos($language, '-');
                        if (false !== $pos) {
                            $canonized = substr($language, 0, $pos);
                            if (isset($languageList[$canonized])) {
                                $auto = $canonized;
                                break;
                            }
                        }
                    }
                }
            }
            $this->setLocale($auto);
        }
        $charset = $this->getPersist(static::PERSIST_CHARSET);
        if ($charset) {
            $this->charset = $charset;
        }
        Translator::setPath($this->getRoot() . '/locale');
        Translator::setLocale($this->locale);
        Translator::loadDomain('default');
    }

    public function setLocale($locale)
    {
        $languages = $this->getLanguages();
        if (isset($languages[$locale])) {
            $this->locale = $locale;
            $this->setPersist(static::PERSIST_LOCALE, $this->locale);

            return true;
        }

        return false;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getLanguages()
    {
        if (!$this->languages) {
            $languageList = array();

            $lookupIcon = function ($name) {
                $icon       = '';
                $root       = $this->getRoot();
                $pathLocale = sprintf('%s/locale/%s', $root, $name);
                $pathIcon   = sprintf('%s/asset/image/country', $root);
                $iconFile   = $pathLocale  . '/icon.gif';
                if (is_readable($iconFile)) {
                    $icon = $iconFile;
                } else {
                    $iconFile = $pathLocale  . '/icon.png';
                    if (is_readable($iconFile)) {
                        $icon = $iconFile;
                    } else {
                        $configFile = $pathLocale . '/config.ini';
                        if (is_readable($configFile)) {
                            $config = parse_ini_file($configFile);
                            if (!empty($config['icon'])) {
                                $iconFile = $pathIcon . '/' . $config['icon'];
                                if (is_readable($iconFile)) {
                                    $icon = $iconFile;
                                }
                            }
                        }
                        if (!$icon) {
                            $icon = $pathIcon . '/blank.png';
                        }
                    }
                }
                if ($icon) {
                    // Get root URI
                    $request = $this->getRequest();
                    $baseUrl = '//' . $request->getHttpHost() . $request->getBaseUrl();

                    // Assemble icon URI
                    $icon = rtrim($baseUrl, '/') . '/' . substr($icon, strlen($root) + 1);
                }

                return $icon;
            };

            $iterator = new \DirectoryIterator(
                $this->getRoot() . '/locale/'
            );
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                $name = $fileinfo->getFilename();
                if ($name[0] == '.') {
                    continue;
                }
                $title = $name;
                if (class_exists('\Locale')) {
                    $title = Locale::getDisplayName($name) ?: $title;
                }
                $iconFile = $lookupIcon($name);
                $languageList[$name] = array(
                    'title' => $title,
                    'icon'  => $iconFile
                );
            }
            asort($languageList);
            $this->languages = $languageList;
        }

        return $this->languages;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        $this->setPersist(static::PERSIST_CHARSET, $this->charset);
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
        $page = $this->getRequest()->getParam('page', '');
        $page = $this->getPage($page);
        $this->pageIndex = array_search($page, array_keys($this->pages));

        $controllerClass = __NAMESPACE__ . '\\Controller\\' . ucfirst($page);
        $action = $this->getRequest()->getParam('action', '')
            ?: ($this->getRequest()->isPost() ? 'submit' : 'index');
        $action .= 'Action';
        $this->controller = new $controllerClass($this);
        $this->controller->$action();
    }

    public function render()
    {
        $status = $this->controller->getStatus();
        if ($status > 0 /*&& !$this->getRequest()->getParam('r')*/) {
            $this->gotoPage('+1');
            exit;
        }

        // Prevent client caching
        header('Cache-Control: no-store, no-cache, must-revalidate', false);
        header('Pragma: no-cache');

        $content = $this->controller->getContent();
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->controller->hasBootstrap()
                && Pi::service()->hasService('log')
            ) {
                Pi::service('log')->mute();
            } else {
                error_reporting(0);
            }
            echo $content;
            exit;
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

        $previousUrl = $nextUrl = '';
        // For non-first page
        if ($pageIndex > 0) {
            $previousUrl = $this->url('-1', array('r' => 1));
        }
        // For non-last page
        if ($status > -1 && $pageIndex < count($pages) - 1) {
            $nextUrl = $this->url('+1');
        }

        // For finish page explicitly
        if ($pageIndex == count($pages) - 1) {
            $currentPage['url'] = $previousUrl = $nextUrl = '';
        }

        $pageHasForm = $this->controller->hasForm();
        $headContent = $this->controller->headContent();
        $footContent = $this->controller->footContent();
        $baseUrl = $this->getRequest()->getBaseUrl();

        $data = compact(
            'status', 'locale', 'charset', 'title', 'desc',
            'baseUrl', 'navPages', 'pageIndex', 'currentPage', 'previousUrl',
            'nextUrl', 'pageHasForm', 'content', 'headContent', 'footContent'
        );

        include $this->getRoot() . '/include/template.phtml';

        exit;
    }

    public function url($page = '', $params = array())
    {
        $page = $this->getPage($page);
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $query = http_build_query($params);
        $url = $this->getRequest()->getBaseUrl() . ($query ? '?' . $query : '');

        return $url;
    }

    public function gotoPage($page = '', $params = array())
    {
        $url = $this->url($page, $params);

        header('Location: ' . $this->getRequest()->getScheme() . '://'
               . $this->getRequest()->getHttpHost() . $url);

        exit();
    }

    protected function persist()
    {
        if (!$this->persist instanceof Persist) {
            if ($this->tmpDir) {
                $this->persist = new Persist('file', $this->getRoot() . '/' . $this->tmpDir);
            } else {
                $this->persist = new Persist;
            }
        }

        return $this->persist;
    }

    protected function loadPersist()
    {
        $this->persist()->load();

        return;
    }

    public function savePersist()
    {
        $this->persist->save();

        return;
    }

    public function destroyPersist()
    {
        try {
            $this->persist->destroy();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->persist()->load();

        return true;
    }

    public function setPersist($key, $value = null)
    {
        $this->persist->set($key, $value);
        $this->savePersist();

        return $this;
    }

    public function getPersist($key = null)
    {
        return $this->persist->get($key);
    }

    public function shutdown()
    {
        return;
    }
}
