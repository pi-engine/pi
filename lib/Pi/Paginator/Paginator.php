<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Paginator;

use Pi;
use ArrayIterator;
use Countable;
use Iterator;
use Traversable;
use Zend\Db\Sql;
use Zend\Db\ResultSet\AbstractResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select as DbSelect;
use Zend\Filter\FilterInterface;
use Zend\Json\Json;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View;
use Zend\Paginator\Exception;
use Zend\Paginator\Paginator as Pagit; // Solely for other API calls, shit!!!

/**
 * Paginator handler
 *
 * - Create paginator with factory
 *
 * ```
 *  $paginator = Paginator::factory(5, array(
 *      'item_count_per_page'   => $limit,
 *      // or 'limit'           => $limit,
 *      'current_page_number'   => $page,
 *      // or 'page'            => $page,
 *      'url_options'           => array(
 *          'page_param'    => 'p',
 *          'total_param'   => 't',
 *          'params'        => array(
 *              'flag'      => $flag,
 *          ),
 *      ),
 *  ));
 *
 *  $paginator = Paginator::factory(5, array(
 *      'item_count_per_page'   => $limit,  // or 'limit' => $limit
 *      'current_page_number'   => $page,   // or 'page' => $page
 *      'url_options'           => array(
 *          'template'=> $this->url('', array(
 *              'p' => '__page__',
 *              't' => '__total__',
 *              'f' => $flag,
 *          ),
 *      ),
 *  ));
 *
 *  $paginator = Paginator::factory(5, array(
 *      'limit' => $limit,
 *      'page'  => $page,
 *      'url_options'           => array(
 *          'template'=> '/url/to/page/p/__page__/t/__total__/f/{$flag}',
 *      ),
 *  ));
 * ```
 *
 * - Create paginator with constructor
 *
 * ```
 *  $paginator = new Paginator(5);
 *  $paginator->setOptions(array(
 *      'item_count_per_page'   => $limit,  // or 'limit' => $limit
 *      'current_page_number'   => $page,   // or 'page' => $page
 *      'url_options'           => array(
 *          'page_param'    => 'p',
 *          'total_param'   => 't',
 *          'params'        => array(
 *              'flag'      => $flag,
 *          ),
 *      ),
 *  ));
 *
 *  $paginator->setOptions(array(
 *      'item_count_per_page'   => $limit,  // or 'limit' => $limit
 *      'current_page_number'   => $page,   // or 'page' => $page
 *      'url_options'           => array(
 *          'template'=> $this->url('', array(
 *              'p' => '__page__',
 *              't' => '__total__',
 *              'f' => $flag,
 *          ),
 *      ),
 *  ));
 *
 *  $paginator->setOptions(array(
 *      'item_count_per_page'   => $limit,  // or 'limit' => $limit
 *      'current_page_number'   => $page,   // or 'page' => $page
 *      'url_options'           => array(
 *          'template'=> '/url/to/page/p/__page__/t/__total__/f/{$flag}',
 *      ),
 *  ));
 * ```
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Paginator extends Pagit
{
    /**
     * Configuration file
     * @var array|null
     */
    protected static $config = null;

    /**
     * Default scrolling style
     * @var string
     */
    protected static $defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     * @var int
     */
    protected static $defaultItemCountPerPage = 10;

    /**
     * Scrolling style plugin manager
     * @var ScrollingStylePluginManager
     */
    protected static $scrollingStyles = null;

    /**
     * Adapter
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * Number of items in the current page
     * @var int|null
     */
    protected $currentItemCount = null;

    /**
     * Current page items
     * @var Traversable
     */
    protected $currentItems = null;

    /**
     * Current page number (starting from 1)
     * @var integer
     */
    protected $currentPageNumber = 1;

    /**
     * Result filter
     * @var FilterInterface
     */
    protected $filter = null;

    /**
     * Number of items per page
     * @var int|null
     */
    protected $itemCountPerPage = null;

    /**
     * Number of pages
     * @var int|null
     */
    protected $pageCount = null;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     * @var integer
     */
    protected $pageRange = 10;

    /**
     * Pages
     * @var array|null
     */
    protected $pages = null;

    /**
     * View instance used for self rendering
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $view = null;

    /**
     * Options for URL assemble:
     * template, page_param, total_param, params, router, route
     * @var array
     */
    protected $urlOptions = array(
        'page_param'    => 'p',
        'route'         => 'default',
    );

    /** @var string Pattern for URL replacement */
    const PAGE_PATTERN = '__page__';
    const TOTAL_PATTERN = '__total__';

    /**
     * Factory.
     *
     * @param mixed $data
     * @param array $options
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public static function factory($data, $options = array())
    {
        if ($data instanceof AdapterAggregateInterface) {
            return new static($data->getPaginatorAdapter());
        }

        if (is_array($data)) {
            $adapter = 'arrayAdapter';
        } elseif ($data instanceof AbstractTableGateway) {
            $adapter = 'dbTableGateway';
        } elseif ($data instanceof DbSelect) {
            $adapter = 'dbSelect';
        } elseif ($data instanceof Iterator) {
            $adapter = 'iterator';
        } elseif (is_integer($data)) {
            $adapter = 'null';
        } else {
            $type = (is_object($data)) ? get_class($data) : gettype($data);
            throw new Exception\InvalidArgumentException(
                'No adapter for type ' . $type
            );
        }

        $adapter = static::createAdapter($adapter, $data);

        $paginator = new static($adapter);
        if ($options) {
            $paginator->setOptions($options);
        }

        return $paginator;
    }

    /**
     * Canonize paginator options
     *
     * Transform:
     * - limit => item_count_per_page
     * - page => current_page_number
     *
     * 
     * @param array $options
     *
     * @return array
     */
    protected function canonizeOptions(array $options)
    {
        if (isset($options['limit'])) {
            $options['item_count_per_page'] = $options['limit'];
            unset($options['limit']);
        }
        if (isset($options['page'])) {
            $options['current_page_number'] = $options['page'];
            unset($options['page']);
        }

        return $options;
    }

    /**
     * Set options
     *
     * @param array|\Traversable $options
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                __METHOD__ . ' expects an array or Traversable'
            );
        }
        $options = $this->canonizeOptions($options);
        foreach ($options as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Set a global config
     *
     * @param array|\Traversable $config
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public static function setGlobalConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(
                __METHOD__ . ' expects an array or Traversable'
            );
        }

        static::$config = $config;

        if (isset($config['scrolling_style_plugins'])
            && null !== ($adapters = $config['scrolling_style_plugins'])
        ) {
            static::setScrollingStylePluginManager($adapters);
        }

        $scrollingStyle = isset($config['scrolling_style'])
            ? $config['scrolling_style'] : null;

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
        $adapterClass = '%s\Paginator\Adapter\\' . ucfirst($adapter);
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
        $styleClass = '%s\Paginator\ScrollingStyle\\' . ucfirst($style);
        $class = sprintf($styleClass, 'Pi');
        if (!class_exists($class)) {
            $class = sprintf($styleClass, 'Zend');
        }

        return new $class;
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
     * @return void
     */
    public static function setDefaultItemCountPerPage($count)
    {
        static::$defaultItemCountPerPage = (int) $count;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param string $scrollingStyle
     * @return void
     */
    public static function setDefaultScrollingStyle(
        $scrollingStyle = 'Sliding'
    ) {
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
                'Zend_Paginator only accepts instances of the type '
                . 'Zend\Paginator\Adapter\AdapterInterface'
                . ' or Zend\Paginator\AdapterAggregateInterface.'
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
     * Serializes the object as a string. Proxies to {@link render()}.
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
     * @return int
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
     * @return int
     */
    public function getTotalItemCount()
    {
        return count($this->getAdapter());
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  int $relativeItemNumber Relative item number
     * @param  int $pageNumber Page number
     * @return int
     */
    public function getAbsoluteItemNumber(
        $relativeItemNumber,
        $pageNumber = null
    ) {
        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);

        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        }

        $pageNumber = $this->normalizePageNumber($pageNumber);

        return (($pageNumber - 1) * $this->getItemCountPerPage())
            + $relativeItemNumber;
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
     * @return int
     */
    public function getCurrentItemCount()
    {
        if ($this->currentItemCount === null) {
            $this->currentItemCount =
                $this->getItemCount($this->getCurrentItems());
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
            $this->currentItems =
                $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param  int $pageNumber Page number
     * @return self
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
     * @return self
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Returns an item from a page.
     *
     * The current page is used if there's no page specified.
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
            throw new Exception\InvalidArgumentException(
                'Page ' . $pageNumber . ' does not exist'
            );
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new Exception\InvalidArgumentException(
                'Page ' . $pageNumber . ' does not'
                . ' contain item number ' . $itemNumber
            );
        }

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return int
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
     * @param int $itemCountPerPage
     * @return self
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
     * @return int
     */
    public function getItemCount($items)
    {
        $itemCount = 0;

        if (is_array($items) || $items instanceof Countable) {
            $itemCount = count($items);
        } elseif ($items instanceof Traversable) {
            // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @param int $pageNumber
     * @return mixed
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);
        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();
        $items = $this->adapter->getItems(
            $offset,
            $this->getItemCountPerPage()
        );

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
            throw new Exception\RuntimeException('Error producing an iterator',
                                                 null, $e);
        }
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return int
     */
    public function getPageRange()
    {
        return $this->pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  int $pageRange
     * @return self
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
     * @param  int $lowerBound Lower bound of the range
     * @param  int $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = array();

        for ($pageNumber = $lowerBound;
            $pageNumber <= $upperBound;
            $pageNumber++) {
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
     * @return self
     */
    public function setView(View\Renderer\RendererInterface $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Brings the item number in range of the page.
     *
     * @param  int $itemNumber
     * @return int
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
     * @param  int $pageNumber
     * @return int
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

        if ($currentItems instanceof AbstractResultSet) {
            return Json::encode($currentItems->toArray());
        } else {
            return Json::encode($currentItems);
        }
    }

    /**
     * Calculates the page count.
     *
     * @return int
     */
    protected function _calculatePageCount()
    {
        return (int) ceil(
            $this->getAdapter()->count() / $this->getItemCountPerPage()
        );
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
            $pages->firstItemNumber  =
                (($currentPageNumber - 1) * $this->getItemCountPerPage()) + 1;
            $pages->lastItemNumber   =
                $pages->firstItemNumber + $pages->currentItemCount - 1;
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
                        'Scrolling style must implement'
                        . ' Zend\Paginator\ScrollingStyle\\'
                        . 'ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'string':
                return $this->createScrollingStyle($scrollingStyle);

            case 'null':
                // Fall through to default case

            default:
                throw new Exception\InvalidArgumentException(
                    'Scrolling style must be a class name or object'
                    . ' implementing Zend\Paginator\ScrollingStyle\\'
                    . 'ScrollingStyleInterface'
                );
        }
    }

    /**
     * Set up options for URL assemble
     *
     * Options:
     *
     * - template: template content for generating paginator
     * - page_param: parameter name for page number
     * - total_param: parameter name for total page count
     * - params: array of extra parameters
     * - router: object of router
     * - route: route name
     *
     * @param array $options
     *
     * @return self
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
            $url = str_replace(
                array(static::PAGE_PATTERN, static::TOTAL_PATTERN),
                array($page, $this->count()),
                $this->urlOptions['template']
            );

            return $url;
        }

        $params = isset($this->urlOptions['params'])
            ? $this->urlOptions['params'] : array();
        $params[$this->urlOptions['page_param']] = $page;
        if (!empty($this->urlOptions['total_param'])) {
            $params[$this->urlOptions['total_param']] = $this->count();
        }

        $router = isset($this->urlOptions['router'])
            ? $this->urlOptions['router']
            : (isset(static::$config['router'])
                ? static::$config['router']
                : null);
        $route = isset($this->urlOptions['route'])
            ? $this->urlOptions['route']
            : (isset(static::$config['route'])
                ? static::$config['route']
                : null);

        if ($router) {
            $options = array(
                'router'                => $router,
                'reuse_matched_params'  => true
            );
        } else {
            $options = true;
        }
        $url = Pi::service('url')->assemble($route, $params, $options);

        return $url;
    }
}
