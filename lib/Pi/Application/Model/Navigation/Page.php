<?php
/**
 * Pi Navigation Page Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model\Navigation;

use Pi\Application\Model\Nest;

class Page extends Nest
{
    /** @var string Navigation name */
    protected $navigation = '';

    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns = array(
        'params'    => true,
        'resource'  => true,
    );

    /**
     * Set navigation name
     *
     * @param string $navigation
     * @return $this
     */
    public function setNavigation($navigation)
    {
        if (null !== $navigation) {
            $this->navigation = $navigation;
        }
        return $this;
    }

    /**
     * Get navigation
     *
     * @return string
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * Remove pages of a navigation
     *
     * @param string $nav  Navigation name
     * @return bool
     */
    public function removeByNavigation($nav)
    {
        $pageRoots = $this->getRoots(array('navigation' => $nav), array($this->column('left') . ' DESC'));
        foreach ($pageRoots as $root) {
            $this->remove($root);
        }
        return true;
    }
}
