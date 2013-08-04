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
 * Role select element for front or admin
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Role extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            // Roles from section front or admin
            $section = $this->getOption('section') ?: 'front';
            $rowset = Pi::model('acl_role')->select(array(
                'section'   => $section,
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
