<?php
use Zend\EventManager\Event;

namespace Module\Page\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class PageSlugDuplicate extends AbstractValidator
{
    const TAKEN        = 'slugExists';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::TAKEN     => 'Page slug already exists',
    );

    /**
     * Page slug validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (null !== $value) {
            $where = array('slug' => $value);
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
