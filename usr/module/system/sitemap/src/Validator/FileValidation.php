<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Sitemap\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class FileValidation extends AbstractValidator
{
    const TAKEN = 'FileInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::TAKEN     => 'XML file name is not valid, vlaid example is : sitemap1.xml, You shuold add .xml as file prefix and use a-z 1-9 on filename',
    );

    public function isValid($value)
    {
        $this->setValue($value);
        if(preg_match('/^[a-z0-9-]+\.xml$/', $value)) {
            return true;
        } else {
            $this->error(static::TAKEN);
            return false;
        } 
    }
}
