<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\I18n\Translator;

use Pi;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorInterface;
use Zend\I18n\Translator\TextDomain;


/**
 * Translator handler
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Translator extends ZendTranslator implements ValidatorInterface
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

    /** @var string File extension */
    protected $extension;

    /** @var array Loaded i18n files */
    protected $loaded = array();

    /**
     * Set translation file extension
     *
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

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

        return null;
    }

    /**
     * Load translation resource, existent data will be flushed
     *
     * @param array|string  $rawDomain
     * @param string|null   $locale
     * @param bool|null     $custom
     *
     * @return bool
     */
    public function load($rawDomain, $locale = null, $custom = null)
    {
        // Canonize locale
        $locale = $locale ?: Pi::service('i18n')->getLocale();

        // Canonize domain
        if (is_array($rawDomain)) {
            if (!array_key_exists(0, $rawDomain)) {
                extract($rawDomain);
            } else {
                list($domain, $file) = $rawDomain;
            }
        } else {
            list($domain, $file) =
                Pi::service('i18n')->canonizeDomain($rawDomain);
        }
        if ('custom/' == substr($domain, 0, 7)) {
            $custom = true;
            $domain = substr($domain, 7);
        }

        $this->setTextDomain($domain);
        $this->setLocale($locale);
        $keyLoaded = sprintf(
            '%s-%s-%s-%d',
            $domain,
            $file,
            $locale,
            null === $custom ? -1 : (int) $custom
        );
        if (isset($this->loaded[$keyLoaded])) {
            return $this->loaded[$keyLoaded];
        }
        $messages = Pi::registry('i18n')->read(
            array($domain, $file),
            $locale,
            $custom
        );
        /*
        $messages = (array) Pi::registry('i18n')
            ->setGenerator(array($this, 'loadResource'))
            ->read($domain, $this->locale);
        */
        if (is_array($messages)) {
            $textDomain = new TextDomain($messages);
            if (!empty($this->messages[$this->textDomain][$this->locale])) {
                $this->messages[$this->textDomain][$this->locale]->merge($textDomain);
            } else {
                $this->messages[$this->textDomain][$this->locale] = $textDomain;
            }

            if ($this->textDomain && $messages) {
                if (!empty($this->messages[''][$this->locale])) {
                    $this->messages[''][$this->locale]->merge($textDomain);
                } else {
                    $this->messages[''][$this->locale] = clone $textDomain;
                }
            }

            $result = true;
        } else {
            $result = false;
        }
        $this->loaded[$keyLoaded] = $result;

        return $result;
    }

    /**
     * Load translation resource
     *
     * @param array $options
     *
     * @return array
     * @see Pi\Application\Registry\I18n
     */
    public function loadResource($options)
    {
        $filename = Pi::service('i18n')->getPath(
            array($options['domain'], $options['file']),
            $options['locale']
        ) . '.' . $this->extension;
        try {
            $result = $this->loader->load($options['locale'], $filename);
            $result = (array) $result;
        } catch (\Exception $e) {
            if (Pi::service()->hasService('log')) {
                Pi::service()->getService('log')->info(sprintf(
                    'Translation "%s-%s.%s" load failed: ' . $e->getMessage(),
                    $options['domain'],
                    $options['file'],
                    $options['locale']
                ));
            }
            $result = array();
        }

        return $result;
    }
}
