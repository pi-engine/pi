<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class WidgetNameDuplicate extends AbstractValidator
{
    const TAKEN        = 'widgetExists';

    /**
     * @var array
     */
    protected $messageTemplates = array();


    public function __construct()
    {
        $this->messageTemplates = array(
            self::TAKEN => _a('Widget name already exists'),
        );

        parent::__construct();
    }

    /**
     * Block name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (null !== $value) {
            $where = array('name' => $value);
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }

            //$rowset = Pi::model('widget', 'widget')->select($where);
            $count = Pi::model('widget', 'widget')->count($where);
            if ($count) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}
