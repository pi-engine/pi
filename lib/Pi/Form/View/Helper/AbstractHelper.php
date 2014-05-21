<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper as ZendAbstractHelper;
use Zend\Form\ElementInterface;

/**
 * Basic class for helpers
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractHelper extends ZendAbstractHelper
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  null|ElementInterface    $element
     * @param  array|mixed              $options
     *
     * @return string|self
     */
    public function __invoke(
        ElementInterface $element = null,
        $options = array()
    ) {
        if (null === $element) {
            return $this;
        }

        return $this->render($element, $options);
    }

    /**
     * Render element content
     *
     * @param ElementInterface  $element
     * @param array|mixed       $options
     *
     * @return string
     */
    abstract public function render(
        ElementInterface $element,
        $options = array()
    );
}
