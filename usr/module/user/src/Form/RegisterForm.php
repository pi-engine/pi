<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

/**
 * User registration form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterForm extends UserForm
{
    /** {@inheritDoc} */
    protected $configIdentifier = 'register';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if($this->get('credential')){
            $this->add(array(
                'name'          => 'show_credential',
                'type' => 'checkbox',
                'attributes' => array(
                    'description' => __('Show my password'),
                ),
            ), array('priority' => -100));

            $showPasswordBtn = <<<HTML
<label>
    <input
        onchange="$('input[name=credential], input[name=credential-confirm]').attr('type', function(index, attr){ return attr == 'text' ? 'password' : 'text';})"
        name="show_password"
        type="checkbox"
    />
    
    Show my password
</label>
HTML;

            $this->get('credential')->setAttribute('description', $showPasswordBtn);
        }

        $this->add(array(
            'name'       => 'redirect',
            'type'       => 'hidden',
        ));
    }
}