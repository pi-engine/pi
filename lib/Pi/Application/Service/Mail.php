<?php
/**
 * Pi Engine mailing service
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Mail as MailHandler;

/**
 * Mailing service
 *
 * <code>
 *  $transport = Pi::service('mail')->transport(...);
 *  $message = Pi::service('mail')->message(...);
 *  $body = Pi::service('mail')->template(...);
 *  $message->setBody($body);
 *  $transport->send($message);
 *
 *  Pi::service('mail')->send($message);
 * </code>
 */
class Mail extends AbstractService
{
    //protected $transport;
    //protected $message;

    public function send(MailHandler\Message $message)
    {
        $this->transport()->send($message);
    }

    public function transport($name = null, $config = null)
    {
        $name = $name ?: $this->options['transport'];
        if (isset($this->options[$name])) {
            $config = array_merge($this->options[$name], (array) $config);
        }
        switch ($name) {
            case 'smtp':
                $smtpOptions = new MailHandler\Transport\SmtpOptions($config);
                $transport = new MailHandler\Transport\Smtp($smtpOptions);
                break;
            case 'file':
                $fileOptions = new MailHandler\Transport\FileOptions($config);
                $transport = new MailHandler\Transport\File($fileOptions);
                break;
            case 'sendmail':
            default:
                $transport = new MailHandler\Transport\Sendmail($config);
                break;
        }

        return $transport;
    }

    public function message($body = null, $subject = null)
    {
        $message = new MailHandler\Message;
        $message->setSender(Pi::config('from', 'mail'), Pi::config('fromname', 'mail'));
        if ($body) {
            $message->setBody($body);
        }
        if ($subject) {
            $message->setSubject($body);
        }
        return $message;
    }

    /**
     * Load content from template
     *
     * @see Pi\Service\I18n
     *
     * @param string|array $template
     * @param array $vars
     * @return string
     */
    public function template($template, $vars = array())
    {
        if (file_exists($template)) {
            $path = $template;
        } else {
            if (is_array($template)) {
                $module = isset($template['module']) ? $template['module'] : Pi::service('module')->directory();
                $locale = isset($template['locale']) ? $template['locale'] : null;
                $file = $template['file'];
            } else {
                $module = Pi::service('module')->directory();
                $locale = null;
                $file = $template;
            }
            $path = Pi::service('i18n')->getPath(array('module/' . $module, 'mail/' . $file), $locale) . '.phtml';
        }

        if ($vars) {
            extract($vars);
        }
        ob_start();
        include $path;
        $content = ob_get_clean();

        return $content;
    }
}
