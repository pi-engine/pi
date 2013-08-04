<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Role select element for admin
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleAdmin extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $rowset = Pi::model('acl_role')->select(array(
                'section'   => 'admin',
            ));
            $roles = array(
                ''  => __('None'),
            );
            foreach ($rowset as $row) {
                $roles[$row->name] = __($row->title);
            }
            $this->valueOptions = $roles;
        }

        return $this->valueOptions;
    }
}
