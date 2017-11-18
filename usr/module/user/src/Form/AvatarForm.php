<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of avatar
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AvatarForm extends BaseForm
{
    /**
     * Initializing form
     */
    public function init()
    {
        $this->add([
            'name'       => 'fake_id',
            'attributes' => [
                'type'  => 'hidden',
                'value' => uniqid(),
            ],
        ]);
    }
}
