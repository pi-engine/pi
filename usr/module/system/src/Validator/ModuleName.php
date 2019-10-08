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
 * Module name validator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleName extends AbstractValidator
{
    /** @var string */
    const RESERVED = 'moduleNameReserved';

    /** @var string */
    const TAKEN = 'moduleNameTaken';

    /**
     * Options
     * @var array
     */
    protected $options
        = [
            // Reserved module name which could be
            // potentially conflicted with system
            'blacklist' => [
                'pi', 'zend', 'module', 'service', 'theme',
                'application', 'event', 'registry', 'config',
            ],
        ];

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = [
            static::RESERVED => __('Module name is reserved'),
            static::TAKEN    => __('Module name is already taken'),
        ];
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

        if (!empty($this->options['blacklist'])) {
            $pattern = implode('|', $this->options['blacklist']);
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        $where = ['name' => $value];
        if (!empty($context['id'])) {
            $where['id <> ?'] = $context['id'];
        }
        $count = Pi::model('module')->count($where);
        if ($count) {
            $this->error(static::TAKEN);
            return false;
        }

        return true;
    }
}
