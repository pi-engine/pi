<?php
/**
 * Pi Navigation Page Model
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
 * @package         Pi\Application
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model\Navigation;
use Pi\Application\Model\Nest;

class Page extends Nest
{
    protected $navigation = '';

    /**
     * Classname for row
     *
     * @var string
     */
    protected $encodeColumns = array(
        'params'    => true,
        'resource'  => true,
    );

    /**
     * Classname for row
     *
     * @var string
     */
    //protected $rowClass = 'Pi\\Db\\RowGateway\\Node';

    public function setNavigation($navigation)
    {
        if (null !== $navigation) {
            $this->navigation = $navigation;
        }
        return $this;
    }

    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * Remove pages of a navigation
     *
     * @param string $nav  Navigation name
     * @return boolean
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
