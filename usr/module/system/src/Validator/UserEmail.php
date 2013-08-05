<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * User email check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserEmail extends AbstractValidator
{
    /** @var string */
    const RESERVED  = 'userEmailReserved';

    /** @var string */
    const USED      = 'userEmailUsed';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::RESERVED  => 'User email is reserved',
        self::USED      => 'User email is already used',
    );

    /**
     * Options
     * @var array
     */
    protected $options = array(
        'backlist'          => array(),
        'checkDuplication'  => true,
    );

    /**
     * User name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (!empty($this->options['backlist'])) {
            $pattern = is_array($this->options['backlist'])
                ? implode('|', $this->options['backlist'])
                : $this->options['backlist'];
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        if ($this->options['checkDuplication']) {
            $where = array('email' => $value);
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $rowset = Pi::model('user_account')->select($where);
            if ($rowset->count()) {
                $this->error(static::USED);
                return false;
            }
        }

        return true;
    }
}
