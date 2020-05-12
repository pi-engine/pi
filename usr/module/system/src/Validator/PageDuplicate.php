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
 * Page existence check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageDuplicate extends AbstractValidator
{
    /** @var string */
    const PAGEEXISTS = 'pageExists';

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = [
            static::PAGEEXISTS => __('The page already exists'),
        ];
        parent::__construct($options);
    }

    /**
     * Page validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $where = [
            'section'    => $context['section'],
            'module'     => $context['module'],
            'action'     => $context['action'],
            'controller' => $value,
        ];

        //$rowset = Pi::model('page')->select($where);
        $count = Pi::model('page')->count($where);
        if ($count) {
            $this->error(static::PAGEEXISTS);
            return false;
        }

        return true;
    }
}
