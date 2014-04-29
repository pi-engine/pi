<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Username validator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Username extends AbstractValidator
{
    /** @var string */
    const INVALID   = 'usernameInvalid';

    /** @var string */
    const RESERVED  = 'usernameReserved';

    /** @var string */
    const TAKEN     = 'usernameTaken';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => 'Invalid username: %formatHint%',
        self::RESERVED  => 'Username is reserved',
        self::TAKEN     => 'Username is already taken',
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
        'strict'    => 'Only alphabetic and digits are allowed with leading alphabetic',
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
            $isDuplicated = $this->isDuplicated($value, $context);
            if ($isDuplicated) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }

    /**
     * Check for duplication
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    protected function isDuplicated($value, $context)
    {
        $where = array('identity' => $value);
        if (!empty($context['id'])) {
            $where['id <> ?'] = $context['id'];
        }
        $count = Pi::model('user_account')->count($where);
        if ($count) {
            return true;
        }

        return false;
    }
}
