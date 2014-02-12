# User module custom develop guid

## Custom for profile fields
* Original config: `usr/module/user/config/user.php`
* Custom config: `usr/custom/module/user/config/user.php`

## Custom for forms
### Register form
* Original form config: `usr/module/user/config/register.php`
* Custom form config: `usr/custom/module/user/config/register.php`

### Register complete form
* The form is optional, to enable it, check config `require_register_complete`
* Original form config: `usr/module/user/config/register-complete.php`
* Custo form config: `usr/custom/module/user/config/register-complete.php`

### Profile complete form
* The form is optional, to enable it, check config `require_profile_complete`
* Original form config: `usr/module/user/config/profile-complete.php`
* Custom form config: `usr/custom/module/user/config/profile-complete.php`

## Custom for display templates
* Original template: `usr/module/user/template/front/<template>.phtml`
* Custom template: `usr/custom/module/user/template/front/<template>.phtml`


## User 模块定制开发整理
### 定制说明
* 字段定制
* 模板定制
* 特殊表单元素元素定制
* 程序流程定制
* 邮件模板定制
* 邮件发送方式定制

### 字段定制
* 字段定制主要分为两种：
  * 常规字段
  * 组合字段
* 定制方法

```
######## 普通字段定制 ########
配置文件位置：usr/custom/user/config/user.php
在user.php添加如下代码
```

```
return array(
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
);
```

```
说明：上面代码定制了两个普通字段，安装模块后将在user_porfile表中增加fullname和language字段
1、name ： 字段名
2、title：字段title
3、edit 用户指定字段的验证方式和表单元素定义 可以指定如下属性：
     required => true 指定该字段为必填字段
     class: 指定表单元素的类
     validators：元素验证
     attributes：表单元素附加属性，如select元素的option选项
4、is_display：字段是否可展示
5、is_edit：字段是否可编辑
6、handler：指定处理字段的类
```

```
复合字段的定制：在user.php添加下面代码
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
说明：以上代码添加了education复合字段
复合字段可以定义：
name：复合字段名称
title：显示用的title
handler：字段处理的handler
```

### 模板定制
说明：模板定制解决在不修改模块模板的情况下定制模板

方法：
 * 在usr/custom/module/user/template/front 中phtml将会覆盖模块的中同名phtml文件
 * 在usr/custom/module/user/template/front 中phtml将会覆盖模块的中同名phtml文件

### 特殊表单元素定制
* 说明：复杂交互的表单元素，如联动效果等，可以通过view helper 来实现
* 方法：

```
1、在user.php 中指定element 如：
'edit' => array(
       'element'  => 'Custom\User\Form\Element\Industry',
       'required' => true,
),
2、在usr/custom/module/user/Form/Element/ 创建Industry类
class Industry extends Element
{
    /**
     * Seed attributes
     * @var array
     */
    protected $attributes = array(
        'type'  => 'Custom\User\Form\View\Helper\Industry',
    );
}
3、在usr/custom/module/user/Form/View/Helper 目录下创建Industry类
class Industry extends AbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|self
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $this->view->jQuery();
        $this->view->plugin('js')->load(
            Pi::url('static/custom/js/eefocus-linkage.min.js')
        );
        $id = md5(uniqid());

        $html = <<<'EOT'
        <div id="%s" data-value="%s">
        </div>
        <script>
            new eefocus.Linkage("%s", ["industry", "sector"]);
        </script>
EOT;

        return sprintf(
            $html,
            $id,
            $element->getValue(),
            $id
        );
    }
}
这样就能在render里面写要返回的html的代码
```

### 程序流程定制
* 说明： 通用user模块注册流程只有一步，User模块在定制的时候将注册流程定制为两步
 * 填写基本的账号信息
 * 填写详细的账号信息
* 定制方法：

```
1、定制表单
在usr/custom/module/user/comfig 下面创建
* eefocus.register.php 文件 定义第一步注册的表单
return array(
    // Use user module field
    'gender',
    'birthdate',
    'address',
    'postcode',
    'subscription'
);
上面代码定义了第一步注册表单中要出现的字段
* eefocus.register.complete.php 定义第二步注册的表单
return array(
    // Use user module field
    'fullname',
    'telephone',
    'country',
    'province',
    'city',
    'work',
    'interest',
    'email' => array(
        'element' => array(
            'name'  => 'email',
            'type'  => 'hidden',
        )
    ),
    'identity' => array(
        'element' => array(
            'name' => 'identity',
            'type' => 'hidden',
        )
    ),
    'credential' => array(
        'element' => array(
            'name' => 'credential',
            'type' => 'hidden',
        )
    ),
    'name' => array(
        'element' => array(
            'name' => 'name',
            'type' => 'hidden',
        )
    ),
    'registered_source' => array(
        'element' => array(
            'name' => 'registered_source',
            'type' => 'hidden',
        )
    ),
);
上面代码定义了第二步注册表单中要出现的字段，其中需要将第一步定义的字段在第二步配置文件中设置为hidden
2、定制模块配置参数
在模块配置参数中加入两个参数
'register_form' => array(
        'title'         => _t('Register form config file name'),
        'description'   => _t('Set it only if necessary.'),
        'value'         => '', // 'register',
    ),
'register_complete_form' => array(
        'title'         => _t('Register complete form config file name'),
        'description'   => _t('Set it only if necessary.'),
        'value'         => '',
),
上面定义了注册表单模板配置文件，如果在后台设置了值，注册流程将走定制的流程
同样，还可以定制完善信息页面，定制方法和注册流程一致
```

### 邮件模板定制
说明：通用User模块的邮件模板位于: local/en/mail 分为4个模板
* 激活邮件模板：activity-mail-html.txt
* 找回密码邮件模板：find-password-mail-html.txt
* 修改邮箱通知邮件模板：reset-email-confirm-html.txt
* 修改邮箱验证信息模板：reset-email-html.txt
下面用激活邮件模板作为例子说明：

```
[comment]Pi user system change email confirm mail[/comment]
[format]html[/format]
[subject]Pi user system change email confirm mail[/subject]
[body]
<div style="margin-bottom: 5px">Dear <strong>%username%</strong>:</div>
<div style="margin: 5px">You email reset %old_email% to %new_email%</div>
<div style="margin-top: 10px">
    %site_adminname%<br />
    %site_adminmail%<br />
    <a href="%site_url%" title="%site_name%">%site_name%</a>
    <small>sn: %sn%</small>
</div>
[/body]
%%中间的是变量，其他信息可以自定义，最后解析为html模板，比如%username%是注册的用户名
```
在定制邮件模板的时候，在usr/custom/user/locale/en/mail 中创建相同的文件即可覆盖通用模块的邮件模板

### 邮件发送方式定制
邮件发送方式支持sendmail，smtp方式，具体配置信息在var/config/service.mail.php 中配置
如果要定制邮件发送方式，可以在var/config/custom/ 下面创建service.mail.php文件既可覆盖默认配置
