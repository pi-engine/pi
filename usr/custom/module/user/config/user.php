<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User profile and resource specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
//return array();


$interestOptsArr = array(
    __('chuangan/MEMS'),
    __('kongzhiqi/chuliqi/DSP'),
    __('baohu/geli'),
    __('kaiguan/duolufuyongqi'),
    __('fadahexianxingchanpin'),
    __('cunchu'),
    __('lianjieqi'),
    __('ceshiceliang'),
    __('shujuzhuanhuanqi'),
    __('jiekou'),
    __('guangdian/xianshi'),
    __('qianrushikaifagongju'),
    __('dianyuan/dianchiguanli'),
    __('yin/shipin'),
    __('RF/weibo'),
    __('EDA/IP/IC/PCBsheji'),
    __('shuzi/kebianchengluoji'),
    __('fenli/wuyuanqijian'),
    __('tongxin/wangluoIC'),
    __('zhizao/fengzhuang'),
    __('shizhong/dingshi'),
);

foreach ($interestOptsArr as $value) {
    $interestOpts[$value] = array(
        'value'            => $value,
        'label'            => $value,
        'label_attributes' => array(
            'class' => 'checkbox interest-checkbox'
        )
    );
}

$subscriptionOptsArr = array(
    __('qianrushixitongsheji'),
    __('wuxian&shepin'),
    __('lvsesheji'),
    __('ceshiceliang'),
    __('xiaofeidianzi'),
    __('dianzizixunjinji'),
    __('moni&dianyuan'),
    __('gongyedianzi'),
);

foreach ($subscriptionOptsArr as $value) {
    $subscriptionOpts[$value] = array(
        'value'            => $value,
        'label'            => $value,
        'label_attributes' => array(
            'class' => 'checkbox subscription-checkbox'
        )
    );
}

