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
use Laminas\Validator\AbstractValidator;

/**
 * Navigation name check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavNameDuplicate extends AbstractValidator
{
    /** @var string */
    const TAKEN = 'navExists';

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = [
            static::TAKEN => __('Navigation name is already taken'),
        ];
        parent::__construct($options);
    }

    /**
     * Navigation name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (null !== $value) {
            $where = ['name' => $value];
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $count = Pi::model('navigation')->count($where);
            if ($count) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}
