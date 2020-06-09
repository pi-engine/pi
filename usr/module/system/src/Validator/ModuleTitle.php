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
 * Module title check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleTitle extends AbstractValidator
{
    /** @var string */
    const TAKEN = 'moduleTitleTaken';

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = [
            static::TAKEN => __('Module title is already taken'),
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

        $where = ['title' => $value];
        if (!empty($context['id'])) {
            $where['id <> ?'] = $where['id'];
        }
        $count = Pi::model('module')->count($where);
        if ($count) {
            $this->error(static::TAKEN);
            return false;
        }

        return true;
    }
}
