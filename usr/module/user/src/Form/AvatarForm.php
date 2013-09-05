<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of avatar
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */ 
class AvatarForm extends BaseForm
{
    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'fake_id',
            'attributes' => array(
                'type'      => 'hidden',
            ),
        ));
    }
}
