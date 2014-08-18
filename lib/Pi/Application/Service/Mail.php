<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Mail as MailHandler;
use Zend\Mime;

/**
 * Mailing service
 *
 * Send mails using default transport and instant content
 *
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
 *  $transport = Pi::service('mail')->loadTransport('smtp',
 *      array('username' => <username>, 'password' => <password>));
 *  $transport->send($message);
 *  // Or send with default transport directly
 *  Pi::service('mail')->send($message);
 *
 *  // Set default transport
 *  Pi::service('mail')->setTransport($transport);
 * </code>
 *
 * Send mails with template
 *
 * * Template location
 *   - Full path
 *   - Custom template in locale folder of module custom: usr/custom/module/locale/en/mail/
 *   - Original template in locale folder of module: usr/module/locale/en/mail/
 *
 * * Sample
 * <code>
 *  // Load from absolute template
 *  $data = Pi::service('mail')->template('/path/to/mail-template.txt',
 *      array());
 *  // Load from template relative to current module
 *  $data = Pi::service('mail')->template('mail-template[.txt]', array());
 *  // Load from template of specified module and locale
 *  $data = Pi::service('mail')->template(
 *      array(
 *          'file'      => 'mail-template[.txt]',
 *          'module'    => 'user',
 *          'locale'    => 'en',
 *      ),
 *      array()
 *  );
 *
 *  $message = Pi::service('mail')->message($data['subject'], $data['body'],
 *      $data['format']);
 *  Pi::service('mail')->send($message);
 * </code>
 *
 * Send mails with specified mime part
 *
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
 *
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
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Mail extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'mail';

    /**
     * Default transport
     *
     * @var MailHandler\Transport\TransportInterface
     */
    protected $transport;

    /**
     * Load transport
     *
     * @param string $name
     * @param array $config
     * @return MailHandler\Transport\TransportInterface|null
     */
    public function loadTransport($name = null, $config = null)
    {
        $name = $name ?: $this->options['transport'];
        if (isset($this->options[$name])) {
            $config = array_merge($this->options[$name], (array) $config);
        }
        $option     = null;
        $exception  = null;
        $transport  = null;
        switch ($name) {
            case 'smtp':
                try {
                    $option = new MailHandler\Transport\SmtpOptions($config);
                    $transportClass = 'Smtp';
                } catch (\Exception $exception) {
                    $transportClass = '';
                }
                break;
            case 'file':
                try {
                    $option = new MailHandler\Transport\FileOptions($config);
                    $transportClass = 'File';
                } catch (\Exception $exception) {
                    $transportClass = '';
                }
                break;
            case 'sendmail':
            default:
                $option = $config;
                $transportClass = 'Sendmail';
                break;
        }
        if ($transportClass) {
            try {
                $transportClass = 'Zend\Mail\Transport\\' . $transportClass;
                $transport = new $transportClass($option);
            } catch (\Exception $eTransport) {
                trigger_error($eTransport->getMessage());
                $transport = null;
            }
        } elseif ($exception) {
            trigger_error($exception->getMessage());
        }

        return $transport;
    }

    /**
     * get default transport, load it if not previously loaded
     *
     * @return MailHandler\Transport\TransportInterface|null
     */
    public function transport()
    {
        if (!$this->transport) {
            $this->transport = $this->loadTransport();
        }

        return $this->transport;
    }

    /**
     * get default transport, load it if not previously loaded
     *
     * @param  MailHandler\Transport\TransportInterface $transport
     * @return Mail
     */
    public function setTransport(
        MailHandler\Transport\TransportInterface $transport
    ) {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Send mail
     *
     * @param MailHandler\Message $message
     *
     * @return bool
     */
    public function send(MailHandler\Message $message)
    {
        $transport = $this->transport();
        if ($transport) {
            try {
                @set_time_limit(0);
                $transport->send($message);
            } catch (\Exception $e) {
                trigger_error($e->getMessage());
                return false;
            }
        }

        return true;
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
        $sender = array(
            'mail'  => Pi::config('adminmail'),
            'name'  => Pi::config('adminname') ?: null
        );
        if ($sender['mail']) {
            $message->setSender($sender['mail'], $sender['name']);
            $message->setFrom($sender['mail'], $sender['name']);
        }
        $encoding = Pi::config('mail_encoding');
        if ($encoding) {
            $message->setEncoding($encoding);
        }

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
     * Create message from template
     *
     * @see Pi\Service\I18n for template path
     *
     * @param string|array $template
     * @param array $vars
     * @return array Associative array of subject, body and format, etc.
     */
    public function template($template, $vars = array())
    {
        // Load content
        $content = $this->loadTemplate($template);

        // Assign data
        $content = $this->assignTemplate($content, $vars);

        // Parse elements
        $result = $this->parseTemplate($content);

        return $result;
    }

    /**
     * Load a template content
     *
     * @param string $template
     * @return string
     */
    public function loadTemplate($template)
    {
        // Load directly for absolute path
        if (file_exists($template)) {
            $path = $template;
        // Get realpath
        } else {
            // Canonize path from array('module' => , 'locale' => , 'file' => )
            if (is_array($template)) {
                $module = isset($template['module'])
                    ? $template['module'] : Pi::service('module')->current();
                $locale = isset($template['locale'])
                    ? $template['locale'] : null;
                $file = $template['file'];
            // Canonize for current module
            } else {
                $module = Pi::service('module')->current();
                $locale = null;
                $file = $template;
            }

            // Canonize file extension, only txt is accepted
            if (substr($file, -4) != '.txt') {
                $file .= '.txt';
            }

            $lookup = function ($file, $locale) use ($module) {
                // Assemble module mail template
                $path = Pi::service('i18n')->getPath(
                    array('custom/module/' . $module, 'mail/' . $file),
                    $locale
                );
                // Load default template if custom template is not available
                if (!file_exists($path)) {
                    $path = Pi::service('i18n')->getPath(
                        array('module/' . Pi::service('module')->directory($module), 'mail/' . $file),
                        $locale
                    );
                }
                if (!file_exists($path)) {
                    $path = '';
                }

                return $path;
            };

            $path = $lookup($file, $locale) ?: $lookup($file, 'en');
        }

        // Load content from file
        $content = is_readable($path) ? file_get_contents($path) : '';

        return $content;
    }

    /**
     * Assign data to template
     *
     * Note:
     *
     *  - Variables are tagged with %name% in templates
     *  - Variables provided by system by default:
     *      site_name,
     *      site_url,
     *      site_slogan,
     *      site_description,
     *      site_adminname,
     *      site_adminmail
     *
     * @param string $content
     * @param array $vars
     * @return string
     */
    public function assignTemplate($content, $vars = array())
    {
        // Bind system variables
        $systemVars = array(
            'site_adminmail'    => _sanitize(Pi::config('adminmail')),
            'site_adminname'    => _sanitize(Pi::config('adminname')),
            'site_name'         => _sanitize(Pi::config('sitename')),
            'site_slogan'       => _sanitize(Pi::config('slogan')),
            'site_description'  => _sanitize(Pi::config('description')),
            'site_url'          => Pi::url('www', true),
        );
        $vars = array_merge($systemVars, $vars);
        // Assign variables
        foreach ($vars as $key => $val) {
            $content = str_replace('%' . $key . '%', $val, $content);
        }

        return $content;
    }

    /**
     * Parse content into required elements
     *
     * Template with element tag of subject, body and format:
     *
     *  - Text
     *
     *      <code>
     *          [subject]Mail from %site_name%[/subject]
     *          [body]Dear %username%, greetings from %site_name%...[/body]
     *      </code>
     *
     *  - HTML
     *
     *      <code>
     *          [subject]Mail from %site_name%[/subject]
     *          [body]<div>Dear %username%,</div>
     *                  <p>Greetings from %site_name%...</p>
     *          [/body]
     *          [format]html[/html]
     *      </code>
     *
     * Template text body only:
     *
     *  <code>
     *   Dear %username%, greetings from %site_name%...
     *  </code>
     *
     * @param string $content
     * @param array $elements   Names for elements to parse
     * @return array
     */
    public function parseTemplate($content, $elements = array())
    {
        // Default elements
        $defaultElements = array('subject', 'body', 'format');
        $elements = array_merge($defaultElements, $elements);
        $result = array_fill_keys($elements, '');

        // Extract elements
        $subpattern = '#(\[%s\](?P<%s>.*)\[\/%s\])#msU';
        $tagged = false;
        foreach ($elements as $element) {
            $pattern = str_replace('%s', $element, $subpattern);
            $matched = preg_match($pattern, $content, $matches);
            if ($matched) {
                $result[$element] = $matches[$element];
                $tagged = true;
            }
        }
        if (!$tagged && in_array('body', $elements)) {
            $result['body'] = $content;
        }

        return $result;
    }

    /**
     * Create a mime message
     *
     * @param array|string $data
     * @param null|string $type
     *
     * @return Mime\Message
     */
    public function mimeMessage($data, $type = null)
    {
        $createPart = function ($content) {
            if (is_string($content)) {
                $content = new Mime\Part($content);
            } elseif (is_array($content)) {
                list($data, $type) = $content;
                $content = $this->mimePart($data, $type);
            }
            return $content;
        };
        if (!is_array($data)) {
            if (null !== $type) {
                $part = array($data, $type);
            } else {
                $part = $data;
            }
            $parts = array($part);
        } else {
            $parts = $data;
        }
        $message = new Mime\Message;
        foreach ($parts as $content) {
            $message->addPart($createPart($content));
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

        // Canonize type
        if (!$type) {
            $type = array();
        } elseif (is_string($type)) {
            $type = array('type' => $type);
        }
        if (!isset($type['type'])) {
            $type['type'] = 'text';
        }
        if (!isset($type['language'])) {
            $type['language'] = Pi::config('locale');
        }
        if (!isset($type['charset'])) {
            $type['charset'] = Pi::config('charset');
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
