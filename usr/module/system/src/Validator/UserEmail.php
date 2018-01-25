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
use Zend\Validator\EmailAddress;

//use Zend\Validator\AbstractValidator;

/**
 * User email check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserEmail extends EmailAddress
{
    /** @var string */
    const RESERVED = 'userEmailReserved';

    /** @var string */
    const USED = 'userEmailUsed';

    /**
     * Message templates
     * @var array
     */
    //protected $messageTemplates = array();

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $options = $options ?: [];
        $options = array_merge([
            'blacklist'         => [],
            'check_duplication' => true,
        ], $options);

        parent::__construct($options);
        $this->abstractOptions['messageTemplates'] = [
                static::RESERVED => __('User email is reserved'),
                static::USED     => __('User email is already used'),
            ] + $this->abstractOptions['messageTemplates'];
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

        $result = parent::isValid($value, $context);
        if (!$result) {
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
            $where = ['email' => $value];
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $count = Pi::model('user_account')->count($where);
            if ($count) {
                $this->error(static::USED);
                return false;
            }
        }

        return true;
    }
}
