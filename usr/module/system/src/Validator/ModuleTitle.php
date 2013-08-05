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
 * Module title check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleTitle extends AbstractValidator
{
    /** @var string */
    const TAKEN     = 'moduleTitleTaken';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::TAKEN     => 'Module title is already taken',
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

        $where = array('title' => $value);
        if (!empty($context['id'])) {
            $where['id <> ?'] = $where['id'];
        }
        $rowset = Pi::model('module')->select($where);
        if ($rowset->count()) {
            $this->error(static::TAKEN);
            return false;
        }

        return true;
    }
}
