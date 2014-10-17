<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\HeadTitle as ZendHeadTitle;

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * @see \Zend\View\Helper\HeadTitle for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadTitle extends ZendHeadTitle
{
    /** @var bool */
    protected $isReset = false;

    /**
     * {@inheritDoc}
     */
    public function __invoke($title = null, $setType = null)
    {
        if (null !== $setType) {
            $setType = strtoupper($setType);
        }

        return parent::__invoke($title, $setType);
    }

    /**
     * {@inheritDoc}
     */
    public function set($value)
    {
        $this->isReset = true;
        parent::set($value);

        return $this;
    }

    /**
     * Check if head title is reset
     *
     * @return bool
     */
    public function isReset()
    {
        return $this->isReset;
    }
}
