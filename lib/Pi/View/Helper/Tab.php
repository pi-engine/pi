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

/**
 * Helper for rendering tab menu
 *
 * Usage inside a phtml template, default as vertical with data attributes:
 * - label
 * - href
 * - active
 *
 *
 * ```
 *  $this->tab($data, 'vertical');
 *  $this->tab($data);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tab extends NavMenu
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $items, $attribs = [])
    {
        if (is_array($attribs) && !isset($attribs['type'])) {
            $attribs['type'] = 'horizontal';
        }

        return parent::__invoke($items, $attribs);
    }
}
