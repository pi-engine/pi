<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Zend\View\Helper\HeadTitle as ZendHeadTitle;

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * Usages:
 *
 * Set head title in controller actions,
 * or in phtml templates replace `$this->view()->headTitle()` with `$this->headTitle`
 * ```
 *  // Set separator, default at ' - '
 *  $this->view()->headTitle->setSeparator(' - ');
 *
 *  // Set postfix, default as <module title> - <site name>
 *  $this->view()->headTitle->setPostfix(' :: Powered by Pi Engine');
 *
 *  // Set prefix, default as empty
 *  $this->view()->headTitle->setPrefix('>>My site - ');
 *
 *  // Set title
 *  $this->view()->headTitle->set('Custom Title');
 *
 *  // Append title
 *  $this->view()->headTitle->append('Custom Title');
 *
 *  // Prepend title
 *  $this->view()->headTitle->prepend('Custom Title');
 * ```
 *
 *
 * @see \Zend\View\Helper\HeadTitle for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadTitle extends ZendHeadTitle
{
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
}
