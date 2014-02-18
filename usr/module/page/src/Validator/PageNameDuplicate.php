<?php
namespace Module\Page\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class PageNameDuplicate extends AbstractValidator
{
    const TAKEN        = 'pageExists';

    public function __construct()
    {
        $this->messageTemplates = array(
            self::TAKEN => _a('Page name already exists.'),
        );

        parent::__construct();
    }

    /**
     * Page name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (null !== $value) {
            $where = array('name' => strval($value));
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $rowset = Pi::model('page/page')->select($where);
            if ($rowset->count()) {
                $this->error(static::TAKEN);
                return false;
            }
        }

        return true;
    }
}
