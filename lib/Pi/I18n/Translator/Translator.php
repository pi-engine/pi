<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\I18n\Translator;

use Pi;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\TextDomain;

/**
 * Translator handler
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Translator extends ZendTranslator
{
    /** @var string Default locale */
    const DEFAULT_LOCALE = 'en';

    /**
     * Previous set locale, for restore
     * @var string
     */
    protected $previousLocale;

    /**
     * Text domain
     * @var string
     */
    protected $textDomain = 'default';

    /**
     * Previous text domain, for restore
     * @var string
     */
    protected $previousTextDomain = 'default';

    /**
     * Resource loader
     * @var FileLoaderInterface
     */
    protected $loader;

    /**
     * Set locale
     *
     * {@inheritDoc}
     */
    public function setLocale($locale)
    {
        if (null !== $locale && $locale != $this->locale) {
            $this->previousLocale = $this->locale;
            $this->locale = $locale;
        }

        return $this;
    }

    /**
     * Get locale
     *
     * {@inheritDoc}
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = static::DEFAULT_LOCALE;
        }

        return $this->locale;
    }

    /**
     * {@inheritDoc}
     */
    public function getFallbackLocale()
    {
        if ($this->fallbackLocale === null) {
            $this->fallbackLocale = static::DEFAULT_LOCALE;
        }

        return $this->fallbackLocale;
    }

    /**
     * Restore the default locale.
     *
     * @return self
     */
    public function restoreLocale()
    {
        $this->locale = $this->previousLocale ?: $this->locale;

        return $this;
    }

    /**
     * Set the text domain
     *
     * @param  string $textDomain
     * @return self
     */
    public function setTextDomain($textDomain)
    {
        if ($textDomain != $this->textDomain) {
            $this->previousTextDomain = $this->textDomain;
            $this->textDomain = $textDomain;
        }

        return $this;
    }

    /**
     * Get the text domain
     *
     * @return string
     */
    public function getTextDomain()
    {
        return $this->textDomain;
    }

    /**
     * Restore the text domain
     *
     * @return self
     */
    public function restoreTextDomain()
    {
        $this->textDomain = $this->previousTextDomain;

        return $this;
    }

    /**
     * Restore text domain and locale to previous one
     *
     * @return self
     */
    public function restore()
    {
        $this->locale = $this->previousLocale ?: $this->locale;
        $this->textDomain = $this->previousTextDomain;

        return $this;
    }

    /**
     * Set resource loader
     *
     * @param FileLoaderInterface $loader
     * @return self
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get resource loader
     *
     * @return FileLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Translate a message with specific domain and locale
     *
     * {@inheritDoc}
     */
    public function translate($message, $textDomain = null, $locale = null)
    {
        if (null === $textDomain) {
            $textDomain = $this->getTextDomain();
        }
        //d($textDomain);

        return parent::translate($message, $textDomain, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = null,
        $locale = null
    ) {
        if (!$textDomain) {
            $textDomain = $this->getTextDomain();
        }

        return parent::translatePlural(
            $singular,
            $plural,
            $number,
            $textDomain,
            $locale
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTranslatedMessage(
        $message,
        $locale = null,
        $textDomain = null
    ) {
        if ($message === '') {
            return '';
        }

        if (!$textDomain) {
            $textDomain = $this->getTextDomain();
        }

        if (isset($this->messages[$textDomain][$locale][$message])) {
            return $this->messages[$textDomain][$locale][$message];
        }

        if (isset($this->messages[''][$locale][$message])) {
            return $this->messages[''][$locale][$message];
        }

        //return parent::getTranslatedMessage($message, $locale, $textDomain);

        return null;
    }

    /**
     * Load translation resource, existent data will be flushed
     *
     * @param array|string $domain
     * @param string|null $locale
     * @return bool
     */
    public function load($domain, $locale = null)
    {
        // Array of ($textDomain, $file)
        $domain = is_array($domain)
            ? $domain : Pi::service('i18n')->normalizeDomain($domain);
        $this->setTextDomain($domain[0]);
        $this->setLocale($locale);

        $messages = (array) Pi::registry('i18n')
            ->setGenerator(array($this, 'loadResource'))
            ->read($domain, $this->locale);
        $this->messages[$this->textDomain][$this->locale] =
            new TextDomain($messages);
        //$this->messages[$this->textDomain][$this->locale] = $messages;
        if ($this->textDomain && $messages) {
            if (!empty($this->messages[''][$this->locale])) {
                foreach ($messages as $key => $val) {
                    $this->messages[''][$this->locale]->offsetSet($key, $val);
                }
            } else {
                $this->messages[''][$this->locale] = new TextDomain($messages);
            }
        }

        return $messages ? true : false;
    }

    /**
     * Load translation resource
     *
     * @param array $options
     * @return array
     * @see Pi\Application\Registry\I18n
     */
    public function loadResource($options)
    {
        $filename = Pi::service('i18n')->getPath(
            array($options['domain'],
            $options['file']),
            $options['locale']
        );
        try {
            $result = $this->loader->load($options['locale'], $filename);
        } catch (\Exception $e) {
            $result = false;
        }
        if (false === $result) {
            if (Pi::service()->hasService('log')) {
                Pi::service()->getService('log')->info(sprintf(
                    'Translation "%s-%s.%s" load failed.',
                    $options['domain'],
                    $options['file'],
                    $options['locale']
                ));
            }
            $result = array();
        } else {
            $result = (array) $result;
        }

        return $result;
    }
}
