<?php
/**
 * Paginator
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
 * @package         Pi\Paginator
 * @version         $Id$
 */

namespace Pi\Paginator;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;
//use Zend\Cache\Storage\IteratorInterface as CacheIterator;
//use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Db\Sql;
use Zend\Db\Table\AbstractRowset as DbAbstractRowset;
use Zend\Db\Table\Select as DbTableSelect;
use Zend\Filter\FilterInterface;
use Zend\Json\Json;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View;
use Zend\Paginator\Exception;

// Solely for other API calls, shit!!!
use Zend\Paginator\Paginator as Pagit;

class Paginator extends Pagit //*/implements Countable, IteratorAggregate
{
    /**
     * Specifies that the factory should try to detect the proper adapter type first
     *
     * @var string
     */
    //const INTERNAL_ADAPTER = 'Zend\Paginator\Adapter\Internal';

    /**
     * The cache tag prefix used to namespace Paginator results in the cache
     *
     */
    //const CACHE_TAG_PREFIX = 'Zend_Paginator_';

    /**
     * Adapter plugin manager
     *
     * @var AdapterPluginManager
     */
    //protected static $adapters = null;

    /**
     * Configuration file
     *
     * @var array|null
     */
    protected static $config = null;

    /**
     * Default scrolling style
     *
     * @var string
     */
    protected static $defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     *
     * @var int
     */
    protected static $defaultItemCountPerPage = 10;

    /**
     * Scrolling style plugin manager
     *
     * @var ScrollingStylePluginManager
     */
    protected static $scrollingStyles = null;

    /**
     * Cache object
     *
     * @var CacheStorage
     */
    //protected static $cache;

    /**
     * Enable or disable the cache by Zend\Paginator\Paginator instance
     *
     * @var bool
     */
    //protected $cacheEnabled = true;

    /**
     * Adapter
     *
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * Number of items in the current page
     *
     * @var integer
     */
    protected $currentItemCount = null;

    /**
     * Current page items
     *
     * @var Traversable
     */
    protected $currentItems = null;

    /**
     * Current page number (starting from 1)
     *
     * @var integer
     */
    protected $currentPageNumber = 1;

    /**
     * Result filter
     *
     * @var FilterInterface
     */
    protected $filter = null;

    /**
     * Number of items per page
     *
     * @var integer
     */
    protected $itemCountPerPage = null;

    /**
     * Number of pages
     *
     * @var integer
     */
    protected $pageCount = null;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     *
     * @var integer
     */
    protected $pageRange = 10;

    /**
     * Pages
     *
     * @var array
     */
    protected $pages = null;

    /**
     * View instance used for self rendering
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $view = null;

    /**
     * Options for URL assemble
     *                  template - string
     *                  pageParam - string
     *                  totalParam - string
     *                  params - array
     *                  router - object
     *                  route - string
     *
     * @var array
     */
    protected $urlOptions = array(
        'pageParam' => 'p',
        'route'     => 'default',
    );

    /**
     * Factory.
     *
     * @param  mixed  $data
     * @throws Exception\InvalidArgumentException
     * @return Paginator
     */
    public static function factory($data)
    {
        if ($data instanceof AdapterAggregateInterface) {
            return new self($data->getPaginatorAdapter());
        }

        if (is_array($data)) {
            $adapter = 'arrayAdapter';
        } elseif ($data instanceof DbTableSelect) {
            $adapter = 'dbTableSelect';
        } elseif ($data instanceof DbSelect) {
            $adapter = 'dbSelect';
        } elseif ($data instanceof Iterator) {
            $adapter = 'iterator';
        } elseif (is_integer($data)) {
            $adapter = 'null';
        } else {
            $type = (is_object($data)) ? get_class($data) : gettype($data);
            throw new Exception\InvalidArgumentException('No adapter for type ' . $type);
        }

        $adapter = static::createAdapter($adapter, $data);
        return new self($adapter);
    }

    /**
     * Set a global config
     *
     * @param array|\Traversable $config
     * @throws Exception\InvalidArgumentException
     */
    public static function setGlobalConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        static::$config = $config;

        if (isset($config['scrolling_style_plugins'])
            && null !== ($adapters = $config['scrolling_style_plugins'])
        ) {
            static::setScrollingStylePluginManager($adapters);
        }

