<?php
/**
 * Uri page class
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
 * @package         Pi\Navigation
 */

namespace Pi\Navigation\Page;

use Pi;
use Zend\Navigation\Page as ZendPage;
use Zend\Navigation\Page\Uri as ZendUriPage;
use Zend\Navigation\Exception;

/**
 * {@inheritDoc}
 */
class Uri extends ZendUriPage
{
    /**#@+
     * Re-initialize
     * Modified by Taiwen Jiang
     */
    /**
     * {@inheritDoc}
     */
    protected $active = null;
    /**#@-*/

    /**
     * {@inheritDoc}
     * @see Pi\Navigation\Navigation::addPage()
     * @see Pi\Navigation\Page\Mvc::addPage()
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new Exception\InvalidArgumentException(
                'A page cannot have itself as a parent'
            );
        }

        if (!$page instanceof ZendPage\AbstractPage) {
            if (!is_array($page) && !$page instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $page must be an instance of '
                    . 'Zend\Navigation\Page\AbstractPage or Traversable, or an array'
                );
            }
            $page = AbstractPage::factory($page);
        }

        $hash = $page->hashCode();

        if (array_key_exists($hash, $this->index)) {
            // page is already in container
            return $this;
        }

        // adds page to container and sets dirty flag
        $this->pages[$hash] = $page;
        $this->index[$hash] = $page->getOrder();
        $this->dirtyIndex = true;

        // inject self as page parent
        $page->setParent($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isActive($recursive = false)
    {
        /**#@+
         * Modified by Taiwen Jiang
         */
        //if (!$this->active && $recursive) {
        if (null === $this->active && $recursive) {
            foreach ($this->pages as $page) {
                if ($page->isActive(true)) {
                    $this->active = true;
                    return true;
                }
            }
            $this->active = false;
            return false;
        }
        /**#@-*/

        return $this->active;
    }

}
