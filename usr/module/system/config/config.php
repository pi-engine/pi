<?php
/**
 * System preference config
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
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

/**
 * Config definition
 * With category and configs
 * <code>
 *  return array(
 *      'category'  => array(
 *          array(
 *              'name'  => 'category_name',
 *              'title' => 'Category Title'
 *              'order' => 1,
 *          ),
 *          array(
 *              'name'  => 'category_b',
 *              'title' => 'Category B Title'
 *              'order' => 2,
 *          ),
 *          ...
 *      ),
 *      'item'     => array(
 *          // Config of input textbox
 *          'config_name_a' => array(
 *              'title'         => 'Config title A',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'input'
 *              'filter'        => 'text',
 *          ),
 *          // 'edit' default as 'input'
 *          'config_name_ab' => array(
 *              'title'         => 'Config title AB',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *          ),
 *          // Config with select edit type
 *          'config_name_b' => array(
 *              'title'         => 'Config title B',
 *              'description'   => '',
 *              'value'         => 'option_a',
 *              'edit'          => array(
 *                  'type'  => 'select',
 *                  'options'   => array(
 *                      'options'   => array(
 *                          'option_a'  => 'Option A',
 *                          'option_b'  => 'Option B',
 *                      ),
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config with custom edit element
 *          'config_name_c' => array(
 *              'title'         => 'Config title C',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => '',
 *              'edit'          => array(
 *                  'type'          => 'Module\Demo\Form\Element\ConfigTest',
 *                  'attributes'    => array(
 *                      'att'   => 'attValue',
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config not show on edit pages
 *          'config_name_d' => array(
 *              'title'         => 'Config title D',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *              'visible'       => 0,                       // Not show on edit page
 *          ),
 *          // Orphan configs
 *          'config_name_e' => array(
 *              'title'         => 'Config title E',
 *              'category'      => '',                      // Not managed by any category
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'SpecifiedEditElement',
 *              'filter'        => 'text',
 *          ),
 *
 *          ...
 *      )
 *  );
 * </code>
 * Only with configs
 * <code>
 *  return array(
 *          'config_name'   => array(
 *              'title'         => 'Config title',
 *              'category'      => '',
 *              'description'   => '',
 *              'value'         => '',
 *          ),
 *          ...
 *  );
 * </code>
 */

$config = array();

// Config categories
$config['category'] = array(
    array(
        'name'      => 'general',
        'title'     => 'General'
    ),
    array(
        'name'      => 'meta',
        'title'     => 'Head meta',
    ),
    array(
        'name'      => 'intl',
        'title'     => 'Internationalization',
    ),
    array(
        'name'      => 'user',
        'title'     => 'User account',
    ),
    array(
        'name'      => 'text',
        'title'     => 'Text processing',
    ),
    array(
        'name'      => 'mail',
        'title'     => 'Mailing',
    ),
);

// Config items

// General section

