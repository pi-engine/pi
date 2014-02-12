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

use Pi;
use stdClass;
use Zend\View\Helper\HeadLink as ZendHeadLink;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and retrieving link element for HTML head
 *
 * @see \Zend\View\Helper\HeadLink for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadLink extends ZendHeadLink
{
    /**
     * {@inheritDoc}
     * @return self
     */
    public function __invoke(
        array $attributes = null,
        $placement = Placeholder\Container\AbstractContainer::APPEND
    ) {
        parent::__invoke($attributes, strtoupper($placement));

        return $this;
    }

    /**
     * {@inheritDoc}
     *  Canonize attribute 'conditional' with 'conditionalStylesheet'
     */
    public function itemToString(stdClass $item)
    {
        if (isset($item->conditional)) {
            $item->conditionalStylesheet = $item->conditional;
            $item->conditional = null;
        }

        return parent::itemToString($item);
    }
}
