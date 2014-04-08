<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Pi;
use Zend\Form\Element\Captcha;
use Zend\Form\Element\Csrf;

/**
 * User registration complete form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterCompleteForm extends UserForm
{
    /** {@inheritDoc} */
    protected $configIdentifier = 'register-complete';

    /** @var  UserForm Register form */
    protected $registerForm;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->registerForm = Pi::api('form', 'user')->loadForm('register');
        foreach ($this->registerForm->getElements() as $element) {
            if ($element instanceof Captcha
                || $element instanceof Csrf
                || 'submit' == $element->getAttribute('type')
            ) {
                continue;
            }
            $name = $element->getName();
            if (!$this->has($name)) {
                $this->add(array(
                    'name'  => $name,
                    'type'  => 'hidden',
                ));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Load RegisterForm InputFilter
     */
    public function loadInputFilter(array $filters = array())
    {
        parent::loadInputFilter($filters);
        $inputFilter = $this->getInputFilter();
        $registerFilter = $this->registerForm->getInputFilter();
        foreach ($this->registerForm->getElements() as $element) {
            if ($element instanceof Captcha
                || $element instanceof Csrf
                || 'submit' == $element->getAttribute('type')
            ) {
                continue;
            }
            $name = $element->getName();
            if (!$inputFilter->has($name) && $registerFilter->has($name)) {
                $inputFilter->add($registerFilter->get($name));
            }
        }

        return $this;
    }
}