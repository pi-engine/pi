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
 * Username validator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserName extends AbstractValidator
{
    /** @var string */
    const INVALID   = 'userNameInvalid';

    /** @var string */
    const RESERVED  = 'userNameReserved';

    /** @var string */
    const TAKEN     = 'userNameTaken';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => 'Invalid user name: %formatHint%',
        self::RESERVED  => 'User name is reserved',
        self::TAKEN     => 'User name is already taken',
    );

    /**
     * Message variables
     * @var array
     */
    protected $messageVariables = array(
        'formatHint' => 'formatHint',
    );

    /**
     * Format hint
     * @var string
     */
    protected $formatHint;

    /**
     * Format messages
     * @var array
     */
    protected $formatMessage = array(
        'strict'    =>
            'Only alphabetic and digits are allowed with leading alphabetic',
        'medium'    => 'Only ASCII characters are allowed',
        'loose'     => 'Multibyte characters are allowed',
    );

    /**
     * Format pattern
     * @var array
     */
    protected $formatPattern = array(
        'strict'    => '/[^a-zA-Z0-9\_\-]/',
        'medium'    => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/',
        'loose'     => '/[\000-\040]/',
    );

    /**
     * Options
     * @var array
     */
    protected $options = array(
        'format'            => 'strict',
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
        $format = empty($this->options['format'])
            ? 'strict' : $this->options['format'];
        if (preg_match($this->formatPattern[$format], $value)) {
            $this->formatHint = $this->formatMessage[$format];
            $this->error(static::INVALID);
            return false;
        }

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
            $where = array('identity' => $value);
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $rowset = Pi::model('user_account')->select($where);
            if ($rowset->count()) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}