return array(
    // Fields
    'field'     => array(

        // Profile fields

        // Profile: Full name
        'fullname'  => array(
            'name'      => 'fullname',
            'title'     => __('Full name'),
            'edit' => array(
                'required' => true,
            ),
        ),

        // Profile: Language
        'language'  => array(
            'name'  => 'language',
            'title' => __('Language'),
            'edit'  => 'locale',
        ),

        // Profile: Country
        'country'  => array(
            'name'  => 'country',
            'title' => __('Country'),
            'edit' => array(
                'required' => true,
                'element'  => 'Custom\User\Form\Element\Location',
            ),
        ),

        // Profile: Province
        'province'  => array(
            'name'  => 'province',
            'title' => __('Province'),

            'edit'  => 'hidden',
        ),

        // Profile: City
        'city'  => array(
            'name'  => 'city',
            'title' => __('City'),

            'edit'  => 'hidden',
        ),

        //Contact
        'telephone' => array(
            'name'  => 'telephone',
            'title' => __('Telephone'),
            'edit' => array(
                'required' => true,
                'validators'    => array(
                    array(
                        'name' => 'Module\User\Validator\Telephone',
                    ),
                ),
            ),
        ),

        // Profile:  registered source
        'registered_source'  => array(
            'name'  => 'registered_source',
            'title' => __('Registered source'),
            'is_display'    => false,
            'is_edit'       => false,
        ),

        // Profile: phone
        'phone'  => array(
            'name'  => 'phone',
            'title' => __('Phone'),
            'is_display'    => false,
            'is_edit'       => false,
        ),

        'address' => array(
            'name'  => 'address',
            'title' => __('Address'),
            'edit'  => array(
                'required' => true,
                'class' => 'input-xxlarge',
                'attributes' => array(
                    'class' => 'input-xxlarge'
                )
            ),
        ),

        'postcode' => array(
            'name'  => 'postcode',
            'title' => __('Postcode'),
            'edit' => array(
                'required' => true,
                'validators'    => array(
                    array(
                        'name' => 'Module\User\Validator\Postcode',
                    ),
                ),
            ),
        ),

        'interest' => array(
            'name'  => 'interest',
            'title' => __('Interest'),
            'handler'   => 'Custom\User\Field\Interest',
            'edit'  => array(
                'element'  => 'multi_checkbox',
                'required' => true,
                'attributes' => array(
                    'options' => $interestOpts
                ),
            ),
        ),

        'subscription' => array(
            'name'  => 'subscription',
            'title' => __('Subscriptions'),
            'handler'   => 'Custom\User\Field\Subscription',
            'edit'  => array(
                'element'  => 'multi_checkbox',
                'required' => false,
                'attributes' => array(
                    'options' => $subscriptionOpts
                ),
            ),
        ),

        // Compound fields

        // Compound: Education experiences
        'education'  => array(
            'name'  => 'education',
            'title' => __('Education'),

            // Custom handler
            'handler'   => 'Custom\User\Field\Education',

            // Fields
            'field' => array(
                'school'    => array(
                    'title' => __('School name'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'department'    => array(
                    'title' => __('Department'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'major'    => array(
                    'title' => __('Major'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'degree'    => array(
                    'title' => __('Degree'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                    'edit'  => 'Custom\User\Form\Element\StartTime',
                ),
                'end'    => array(
                    'title' => __('End time'),
                    'edit'  => 'Custom\User\Form\Element\EndTime',
                ),
                'description'   => array(
                    'title' => __('Description'),
                    'edit'  => array(
                        'element' => 'textarea',
                        'attributes' => array(
                            'rows'    => 4,
                            'class'   => 'input-block-level',
                        ),
                    ),
                ),
            ),
        ),

        // Compound: Profession experiences
        'work'      => array(
            'name'  => 'work',
            'title' => __('Work'),
            'is_required' => true,
            // Custom handler
            'handler'   => 'Custom\User\Field\Work',

            // Fields
            'field' => array(
                'company'    => array(
                    'title' => __('Company name'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'department'    => array(
                    'title' => __('Work Department'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'industry'    => array(
                    'title' => __('Industry'),
                    'edit' => array(
                        'element'  => 'Custom\User\Form\Element\Industry',
                        'required' => true,
                    ),
                ),
                'sector'    => array(
                    'title' => __('Sector'),
                    'edit'  => 'hidden',
                ),
                'position'    => array(
                    'title' => __('Job Position'),
                    'edit'  => array(
                        'element'    => 'select',
                        'attributes' => array(
                            'options' => array(
                                ''                  => __('Select'),
                                __('R&D')           => __('R&D'),
                                __('Management')    => __('Management'),
                                __('Measurement')   => __('Measurement'),
                                __('QA')            => __('QA'),
                                __('Market')        => __('Market'),
                                __('Student')       => __('Student'),
                            ),
                        ),
                    ),
                ),
                'title'    => array(
                    'title' => __('Job title'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'description'   => array(
                    'title' => __('Description'),
                    'edit'  => array(
                        'element' => 'textarea',
                        'attributes' => array(
                            'rows'    => 4,
                            'class'   => 'input-block-level',
                        ),
                    ),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                    'edit'  => 'Custom\User\Form\Element\StartTime',
                ),
                'end'    => array(
                    'title' => __('End time'),
                    'edit'  => 'Custom\User\Form\Element\EndTime',
                ),
            ),
        ),
    ),

    // Timeline logs from modules
    'timeline'  => array(
    ),

    // Activity logs
    'activity'  => array(
        // Community bbs
        'community_bbs'    => array(
            'title' => __('Community bbs'),
            'callback'  => 'Custom\User\Activity\CommunityBbs',
            'template'  => 'activity-community-bbs'
        ),

        // CNDZZ
        'cndzz'    => array(
            'title' => __('CNDZZ'),
            'callback'  => 'Custom\User\Activity\Cndzz',
            'template'  => 'activity-cndzz'
        ),

        // Blog
        'Blog'    => array(
            'title' => __('Blog'),
            'callback'  => 'Custom\User\Activity\Blog',
            //'template'  => 'activity-blog'
        ),

        // Community
        'community'    => array(
            'title' => __('Community'),
            'callback'  => 'Custom\User\Activity\Community',
            'template'  => 'activity-community'
        ),
    ),

    // Quicklinks
    'quicklink' => array(
        'eefocus'  => array(
            'title' =>  __('EEFOCUS'),
            'link'  => 'http://www.eefocus.com/',
            'icon'  => 'icon-eefocus',
        ),
        'article'  => array(
            'title' =>  __('Article channel'),
            'link'  => 'http://www.eefocus.com/article/',
        ),
        'blog'  => array(
            'title' =>  __('Blog channel'),
            'link'  => 'http://www.eefocus.com/blog/',
        ),
        'forum'  => array(
            'title' =>  __('Forum'),
            'link'  => 'http://www.eefocus.com/bbs/',
        ),
        'cndzz'  => array(
            'title' =>  __('CNDZZ'),
            'link'  => 'http://www.cndzz.com/',
            'icon'  => 'icon-cndzz',
        ),
        'diagram'  => array(
            'title' =>  __('Diagram'),
            'link'  => 'http://www.cndzz.com/diagram/',
        ),
        'cndzzForum'  => array(
            'title' =>  __('Forum'),
            'link'  => 'http://bbs.cndzz.com/forum.php',
        ),
        'eeboard'  => array(
            'title' =>  __('EEboard'),
            'link'  => 'http://www.eeboard.com/',
            'icon'  => 'icon-eeboard',
        ),
        'kaifaban'  => array(
            'title' =>  __('Kaifaban'),
            'link'  => 'http://www.eeboard.com/category/news/',
        ),
        'eeboardForum'  => array(
            'title' =>  __('Forum'),
            'link'  => 'http://www.eeboard.com/bbs/forum.php',
        ),
        'd5'  => array(
            'title' =>  __('Datasheet 5'),
            'link'  => 'http://www.datasheet5.com/',
            'icon'  => 'icon-d5',
        ),
    ),
);
