<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Laminas\Form\Element\Select;

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
            $rowset  = Pi::model('acl_role')->select(
                [
                    'section' => $section,
                ]
            );
            $roles   = [
                '' => __('None'),
            ];
            foreach ($rowset as $row) {
                $roles[$row->name] = __($row->title);
            }
            $this->valueOptions = $roles;
        }

        return $this->valueOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Role');
        }

        return parent::getLabel();
    }
}
