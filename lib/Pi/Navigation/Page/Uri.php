<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Navigation\Page;

use Zend\Navigation\Page as ZendPage;
use Zend\Navigation\Page\Uri as ZendUriPage;
use Zend\Http\Request;
use Zend\Navigation\Exception;

/**
 * URI page
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Uri extends ZendUriPage
{
    /**#@+
     * Re-initialize
     * Modified by Taiwen Jiang
     */
    /**
     * {@inheritDoc}
     * @var bool|null
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
                    . 'Zend\Navigation\Page\AbstractPage or Traversable,'
                    . ' or an array'
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
        if (null === $this->active) {
            if ($this->getRequest() instanceof Request) {
                if ($this->getRequest()->getUri()->getPath() == $this->getUri()) {
                    $this->active = true;
                    return true;
                } else {
                    $this->active = false;
                }
            }
        }
        if (!$this->active && $recursive) {
            foreach ($this->pages as $page) {
                if ($page->isActive(true)) {
                    return true;
                }
            }
        }

        return $this->active;
        /**#@-*/

        if (!$this->active) {
            if ($this->getRequest() instanceof Request) {
                if ($this->getRequest()->getUri()->getPath() == $this->getUri()) {
                    $this->active = true;
                    return true;
                }
            }
        }

        return parent::isActive($recursive);

    }

}