        $scrollingStyle = isset($config['scrolling_style']) ? $config['scrolling_style'] : null;

        if ($scrollingStyle != null) {
            static::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Creates the adapter
     *
     * @param string $adapter
     * @param mixed $data
     * @return AdapterInterface
     */
    public static function createAdapter($adapter, $data)
    {
        $adapterClass = '%s\\Paginator\\Adapter\\' . ucfirst($adapter);
        $class = sprintf($adapterClass, 'Pi');
        if (!class_exists($class)) {
            $class = sprintf($adapterClass, 'Zend');
        }
        return new $class($data);
    }

    /**
     * Creates scrolling style
     *
     * @param string $style
     * @return ScrollingStyleInterface
     */
    protected function createScrollingStyle($style)
    {
        $styleClass = '%s\\Paginator\\ScrollingStyle\\' . ucfirst($style);
        $class = sprintf($styleClass, 'Pi');
        if (!class_exists($class)) {
            $class = sprintf($styleClass, 'Zend');
        }
        return new $class;
    }

    /**
     * Set a global config
     *
     * @param array|\Traversable $config
     * @throws Exception\InvalidArgumentException
     */
    public static function setOptions($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        static::$config = $config;

        $scrollingStyle = isset($config['scrolling_style']) ? $config['scrolling_style'] : null;

        if ($scrollingStyle != null) {
            static::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Returns the default scrolling style.
     *
     * @return  string
     */
    public static function getDefaultScrollingStyle()
    {
        return static::$defaultScrollingStyle;
    }

    /**
     * Get the default item count per page
     *
     * @return int
     */
    public static function getDefaultItemCountPerPage()
    {
        return static::$defaultItemCountPerPage;
    }

    /**
     * Set the default item count per page
     *
     * @param int $count
     */
    public static function setDefaultItemCountPerPage($count)
    {
        static::$defaultItemCountPerPage = (int) $count;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param  string $scrollingStyle
     */
    public static function setDefaultScrollingStyle($scrollingStyle = 'Sliding')
    {
        static::$defaultScrollingStyle = $scrollingStyle;
    }

    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof AdapterInterface) {
            $this->adapter = $adapter;
        } elseif ($adapter instanceof AdapterAggregateInterface) {
            $this->adapter = $adapter->getPaginatorAdapter();
        } else {
            throw new Exception\InvalidArgumentException(
                'Zend_Paginator only accepts instances of the type ' .
                'Zend\Paginator\Adapter\AdapterInterface or Zend\Paginator\AdapterAggregateInterface.'
            );
        }

        $config = static::$config;

        if (!empty($config)) {
            $setupMethods = array('ItemCountPerPage', 'PageRange');

            foreach ($setupMethods as $setupMethod) {
                $key   = strtolower($setupMethod);
                $value = isset($config[$key]) ? $config[$key] : null;

                if ($value != null) {
                    $setupMethod = 'set' . $setupMethod;
                    $this->$setupMethod($value);
                }
            }
        }
    }

    /**
     * Serializes the object as a string.  Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }

    /**
     * Returns the number of pages.
     *
     * @return integer
     */
    public function count()
    {
        if (!$this->pageCount) {
            $this->pageCount = $this->_calculatePageCount();
        }

        return $this->pageCount;
    }

    /**
     * Returns the total number of items available.
     *
     * @return integer
     */
    public function getTotalItemCount()
    {
        return count($this->getAdapter());
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  integer $relativeItemNumber Relative item number
     * @param  integer $pageNumber Page number
     * @return integer
     */
    public function getAbsoluteItemNumber($relativeItemNumber, $pageNumber = null)
    {
        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);

        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        }

        $pageNumber = $this->normalizePageNumber($pageNumber);

        return (($pageNumber - 1) * $this->getItemCountPerPage()) + $relativeItemNumber;
    }

    /**
     * Returns the adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Returns the number of items for the current page.
     *
     * @return integer
     */
    public function getCurrentItemCount()
    {
        if ($this->currentItemCount === null) {
            $this->currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->currentItemCount;
    }

    /**
     * Returns the items for the current page.
     *
     * @return Traversable
     */
    public function getCurrentItems()
    {
        if ($this->currentItems === null) {
            $this->currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return integer
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param  integer $pageNumber Page number
     * @return Paginator $this
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->currentPageNumber = (integer) $pageNumber;
        $this->currentItems      = null;
        $this->currentItemCount  = null;

        return $this;
    }

    /**
     * Get the filter
     *
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set a filter chain
     *
     * @param  FilterInterface $filter
     * @return Paginator
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page specified.
     *
     * @param  integer $itemNumber Item number (1 to itemCountPerPage)
     * @param  integer $pageNumber
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null)
    {
        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        } elseif ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount == 0) {
            throw new Exception\InvalidArgumentException('Page ' . $pageNumber . ' does not exist');
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new Exception\InvalidArgumentException('Page ' . $pageNumber . ' does not'
                                             . ' contain item number ' . $itemNumber);
        }

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return integer
     */
    public function getItemCountPerPage()
    {
        if (empty($this->itemCountPerPage)) {
            $this->itemCountPerPage = static::getDefaultItemCountPerPage();
        }

        return $this->itemCountPerPage;
    }

    /**
     * Sets the number of items per page.
     *
     * @param  integer $itemCountPerPage
     * @return Paginator $this
     */
    public function setItemCountPerPage($itemCountPerPage = -1)
    {
        $this->itemCountPerPage = (integer) $itemCountPerPage;
        if ($this->itemCountPerPage < 1) {
            $this->itemCountPerPage = $this->getTotalItemCount();
        }
        $this->pageCount        = $this->_calculatePageCount();
        $this->currentItems     = null;
        $this->currentItemCount = null;

        return $this;
    }

    /**
     * Returns the number of items in a collection.
     *
     * @param  mixed $items Items
     * @return integer
     */
    public function getItemCount($items)
    {
        $itemCount = 0;

        if (is_array($items) || $items instanceof Countable) {
            $itemCount = count($items);
        } elseif ($items instanceof Traversable) { // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @param integer $pageNumber
     * @return mixed
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->adapter->getItems($offset, $this->getItemCountPerPage());

        $filter = $this->getFilter();

        if ($filter !== null) {
            $items = $filter->filter($items);
        }

        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        return $items;
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @throws Exception\RuntimeException
     * @return Traversable
     */
    public function getIterator()
    {
        try {
            return $this->getCurrentItems();
        } catch (\Exception $e) {
            throw new Exception\RuntimeException('Error producing an iterator', null, $e);
        }
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return integer
     */
    public function getPageRange()
    {
        return $this->pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  integer $pageRange
     * @return Paginator $this
     */
    public function setPageRange($pageRange)
    {
        $this->pageRange = (integer) $pageRange;

        return $this;
    }

    /**
     * Returns the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return array
     */
    public function getPages($scrollingStyle = null)
    {
        if ($this->pages === null) {
            $this->pages = $this->_createPages($scrollingStyle);
        }

        return $this->pages;
    }

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  integer $lowerBound Lower bound of the range
     * @param  integer $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = array();

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = (object) array(
                'number'    => $pageNumber,
                'url'       => $this->buildUrl($pageNumber),
            );
        }

        return $pages;
    }

    /**
     * Retrieves the view instance.
     *
     * If none registered, instantiates a PhpRenderer instance.
     *
     * @return \Zend\View\Renderer\RendererInterface|null
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->setView(new View\Renderer\PhpRenderer());
        }

        return $this->view;
    }

    /**
     * Sets the view object.
     *
     * @param  \Zend\View\Renderer\RendererInterface $view
     * @return Paginator
     */
    public function setView(View\Renderer\RendererInterface $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Brings the item number in range of the page.
     *
     * @param  integer $itemNumber
     * @return integer
     */
    public function normalizeItemNumber($itemNumber)
    {
        $itemNumber = (integer) $itemNumber;

        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @param  integer $pageNumber
     * @return integer
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (integer) $pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Renders the paginator.
     *
     * @param  \Zend\View\Renderer\RendererInterface $view
     * @return string
     */
    public function render(View\Renderer\RendererInterface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $view = $this->getView();

        return $view->paginationControl($this);
    }

    /**
     * Returns the items of the current page as JSON.
     *
     * @return string
     */
    public function toJson()
    {
        $currentItems = $this->getCurrentItems();

        if ($currentItems instanceof DbAbstractRowset) {
            return Json::encode($currentItems->toArray());
        } else {
            return Json::encode($currentItems);
        }
    }

    /**
     * Calculates the page count.
     *
     * @return integer
     */
    protected function _calculatePageCount()
    {
        return (integer) ceil($this->getAdapter()->count() / $this->getItemCountPerPage());
    }

    /**
     * Creates the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return stdClass
     */
    protected function _createPages($scrollingStyle = null)
    {
        $pageCount         = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        $pages = new \stdClass();
        $pages->pageCount        = $pageCount;
        $pages->itemCountPerPage = $this->getItemCountPerPage();
        $pages->current          = $currentPageNumber;
        $pages->first            = (object) array(
            'number'    => 1,
            'url'       => $this->buildUrl(1),
        );
        $pages->last            = (object) array(
            'number'    => $pageCount,
            'url'       => $this->buildUrl($pageCount),
        );

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = (object) array(
                'number'    => $currentPageNumber - 1,
                'url'       => $this->buildUrl($currentPageNumber - 1),
            );
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = (object) array(
                'number'    => $currentPageNumber + 1,
                'url'       => $this->buildUrl($currentPageNumber + 1),
            );
        }

        // Pages in range
        $scrollingStyle = $this->_loadScrollingStyle($scrollingStyle);
        $pages->pagesInRange     = $scrollingStyle->getPages($this);
        $pages->firstPageInRange = min($pages->pagesInRange);
        $pages->lastPageInRange  = max($pages->pagesInRange);

        // Item numbers
        if ($this->getCurrentItems() !== null) {
            $pages->currentItemCount = $this->getCurrentItemCount();
            $pages->itemCountPerPage = $this->getItemCountPerPage();
            $pages->totalItemCount   = $this->getTotalItemCount();
            $pages->firstItemNumber  = (($currentPageNumber - 1) * $this->getItemCountPerPage()) + 1;
            $pages->lastItemNumber   = $pages->firstItemNumber + $pages->currentItemCount - 1;
        }

        return $pages;
    }

    /**
     * Loads a scrolling style.
     *
     * @param string $scrollingStyle
     * @return ScrollingStyleInterface
     * @throws Exception\InvalidArgumentException
     */
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        if ($scrollingStyle === null) {
            $scrollingStyle = static::$defaultScrollingStyle;
        }

        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (!$scrollingStyle instanceof ScrollingStyleInterface) {
                    throw new Exception\InvalidArgumentException(
                        'Scrolling style must implement Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'string':
                return $this->createScrollingStyle($scrollingStyle);

            case 'null':
                // Fall through to default case

            default:
                throw new Exception\InvalidArgumentException(
                    'Scrolling style must be a class ' .
                    'name or object implementing Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                );
        }
    }

    /**
     * Set up options for URL assemble
     *
     * @param array $options
     *                  template - string
     *                  pageParam - string
     *                  totalParam - string
     *                  params - array
     *                  router - object
     *                  route - string
     *
     * @return Paginator
     */
    public function setUrlOptions($options)
    {
        $this->urlOptions = array_merge($this->urlOptions, $options);

        return $this;
    }

    /**
     * Builds URL for a page
     *
     * @param int $page
     * @return string
     */
    public function buildUrl($page)
    {
        if (!empty($this->urlOptions['template'])) {
            $url = str_replace(array('%page%', '%total%'), array($page, $this->count()), $this->urlOptions['template']);
            return $url;
        }

        $params = isset($this->urlOptions['params']) ? $this->urlOptions['params'] : array();
        $params[$this->urlOptions['pageParam']] = $page;
        if (!empty($this->urlOptions['totalParam'])) {
            $params[$this->urlOptions['totalParam']] = $this->count();
        }

        $router = isset($this->urlOptions['router']) ? $this->urlOptions['router'] : (isset(static::$config['router']) ? static::$config['router'] : null);
        $route = isset($this->urlOptions['route']) ? $this->urlOptions['route'] : (isset(static::$config['route']) ? static::$config['route'] : null);

        if (!$router || !$route) {
            return false;
        }

        $url = $router->assemble($params, array('name' => $route));

        return $url;
    }
}
