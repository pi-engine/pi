<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;
use Pi\Acl\Acl as AclManager;

/**
 * Navigation list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Navigation extends AbstractRegistry
{
    /** @var string Module name */
    protected $module;

    /** @var string Section */
    protected $section = 'front';

    /**
     * Columns for URI pages
     *
     * @var array
     */
    protected $uriColumns = array(
        'label',
        'fragment',
        'id',
        'class',
        'title',
        'target',
        'rel',
        'rev',
        'resource',
        'visible',
        'callback',
        'pages',

        'uri',
    );

    /**
     * Columns for MVC pages
     *
     * @var array
     */
    protected $mvcColumns = array(
        'label',
        'fragment',
        'id',
        'class',
        'title',
        'target',
        'rel',
        'rev',
        'resource',
        'visible',
        'callback',
        'pages',

        'route',
        'module',
        'controller',
        'action',
        'params',
    );

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $name = $options['name'];
        if ('front' == $name) {
            return $this->loadFront($options);
        }
        if ('admin' == $name) {
            return $this->loadAdmin($options);
        }

        if (empty($options['section'])) {
            $nav = Pi::model('navigation')->find($name, 'name');
            if (!$nav) {
                $this->section = 'front';
                //$this->route = 'default';
            } else {
                $this->section = $nav->section;
            }
        }

        return $this->loadNavigation($options);
    }

    /**
     * Load navigation data
     *
     * @param array $options
     * @return array
     */
    protected function loadNavigation($options = array())
    {
        $name = $options['name'];
        $locale = $options['locale'];
        //$module = $options['module'];
        $this->role = $options['role'];
        if (!empty($options['section'])) {
            $this->section = $options['section'];
        }

        $row = Pi::model('navigation_node')->find($name, 'navigation');
        if (!$row) {
            return false;
        }
        $this->module = $row->module;

        // Translate global admin navigation
        if ($this->module) {
            $domain = sprintf('module/%s:navigation', $this->module);
        } else {
            $domain = 'usr:navigation';
        }

        $navigation = $this->translateConfig($row->data, $domain, $locale);

        return $navigation;
    }

    /**
     * Load navigation of front section
     *
     * @param array $options
     * @return array
     */
    protected function loadFront($options = array())
    {
        $options['section'] = 'front';
        $options['name'] = 'system-front';

        $navigation = $this->loadNavigation($options);

        return $navigation;
    }

    /**
     * Load navigation of admin section
     *
     * NOTE: Only top level items are shown in a non-system module admin menu
     *
     * @param array $options
     * @return array
     */
    protected function loadAdmin($options = array())
    {
        $options['section'] = 'admin';
        //$this->route = 'admin';
        $options['name'] = 'system-admin';

        $navigation = $this->loadNavigation($options);

        return $navigation;
    }

    /**
     * {@inheritDoc}
     *
     * @param string        $name
     * @param string        $module
     * @param string        $section
     * @param string|null   $role
     * @param string        $locale
     */
    public function read(
        $name = '',
        $module = '',
        $section = '',
        $role = null,
        $locale = ''
    ) {
        //$this->cache = false;
        if (null === $role) {
            $role = Pi::service('user')->getUser()->role;
        }
        $options = compact('name', 'module', 'section', 'role', 'locale');
        $data = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @param string        $name
     * @param string        $module
     * @param string        $section
     * @param string|null   $role
     * @param string        $locale
     */
    public function create(
        $name = '',
        $module = '',
        $role = null,
        $locale = ''
    ) {
        $this->clear('');
        $this->read($name, $module, $role, $locale);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function clear($namespace = '')
    {
        parent::clear('');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }

    /**
     * Translate navigation config
     *
     * @param array     $config
     * @param string    $domain
     * @param string    $locale
     * @return array
     */
    protected function translateConfig($config, $domain, $locale)
    {
        if ($config) {
            Pi::service('i18n')->load($domain, $locale);
            foreach ($config as $p => &$page) {
                $this->translatePage($page, $config, $p, true);
            }
        }

        return $config;
    }

    /**
     * Canonize page data if callback is specified
     *
     * <ul>Note:
     *  <li>Declaration:
     *      'callback' must befined as a direct property of a page,
     *      as a direct method string, or an array of class and method
     *      <ul>
     *          <li>Direct callback
     *
     *              <code>
     *              $pages = array(
     *                  ...
     *                  'the-page'  => array(
     *                      'label' => 'The Page',
     *                      'callback'  =>
     *                          'Module\<ModuleName>\Navigation::thepage',
     *                  ),
     *                  ...
     *              );
     *              </code>
     *
     *          <li>Callback with full class and method
     *
     *              <code>
     *              $pages = array(
     *                  ...
     *                  'the-page'  => array(
     *                      'label' => 'The Page',
     *                      'callback'  => array(
     *                          'Module\<ModuleName>\Navigation',
     *                          'thepage'
     *                      ),
     *                  ),
     *                  ...
     *              );
     *              </code>
     *
     *          <li>Callback with direct class and method,
     *              the class will be transilated to the module
     *              in which the page spec is defined
     *
     *              <code>
     *              $pages = array(
     *                  ...
     *                  'the-page'  => array(
     *                      'label' => 'The Page',
     *                      'callback'  => array('Navigation', 'thepage'),
     *                  ),
     *                  ...
     *              );
     *              </code>
     *
     *      </ul>
     *  <li>Return: the return array shall be for the page itself/subpages,
     *      or for its parent
     *      <ul>
     *          <li>For the page
     *
     *              <code>
     *              $return = array(
     *                  ...
     *                  'uri'   => '/url/to/thepage/',
     *                  'pages' => array(
     *                      'p1' => array(...),
     *                      'p2' => array(...),
     *                      ...
     *                  ),
     *              );
     *              </code>
     *
     *          <li>For the page's parent with replacement:
     *              the page will be replaced with p1, p2, etc. in its parent
     *
     *              <code>
     *              $return = array(
     *                  'parent' => array(
     *                      'p1' => array(...),
     *                      'p2' => array(...),
     *                      ...
     *                  ),
     *              );
     *              </code>
     *
     *          <li>For the page's parent with insertion: pages p1, p2, etc.
     *              specified in 'pages' will be inserted into
     *              its parent before/after the page specified in 'position'
     *
     *              <code>
     *              $return = array(
     *                  'parent' => array(
     *                      'position'  => 'after',
     *                      'pages'     => array(
     *                          'p1' => array(...),
     *                          'p2' => array(...),
     *                          ...
     *                      ),
     *                  ),
     *              );
     *              </code>
     *
     *          <li>For the page itself and its parent with insertion:
     *              the pages  p1, p2, etc.
     *              specified in 'pages' will be inserted into
     *              its parent before/after the page specified in 'position'
     *
     *              <code>
     *              $return = array(
     *                  ...
     *                  'label' => 'Custom Label',
     *                  'parent' => array(
     *                      'position'  => 'after',
     *                      'pages'     => array(
     *                          'p1' => array(...),
     *                          'p2' => array(...),
     *                          ...
     *                      ),
     *                  ),
     *              );
     *              </code>
     *
     *      </ul>
     * </ul>
     *
     * @see     \Module\System\Navigation for details
     * @param array     $page   Page data to be canonized
     * @param array     $parent Sibling of parent page
     * @param string    $pKey   Key of parent in sibling
     * @return array
     */
    protected function canonizeCallback($page, &$parent, $pKey)
    {
        if (empty($page['callback'])) {
            return $page;
        }

        $data = array();
        $callback = null;
        if (is_string($page['callback']) && is_callable($page['callback'])) {
            $callback = $page['callback'];
        } elseif (is_array($page['callback'])) {
            list($class, $method) = $page['callback'];

            if (!class_exists($class)) {
                $module = empty($page['module'])
                    ? $this->module : $page['module'];
                $class = sprintf(
                    'Module\\%s\\%s',
                    ucfirst(Pi::service('module')->directory($module)),
                    ucfirst($class)
                );
            }

            if (method_exists($class, $method)) {
                $callback = array($class, $method);
            }
        }
        unset($page['callback']);

        if ($callback) {
            $data = (array) call_user_func($callback, $this->module);
            $parentNode = null;
            if (isset($data['parent'])) {
                $parentNode = $data['parent'];
                unset($data['parent']);
            }
            if ($data) {
                $pages = isset($page['pages']) ? $page['pages'] : array();
                if (!empty($data['pages'])) {
                    $pages = array_merge($pages, $data['pages']);
                }
                $page = array_merge($page, (array) $data);
                if ($pages) {
                    $page['pages'] = $pages;
                }
            }
            if ($parentNode) {
                $newParent = array();
                foreach ($parent as $key => $node) {
                    // Insert new parent nodes
                    if ($pKey == $key) {
                        if (!empty($parentNode['position'])) {
                            $position = $parentNode['position'];
                            $parentItems = $parentNode['pages'];
                            unset($node['callback']);
                        } else {
                            $position = false;
                            $parentItems = $parentNode;
                        }
                        // Insert parent items after current node
                        if ($position == 'after') {
                            $newParent[$key] = $node;
                        }
                        foreach ($parentItems as $var => $val) {
                            $newParent[$var] = $val;
                        }
                        // Insert parent items before current node
                        if ($position == 'before') {
                            $newParent[$key] = $node;
                        }
                        // Otherwise, just overwrite current node
                    } else {
                        $newParent[$key] = $node;
                    }
                }
                $parent = $newParent;
           }
        }

        return $page;
    }

    /**
     * Canonize a page
     *
     * @param array     $page   Page data to be canonized
     * @param array     $parent Sibling of parent page
     * @param string    $pKey   Key of parent in sibling
     * @param bool      $isTop  If the page is top level,
     *      only top level menu is shown in non-system module admin
     * @return array
     */
    protected function canonizePage($page, &$parent, $pKey, $isTop = false)
    {
        $page = $this->canonizeCallback($page, $parent, $pKey);

        // @see: Zend\Navigation\Page\AbstractPage for identifying MVC pages
        $isMvc = !empty($page['action'])
            || !empty($page['controller'])
            || !empty($page['route']);
        if ($isMvc) {
            $validColumns = $this->mvcColumns;
        } else {
            $validColumns = $this->uriColumns;
        }
        // Clean up
        foreach (array_keys($page) as $key) {
            if (!in_array($key, $validColumns)) {
                unset($page[$key]);
            }
        }
        // Only top level menu is shown in a non-system module back office
        if ('admin' == $this->section
            && 'system' != $this->module
            && !$isTop
        ) {
            $page['visible'] = 0;
        }

        return $page;
    }

    /**
     * Translate a page
     *
     * @param array     $page   Page data to be canonized
     * @param array     $parent Sibling of parent page
     * @param string    $pKey   Key of parent in sibling
     * @param bool      $isTop  If the page is top level,
     *      only top level menu is shown in non-system module admin
     * @return array
     */
    protected function translatePage(&$page, &$parent, $pKey, $isTop = false)
    {
        $page = $this->canonizePage($page, $parent, $pKey, $isTop);

        // Check permission
        if (!$this->isAllowed($page)) {
            $page['visible'] = 0;
            $page['pages'] = array();
            $page['resource'] = null;
            return;
        }
        $page['resource'] = null;

        if (!empty($page['label'])) {
            $page['label'] = __($page['label']);
        }
        // translate title
        if (!empty($page['title'])) {
            $page['title'] = __($page['title']);
        }

        if (!empty($page['pages'])) {
            $pages = $page['pages'];
            foreach ($pages as $p => &$data) {
                $this->translatePage($data, $pages, $p);
            }
            $page['pages'] = $pages;
        }
    }

    /**
     * Check if a page is accessible
     *
     * @param array $page
     * @return bool
     */
    public function isAllowed($page)
    {
        if (!empty($page['resource'])) {
            return $this->isAllowedResource($page['resource']);
        }

        return true;
    }

    /**
     * Check if a resource is allowed
     *
     * @param array $params
     * @return bool
     */
    protected function isAllowedResource($params)
    {
        $module = null;
        $section = empty($params['section'])
            ? $this->section : $params['section'];
        $resource = $params['resource'];
        if (!empty($params['item'])) {
            $resource = array($resource, $params['item']);
        }
        $privilege = empty($params['privilege']) ? null : $params['privilege'];
        $module = empty($params['module']) ? $this->module : $params['module'];

        $acl = new AclManager($section);
        $acl->setModule($module);
        $result = $acl->checkAccess($resource, $privilege);

        return $result;
    }
}
