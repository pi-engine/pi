<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Validator user email
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class UserEmail extends AbstractValidator
{
    const RESERVED  = 'userEmailReserved';
    const USED      = 'userEmailUsed';

    protected $messageTemplates;

    protected $options = array(
        'checkDuplication' => true,
    );

    public function __construct()
    {
        $this->messageTemplates = array(
            self::RESERVED  => __('User email is reserved'),
            self::USED      => __('User email is already used'),
        );

        parent::__construct();
    }

    /**
     * User name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $this->setConfigOption();
        if (!empty($this->options['backlist'])) {
            $pattern = is_array($this->options['backlist']) ? implode('|', $this->options['backlist']) : $this->options['backlist'];
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        if ($this->options['checkDuplication']) {
            $where = array('email' => $value);
            if (!empty($context['uid'])) {
                $where['id <> ?'] = $context['uid'];
            }

            $count = Pi::model('account', 'user')->count($where);
            if ($count) {
                $this->error(static::USED);
                return false;
            }
        }

        return true;
    }

    /**
     * Set email validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = array(
            'backlist'         => Pi::user()->config('email_backlist'),
            'checkDuplication' => true,
        );

        return $this;
    }
}
