<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\AbstractHelper as ZendAbstractHelper;

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
     * @param  null|ElementInterface $element
     * @param  array|mixed $options
     *
     * @return string|self
     */
    public function __invoke(
        ElementInterface $element = null,
        $options = []
    )
    {
        if (null === $element) {
            return $this;
        }

        return $this->render($element, $options);
    }

    /**
     * Render element content
     *
     * @param ElementInterface $element
     * @param array|mixed $options
     *
     * @return string
     */
    abstract public function render(
        ElementInterface $element,
        $options = []
    );
}
