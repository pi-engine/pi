<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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

    /** @var array */
    protected $messageTemplates = array();

    /** @var array */
    protected $formatMessage = array();

    /**
     * Format pattern
     * @var array
     */
    protected $formatPattern = array(
        'strict'        => '/[^a-zA-Z0-9\_\-]/',
        'strict-space'  => '/[^a-zA-Z0-9\_\-\s]/',
        'medium'        => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/',
        'medium-space'  => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"\s]/',
        'loose'         => '/[\000-\040]/',
        'loose-space'   => '/[\000-\040][\s]/',
    );

    /**
     * Options
     * @var array
     */
    protected $options = array(
        'format'            => 'strict',
        'blacklist'         => array(),
        'check_duplication' => true,
    );

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = $this->messageTemplates + array(
            self::INVALID   => __('Invalid username: %formatHint%'),
            self::RESERVED  => __('Username is reserved'),
            self::TAKEN     => __('Username is already taken'),
        );

        $this->formatMessage = array(
            'strict'        => __('Only alphabetic and digits are allowed'),
            'strict-space'  => __('Only alphabetic, digits and spaces are allowed'),
            'medium'        => __('Only ASCII characters are allowed'),
            'medium-space'  => __('Only ASCII characters and spaces are allowed'),
            'loose'         => __('Only multi-byte characters are allowed'),
            'loose-space'   => __('Only multi-byte characters and spaces are allowed'),
        );

        parent::__construct($options);
    }

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

        if (!empty($this->options['blacklist'])) {
            $pattern = is_array($this->options['blacklist'])
                ? implode('|', $this->options['blacklist'])
                : $this->options['blacklist'];
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        if ($this->options['check_duplication']) {
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
