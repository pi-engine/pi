<?php
/**
 * Form element role class
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
 * @package         Pi\Form
 * @subpackage      ELement
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\MultiCheckbox;

class RoleCheckbox extends MultiCheckbox
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            // Roles from section front or admin
            $section = $this->getOption('section') ?: 'front';
            $rowset = Pi::model('acl_role')->select(array('section' => $section));
            $roles = array();
            foreach ($rowset as $row) {
                $roles[$row->name] = __($row->title);
            }
            $this->valueOptions = $roles;
        }

        return $this->valueOptions;
    }
}
