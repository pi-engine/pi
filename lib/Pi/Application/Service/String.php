<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface;

/**
 * String wrapper service
 *
 * Sample code:
 * ```
 *  $text = 'some text input';
 *  $length = Pi::string()->strlen($text);
 *  $partial = Pi::string()->substr($text, 0, 7);
 *  $findme = Pi::string()->strpos($text, 'in');
 *  $toGbk = Pi::string()->convert($text, 'gbk');
 *  $toUTF = Pi::string()->convert($text, 'utf-8', 'gbk');
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class String extends AbstractService
{
    protected $stringWrapper;

    /**
     * Get string wrapper
     *
     * @return StringWrapperInterface
     */
    public function getWrapper()
    {
        if (!$this->stringWrapper) {
            $this->stringWrapper = StringUtils::getWrapper(Pi::config('charset'));
        }

        return $this->stringWrapper;
    }

    /**
     * Convert a string from defined encoding to the defined convert encoding
     *
     * @param string  $str
     * @param string $toEncoding
     * @param string $fromEncoding
     *
     * @return string
     */
    public function convert($str, $toEncoding = null, $fromEncoding = null)
    {
        $wrapper = $this->getWrapper();
        $encoding = $this->getEncoding();
        $convertEncoding = $this->getConvertEncoding();
        $fromEncoding = $fromEncoding ?: $encoding;
        $toEncoding = $toEncoding ?: $convertEncoding;
        try {
            $this->setEncoding($toEncoding, $fromEncoding);
            $str = $wrapper->convert($str);
            $this->setEncoding($encoding, $convertEncoding);
        } catch (\Exception $e) {
            // Do nothing
        }

        return $str;
    }

    /**
     * Magic methods to call string wrapper
     *
     * @param string $method String wrapper methods: `strlen`, `substr`, `strpos`, `wordwrap`, `strpad`
     * @param array $args
     *
     * @return bool|mixed
     */
    public function __call($method, $args)
    {
        $wrapper = $this->getWrapper();
        if (is_callable(array($wrapper, $method))) {
            $result = call_user_func_array(array($wrapper, $method), $args);
        } else {
            $result = false;
        }

        return $result;
    }
}
