<?php
/**
 * Pagination control helper
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
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\Paginator\Paginator;
use Zend\View\Helper\PaginationControl as ZendPaginationControl;
use Zend\View\Exception;

class PaginationControl extends ZendPaginationControl
{
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
     * @throws Exception\RuntimeException if no paginator or no view partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    public function __invoke(Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
    {
        if ($paginator === null) {
            if (isset($this->view->paginator) and $this->view->paginator !== null and $this->view->paginator instanceof Paginator) {
                $paginator = $this->view->paginator;
            } else {
                throw new Exception\RuntimeException('No paginator instance provided or incorrect type');
            }
        }

        if ($partial === null) {
            if (static::$defaultViewPartial === null) {
                throw new Exception\RuntimeException('No view partial provided and no default set');
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
                    'A view partial supplied as an array must contain two values: the filename and its module'
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
