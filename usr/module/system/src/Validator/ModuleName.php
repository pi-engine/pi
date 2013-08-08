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
 * Module name validator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleName extends AbstractValidator
{
    /** @var string */
    const RESERVED  = 'moduleNameReserved';

    /** @var string */
    const TAKEN     = 'moduleNameTaken';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::RESERVED  => 'Module name is reserved',
        self::TAKEN     => 'Module name is already taken',
    );

    /**
     * Options
     * @var array
     */
    protected $options = array(
        // Reserved module name which could be
        // potentially conflicted with system
        'backlist'  => array(
            'pi', 'zend', 'module', 'service', 'theme',
            'application', 'event', 'registry', 'config'
        ),
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

        if (!empty($this->options['backlist'])) {
            $pattern = implode('|', $this->options['backlist']);
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        $where = array('name' => $value);
        if (!empty($context['id'])) {
            $where['id <> ?'] = $context['id'];
        }
        $rowset = Pi::model('module')->select($where);
        if ($rowset->count()) {
            $this->error(static::TAKEN);
            return false;
        }

        return true;
    }
}
