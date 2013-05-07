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
use Zend\Mime;

/**
 * Mailing service
 *
 * Send mails using default transport and instant content
 * <code>
 *  $subject = _('Welcome to Pi community');
 *
 *  $body = _('Mail body message ...');;
 *  $messageText = Pi::service('mail')->message($subject, $body);
 *
 *  $body = _('Mail body message with HTML ...');;
 *  $messageHtml = Pi::service('mail')->message($subject, $body, 'text/html');
 *
 *  // Send with default transport
 *  $transport = Pi::service('mail')->transport();
 *  $transport->send($message);
 *  // Send with specified transport
 *  $transport = Pi::service('mail')->transport('smtp', array('username' => '', 'password' => ''));
 *  $transport->send($message);
 *  // Or send with default transport directly
 *  Pi::service('mail')->send($message);
 * </code>
 *
 * Send mails with template
 * <code>
 *  // Load from absolute template
 *  $body = Pi::service('mail')->template('/path/to/mail-template.phtml', array());
 *  // Load from template relative to current module
 *  $body = Pi::service('mail')->template('mail-template[.phtml]', array());
 *  // Load from template of specified module and locale
 *  $body = Pi::service('mail')->template(array('file' => 'mail-template[.phtml]', 'module' => 'user', 'locale' => 'en'), array());
 *
 *  $message = Pi::service('mail')->message($subject, $body, 'text/html');
 *  Pi::service('mail')->send($message);
 * </code>
 *
 * Send mails with specified mime part
 * <code>
 *  $subject = _('Welcome to Pi community');
 *  $part = Pi::service('mail')->mimePart('part with custom type', 'html');
 *  $part = Pi::service('mail')->mimePart(
 *      'part with custom options',
 *      array(
 *          'type'      => 'html',
 *          'charset'   => 'utf-8',
 *      )
 *  );
 *  $body = Pi::service('mail')->mimeMessage($part);
 *  $message = Pi::service('mail')->message($subject, $body);
 *  Pi::service('mail')->send($message);
 * </code>
 *
 * Send mails with multiple mime parts
 * <code>
 *  $subject = _('Welcome to Pi community');
 *  $parts = array(
 *      'part with direct content',
 *      array(
 *          'part with custom type',
 *          'html',
 *      ),
 *      array(
 *          'part with custom options',
 *          array(
 *              'type'      => 'html',
 *              'charset'   => 'utf-8',
 *          ),
 *      ),
 *      $partOfMimePart
 *  );
 *  $body = Pi::service('mail')->mimeMessage($parts);
 *  $message = Pi::service('mail')->message($subject, $body);
 *  Pi::service('mail')->send($message);
 * </code>
 */
class Mail extends AbstractService
{
    /**
     * Load transport
     *
     * @param string $name
     * @param array $config
     * @return \MailHandler\Transport\TransportInterface
     */
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

    /**
     * Send mail
     *
     * @param MailHandler\Message $message
     * @param string $body
     */
    public function send(MailHandler\Message $message)
    {
        $this->transport()->send($message);
    }

    /**
     * Create a mail message
     *
     * @param string $subject
     * @param string $body
     * @param string $type
     * @return MailHandler\Message
     */
    public function message($subject = null, $body = null, $type = null)
    {
        $message = new MailHandler\Message;
        $message->setSender(Pi::config('from', 'mail'), Pi::config('fromname', 'mail'));
        if ($subject) {
            $message->setSubject($subject);
        }
        if ($body) {
            if ($type) {
                $part = $this->mimePart($body, $type);
                $body = new Mime\Message;
                $body->addPart($part);
            }
            $message->setBody($body);
        }
        return $message;
    }

    /**
     * Load content from template
     *
     * @see Pi\Service\I18n for template path
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

            if (substr($file, -6) != '.phtml') {
                $file .= '.phtml';
            }
            $path = Pi::service('i18n')->getPath(array('module/' . $module, 'mail/' . $file), $locale);
            if (!file_exists($path)) {
                $locale = 'en';
                $path = Pi::service('i18n')->getPath(array('module/' . $module, 'mail/' . $file), $locale);
            }
        }

        if ($vars) {
            extract($vars);
        }
        ob_start();
        include $path;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Create a mime message
     *
     * @param array $parts
     * @return Mime\Message
     */
    public function mimeMessage($parts = array())
    {
        $message = new Mime\Message;
        $part = function ($content) use ($this)
        {
            if (is_string($content)) {
                $content = new Mime\Part($content);
            }
            if (is_array($content)) {
                list($data, $type) = $content;
                $content = $this->mimePart($data, $type);
            }
            return $content;
        };
        $parts = (array) $parts;
        foreach ($parts as $content) {
            $message->addPart($part($content));
        }

        return $message;
    }

    /**
     * Create a mime part
     *
     * @param mixed $content
     * @param string|array $type
     * @return Mime\Part
     */
    public function mimePart($content, $type = null)
    {
        $part = new Mime\Part($content);
        if (is_scalar($type)) {
            $type['type'] = $type;
        }
        foreach ($type as $key => $val) {
            switch ($key) {
                case 'id':
                case 'encoding':
                case 'disposition':
                case 'filename':
                case 'charset':
                case 'boundary':
                case 'location':
                case 'language':
                    $value = $val;
                    break;
                case 'type':
                    switch ($val) {
                        case 'text':
                        case 'plain':
                            $value = Mime\Mime::TYPE_TEXT;
                            break;
                        case 'html':
                            $value = Mime\Mime::TYPE_HTML;
                            break;
                        case 'stream':
                        case 'resource':
                            $value = Mime\Mime::TYPE_OCTETSTREAM;
                            break;
                        default:
                            $value = $val;
                    }
                    break;
                default:
                    $value = null;
                    break;
            }
            if (null !== $value) {
                $part->{$key} = $value;
            }
        }

        return $part;
    }
}
