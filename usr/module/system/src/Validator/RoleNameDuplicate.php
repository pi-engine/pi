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
 * Role name duplication check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleNameDuplicate extends AbstractValidator
{
    /** @var string */
    const TAKEN = 'roleExists';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::TAKEN => 'Role name already exists',
    );

    /**
     * Role name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (null !== $value) {
            $where = array('name' => $value);
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $rowset = Pi::model('acl_role')->select($where);
            if ($rowset->count()) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}
