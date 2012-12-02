<?php
/**
 * View helper to register form element helpers
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
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Pi\Form\View\HelperLoader;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for rendering form element
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->form()->formInput($element);
 *  $this->form()->formLabel();
 *  $this->form('label);
 * </code>
 */
class RegisterForm extends AbstractHelper
{
    protected $isLoaded;

    /**
     * invoke
     *
     * @param   string  $name
     * @return  string
     */
    public function __invoke()
    {
        if ($this->isLoaded) {
            return $this;
        }
        $this->view->getBroker()->getClassLoader()->registerLoader(new HelperLoader);
        return $this;
    }
}