$config['item'] = array(
    // General section

    'sitename'      => array(
        'title'         => 'Site name',
        'description'   => 'Website name.',
        'filter'        => 'string',
        'value'         => 'Pi Engine',
        'category'      => 'general',
    ),

    'slogan'        => array(
        'title'         => 'Slogan',
        'description'   => 'Website slogan.',
        'value'         => 'Power your web and mobile applications.',
        'category'      => 'general',
    ),

    'locale'        => array(
        'title'         => 'Locale',
        'description'   => 'Locale for application content.',
        'edit'          => 'locale',
        'value'         => Pi::config('locale'),
        'category'      => 'general',
    ),

    'charset'       => array(
        'title'         => 'Charset',
        'description'   => 'Charset for page display.',
        'value'         => Pi::config('charset'),
        'category'      => 'general',
    ),

    'timezone'   => array(
        'title'         => 'Timezone',
        'description'   => 'Timezone for application system.',
        'edit'          => 'timezone',
        'category'      => 'general',
    ),

    'ga_account'   => array(
        'title'         => 'GA account',
        'description'   => 'Google Analytics account. Or use following custom foot scripts.',
        'category'      => 'general',
    ),

    'foot_script'   => array(
        'title'         => 'Foot scripts',
        'description'   => 'Scripts that will be appended to each page footer. Either naked or wrapped js scripts are allowed.',
        'edit'          => 'textarea',
        'category'      => 'general',
    ),

    'asset_versioning'  => array(
        'title'         => 'Enable asset versions',
        'description'   => 'Append version to asset URLs. It is suggested to trun off in production environments for performance consideration.',
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'theme'         => array(
        'title'         => 'Theme',
        'value'         => 'default',
        'category'      => 'general',
        'visible'       => 0,
    ),

    'theme_admin'   => array(
        'title'         => 'Admin theme',
        'value'         => 'default',
        'category'      => 'general',
        'visible'       => 0,
    ),

    // Meta section

    'copyright'     => array(
        'title'         => 'Meta copyright',
        'description'   => 'The copyright meta tag defines any copyright statements you wish to disclose about your web page documents.',
        'edit'          => 'text',
        'value'         => 'Copyright &copy; ' . date('Y'),
        'category'      => 'meta',
    ),

    'author'        => array(
        'title'         => 'Meta author',
        'description'   => 'The author meta tag defines the name of the author of the document being read. Supported data formats include the name, email address of the webmaster, company name or URL.',
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'meta',
    ),

    'generator'     => array(
        'title'         => 'Meta generator',
        'description'   => 'Generator of the document being read.',
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'meta',
    ),

    'keywords'      => array(
        'title'         => 'Meta keywords',
        'description'   => 'The keywords meta tag is a series of keywords that represents the content of your site. Separated keywords by a comma.',
        'edit'          => 'textarea',
        'value'         => __('Pi Engine, Web application'),
        'category'      => 'meta',
    ),

    'description'   => array(
        'title'         => 'Meta description',
        'description'   => 'The description meta tag is a general description of what is contained in your web page',
        'edit'          => 'textarea',
        'value'         => __('Pi Engine is an extensible development engine for web and mobile applications written in PHP.'),
        'category'      => 'meta',
    ),

    // Internationalizaiton section

    'number_style'    => array(
        'title'         => 'Default number style',
        'description'   => 'See http://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'DEFAULT_STYLE'     => 'Default format for the locale',
                    'PATTERN_DECIMAL'   => 'Decimal format defined by pattern',
                    'DECIMAL'           => 'Decimal format',
                    'PERCENT'           => 'Percent format',
                    'SCIENTIFIC'        => 'Scientific format',
                    'SPELLOUT'          => 'Spellout rule-based format',
                    'ORDINAL'           => 'Ordinal rule-based format',
                    'DURATION'          => 'Duration rule-based format',
                    'PATTERN_RULEBASED' => 'Rule-based format defined by pattern',
                ),
            ),
        ),
        'value'         => 'DEFAULT_STYLE',
        'category'      => 'intl',
    ),

    'number_pattern'    => array(
        'title'         => 'Default pattern for selected number style',
        'description'   => 'Only if required by style',
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'number_currency'   => array(
        'title'         => 'Default currency type',
        'description'   => 'The 3-letter ISO 4217 currency code indicating the currency to use.',
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'date_calendar'     => array(
        'title'         => 'Default calendar for the locale',
        'description'   => '"persian" is suggested for Persian language. See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes',
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'date_datetype'    => array(
        'title'         => 'Default date type',
        'description'   => 'See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'NONE'      => 'Do not include this element',
                    'FULL'      => 'Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)',
                    'LONG'      => 'Long style (January 12, 1952 or 3:30:32pm)',
                    'MEDIUM'    => 'Medium style (Jan 12, 1952)',
                    'SHORT'     => 'Most abbreviated style, only essential data (12/13/52 or 3:30pm)',
                ),
            ),
        ),
        'value'         => 'MEDIUM',
        'category'      => 'intl',
    ),

    'date_timetype'    => array(
        'title'         => 'Default time type',
        'description'   => 'See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'NONE'      => 'Do not include this element',
                    'FULL'      => 'Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)',
                    'LONG'      => 'Long style (January 12, 1952 or 3:30:32pm)',
                    'MEDIUM'    => 'Medium style (Jan 12, 1952)',
                    'SHORT'     => 'Most abbreviated style, only essential data (12/13/52 or 3:30pm)',
                ),
            ),
        ),
        'value'         => 'LONG',
        'category'      => 'intl',
    ),

    'date_pattern'      => array(
        'title'         => 'Default formatting pattern for date-time',
        'description'   => 'See http://userguide.icu-project.org/formatparse/datetime',
        'edit'          => 'text',
        'value'         => 'yyyy-MM-dd HH:mm:ss',
        'category'      => 'intl',
    ),

    'date_format'       => array(
        'title'         => 'Default format for legacy date function',
        'description'   => 'The format is required in case Intl extension is not available. See http://www.php.net/manual/en/function.date.php',
        'edit'          => 'text',
        'value'         => 'Y-m-d H:i:s',
        'category'      => 'intl',
    ),

    // Mailing section

    /*
    'mailmethod'    => array(
        'title'         => 'Mail delivery method',
        'description'   => 'Method used to deliver mail. Default is "mail", use others only if that makes trouble.',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'phpmail'   => 'PHP mail()',
                    'sendmail'  => 'sendmail',
                    'smtp'      => 'SMTP',
                    'smtpauth'  => 'SMTPAuth',
                ),
            ),
        ),
        'value'         => 'phpmail',
        'category'      => 'mail',
    ),

    'smtphost'      => array(
        'title'         => 'SMTP host(s)',
        'description'   => 'List of SMTP servers to try to connect to.',
        'edit'          => 'textarea',
        'category'      => 'mail',
    ),

    'smtpuser'      => array(
        'title'         => 'SMTPAuth username',
        'description'   => 'Username to connect to an SMTP host with SMTPAuth.',
        'category'      => 'mail',
    ),

    'smtppass'      => array(
        'title'         => 'SMTPAuth password',
        'description'   => 'Password to connect to an SMTP host with SMTPAuth.',
        'category'      => 'mail',
    ),
    */

    'adminmail'     => array(
        'title'         => 'Admin email',
        'description'   => 'Admin email address for contact.',
        'filter'        => 'email',
        'category'      => 'mail',
    ),

    'adminname'      => array(
        'title'         => 'Admin name',
        'description'   => 'User name used to send emails',
        'category'      => 'mail',
    ),

    'mail_encoding'       => array(
        'title'         => 'Email encoding',
        'description'   => 'Encoding for email contents',
        'value'         => '',
        'category'      => 'mail',
    ),

    // Text processing

    'editor'        => array(
        'title'         => 'Editor',
        'description'   => 'Default editor for text processing',
        'category'      => 'text',
    ),

    'censor_enable'  => array(
        'title'         => 'Enable word censoring',
        'description'   => 'Words will be censored if this option is enabled. This option may be turned off for enhanced site speed.',
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'text',
    ),

    'censor_words'  => array(
        'title'         => 'Words to censor',
        'description'   => 'Enter words that should be censored in user posts. Separate each with a "|", case insensitive.',
        'edit'          => 'textarea',
        'value'         => 'fuck|shit',
        'category'      => 'text',
    ),

    'censor_replace'    => array(
        'title'         => 'Word to replace censored words',
        'description'   => 'Censored words will be replaced with the characters entered in this textbox',
        'value'         => '#OOPS#',
        'category'      => 'text',
    ),

    // User account

    'uname_format'  => array(
        'title'         => 'Username format',
        'description'   => 'Format of username for registration.',
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'       => array(
                    'strict'    => 'Strict: alphabet or number only',
                    'medium'    => 'Medium: ASCII characters',
                    'loose'     => 'Loose: multi-byte characters',
                ),
            ),
        ),
        'filter'        => 'string',
        'value'         => 'medium',
        'category'      => 'user',
    ),

    'uname_min'     => array(
        'title'         => 'Minmum username',
        'description'   => 'Minmum length of username for user registration',
        'value'         => 3,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'uname_max'     => array(
        'title'         => 'Maximum username',
        'description'   => 'Maximum length of username for user registration',
        'value'         => 32,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'password_min'  => array(
        'title'         => 'Minmum password',
        'description'   => 'Minmum length of password for user registration',
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'password_max'  => array(
        'title'         => 'Maximum password',
        'description'   => 'Maximum length of password for user registration',
        'value'         => 32,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'uname_backlist'    => array(
        'title'         => 'Username backlist',
        'description'   => 'Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.',
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'user',
    ),

    'email_backlist'    => array(
        'title'         => 'Email backlist',
        'description'   => 'Forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.',
        'edit'          => 'textarea',
        'value'         => 'pi.org$',
        'category'      => 'user',
    ),

    'rememberme'        => array(
        'title'         => 'Remember me',
        'description'   => 'Days to remember login, 0 for disable.',
        'value'         => 14,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'attempts'      => array(
        'title'         => 'Maximum attempts',
        'description'   => 'Maximum attempts allowed to try for user login',
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'login_disable'     => array(
        'title'         => 'Login disable',
        'description'   => 'Disable user login',
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'register_disable'  => array(
        'title'         => 'Register disable',
        'description'   => 'Disable user registration',
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'login_captcha'       => array(
        'title'         => 'Login CAPTCHA',
        'description'   => 'Enable CAPTCHA for user login',
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    'register_captcha'  => array(
        'title'         => 'Register CAPTCHA',
        'description'   => 'Enable CAPTCHA for user registration',
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'user',
    ),

    // Orphan configs
    'theme_module'      => array(
        'title'         => 'Module themes',
        'description'   => 'Themes for modules.',
        'value'         => array(),
        'filter'        => 'array',
        'category'      => '',
        'visible'       => 0,
    ),

    'nav_front'         => array(
        'title'         => 'Front navigation',
        'description'   => 'Global navigation for front end.',
        'value'         => 'front',
        'category'      => '',
        'visible'       => 0,
    ),

    'nav_admin'         => array(
        'title'         => 'Admin navigation',
        'description'   => 'Global navigation for admin.',
        'value'         => 'admin',
        'category'      => '',
        'visible'       => 0,
    ),

);

return $config;
