<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Zend\Paginator\Paginator;
use Zend\View\Helper\PaginationControl as ZendPaginationControl;
use Zend\View\Exception;

/**
 * Pagination creation helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PaginationControl extends ZendPaginationControl
{
    /**
     * Default Scrolling Style
     *
     * @var string
     */
    protected static $defaultScrollingStyle = 'sliding';

    /**
     * Default view partial
     *
     * @var string|array
     */
    protected static $defaultViewPartial = 'paginator';

    /**
     * Render the provided pages.  This checks if $view->paginator is set and,
     * if so, uses that.  Also, if no scrolling style or partial are specified,
     * the defaults will be used (if set).
     *
     * @param  Paginator (Optional) $paginator
     * @param  string $scrollingStyle (Optional) Scrolling style
     * @param  string $partial (Optional) View partial
     * @param  array|string $params (Optional) params to pass to the partial
     * @return string
     * @throws Exception\RuntimeException
     *      if no paginator or no view partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    public function __invoke(
        Paginator $paginator = null,
        $scrollingStyle = null,
        $partial = null,
        $params = null
    ) {
        if ($paginator === null) {
            if (isset($this->view->paginator)
                && $this->view->paginator !== null
                && $this->view->paginator instanceof Paginator
            ) {
                $paginator = $this->view->paginator;
            } else {
                throw new Exception\RuntimeException(
                    'No paginator instance provided or incorrect type'
                );
            }
        }

        if ($partial === null) {
            if (static::$defaultViewPartial === null) {
                throw new Exception\RuntimeException(
                    'No view partial provided and no default set'
                );
            }

            $partial = static::$defaultViewPartial;
        }

        if ($scrollingStyle === null) {
            $scrollingStyle = static::$defaultScrollingStyle;
        }

        $pages = get_object_vars($paginator->getPages($scrollingStyle));

        if ($params !== null) {
            $pages = array_merge($pages, (array) $params);
        }

        if (is_array($partial)) {
            if (count($partial) != 2) {
                throw new Exception\InvalidArgumentException(
                    'A view partial supplied as an array must contain'
                    . ' two values: the filename and its module'
                );
            }

            if ($partial[1] !== null) {
                $partialHelper = $this->view->plugin('partial');
                return $partialHelper($partial[0], $pages);
            }

            $partial = $partial[0];
        }

        $partialHelper = $this->view->plugin('partial');

        return $partialHelper($partial, $pages);
    }
}
