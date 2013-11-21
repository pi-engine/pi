<?php
namespace Module\Account\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class ApiController extends ActionController
{
    public function getProfileAction()
    {
        $uid    = $this->params('id');
        $data   = $this->getUserinfo($uid, array('basic','work'));

        $fields = array(
            'user',
            'real_name',
            'email',
            'tel',
            'phone',
            'country',
	    'province',
            'city',
            'zip',
            'address',
            'company',
            'department',
            'position_title',
            'position',
            'industry',
            'sphere'
        );
        $this->sendData($this->formatUserinfo($data, $fields));
    }

    public function getProfilesAction()
    {
        $uids    = explode(',', $this->params('ids'));
        $result = $this->getUserinfo($uids, array('basic','work','interest','login'));

        $fields = array(
            'user',
            'real_name',
            'email',
            'username',
            'nick_name',
            'gender',
            'register_time',
            'active_time',
            'modify_time',
            'login_count',
            'tel',
            'phone',
            'country',
            'province',
            'city',
            'zip',
            'address',
            'company',
            'department',
            'position_title',
            'position',
            'industry',
            'sphere',
            'interests',
        );

        $data    = array();
        if ($result) {
            foreach ($uids as $uid) {
                $data[$uid] = isset($result[$uid]) ? $this->formatUserinfo($result[$uid], $fields) : array();
            }
        }
        $this->sendData($data);
    }

    public function getUserAction()
    {
        $uid    = $this->params('id');
        $data   = $this->getUserinfo($uid, array('basic','login'));

        if ($data) {
            $data['avatar'] = Pi::service('avatar')->get($uid, 'xxlarge', false);
        }

        $fields = array(
            'user',
            'nick_name',
            'username',
            'old_username',
            'old_email',
            'email',
            'avatar',
            'login_count',
            'register_time',
            'login_time',
            'introduce',
            'gender',
        );

        $this->sendData($this->formatUserinfo($data, $fields));
    }

    public function setProfileAction()
    {
        $fields = array(
            'real_name',
            'tel',
            'phone',
            'country',
            'province',
            'city',
            'zip',
            'address',
            'company',
            'department',
            'position_title',
            'position',
            'industry',
            'sphere',
        );

        $param  = $this->params()->fromPost();
        $uid    = $param['id'];
        $post   = $this->formatUserinfo($param, $fields);

        $data       = array();
        if (!empty($uid)) {
            $metadata   = $this->getMetadata();
            foreach ( $metadata as $type => $value ) {
                foreach ( $value as $k => $v ) {
                    if (isset($post[$v])) {
                        if ($type == 'basic') {
                            $data[$k]   = $post[$v];
                        } else {
                            $data[$type][0][$k] = $post[$v];
                        }
                    }
                }
            }
        }

        if ($data) {
            Pi::api('user','user')->updateUser($uid, $data);
        }

        $this->sendData($uid);
    }

    public function registerAction()
    {
        $fields = array(
            'username',
            'nick_name',
            'gender',
            'email',
            'password',
            'real_name',
            'tel',
            'phone',
            'country',
            'province',
            'city',
            'zip',
            'address',
            'company',
            'department',
            'position_title',
            'position',
            'industry',
            'sphere',
        );

        $pid    = $this->params('pid');
        $param  = $this->params()->fromPost();
        $post   = $this->formatUserinfo($param, $fields);

        $data       = array();
        if (!empty($post)) {
            $metadata   = $this->getMetadata();
            foreach ( $metadata as $type => $value ) {
                foreach ( $value as $k => $v ) {
                    if (isset($post[$v])) {
                        if ($type == 'basic') {
                            $data[$k]   = $post[$v];
                        } else {
                            $data[$type][0][$k] = $post[$v];
                        }
                    }
                }
            }
        }

        $ret    = NULL;
        if ($data) {
            $data['registered_source']  = $pid;
            if ($data['gender']=='M') {
                $data['gender'] = 'male';
            } elseif  ($data['gender']=='F') {
                $data['gender'] = 'female';
            } else {
                $data['gender'] = 'unknown';
            }
            $uid    = Pi::api('user','user')->addUser($data);
            if (is_scalar($uid)) {
                $ret    = array(
                    'id'        =>  $uid,
                    'username'  => $data['identity'],
                    'email'     => $data['email'],
                );
            }
        }

        $this->sendData($ret);
    }

    public function checkUserNameExistsAction()
    {
        $username    = $this->params('username');
        $user   = Pi::api('user','user')->getUser($username, 'identity');

        $this->sendData( empty($user) ? 0 : 1 );
    }

    public function checkUserEmailExistsAction()
    {
        $email  = $this->params('email');
        $user   = Pi::api('user','user')->getUser($email, 'email');

        $this->sendData( empty($user) ? 0 : 1 );
    }

    public function loginAction()
    {
        $post     = $this->params()->fromPost();
        $username = isset($post['username'])   ? $post['username']   : '';
        $password = isset($post['password'])   ? $post['password']   : '';

        $isEmail    = strpos($username, '@') !== false;
        $field      = $isEmail ? 'email' : 'identity';

        $userlist       = Pi::api('user','user')->getList(array($field=>$username),0,0,'', array('identity','credential','email','name'));
        $userinfo       = array_shift($userlist);
        $userinfo       = Pi::model('account','user')->find($userinfo['id'],'id');
        $isAuth         = false;
        $rawpwd         = NULL;
        if ($userinfo) {
            // now
            $site_salt  = Pi::config('salt');
            if ($userinfo['credential'] == md5($userinfo['salt'] . $password . $site_salt)) {
                $isAuth = true;
            }

            // old account
            if (!isAuth) {
                $salt   = '';
                if ($userinfo['credential'] == md5($userinfo['salt'] . $password)) {
                    $rawpwd = $password;
                    $isAuth = true;
                }
            }

            // old eefocus
            if (!isAuth) {
                $salt   = '';
                if ($userinfo['credential'] == md5($password)) {
                    $rawpwd = $password;
                    $isAuth = true;
                }
            }

            // old cndzz
            if (!isAuth) {
                $salt   = '';
                if ($userinfo['credential'] == substr(md5($password), 8, 16)) {
                    $rawpwd = $password;
                    $isAuth = true;
                }
            }
        }

        if ($rawpwd) {
            Pi::api('user','user')->updateUser($userinfo['id'], array('credential'=>$rawpwd));
        }

        $data   = !$isAuth ? array() : array(
            'id'                => $userinfo['id'],
            'username'          => $userinfo['identity'],
            'nickname'          => $userinfo['name'],
            'authentication'    => true
        );


        $this->sendData($data);
    }

    public function activeAction()
    {
        $uid    = $this->params('id');
        $ret    = Pi::api('user','user')->activateUser($uid);

        $this->sendData( empty($ret) ? 0 : 1 );
    }

    public function getLoginInformationAction()
    {
        $post   = $this->params()->fromPost();
        $uid    = isset($post['id']) ? $post['id'] : '';

        $data   = array();
        if ($uid) {
            $data   = $this->getUserinfo($uid, array('basic','login'));

            if ($data) {
                $data['id'] = $data['user'];
                $fields = array(
                    'id',
                    'login_count',
                    'register_time',
                    'login_time',
                );

                $data   = $this->formatUserinfo($data, $fields);
            }
        }

        $this->sendData($data);
    }

    // Change username
    public function changeNameAction()
    {
        $uid        = $this->params('id');
        $username   = $this->params('newname');

        $status = Pi::api('user','user')->updateUser($uid, array('identity'=>$username));
        $this->sendData( $status === true ? 1 : 0 );
    }

    public function getBaseInfoAction()
    {
        $uid    = $this->params('id');
        $data   = $this->getUserinfo($uid, array('basic','work','login'));
        if ($data) {
            $data['avatar'] = Pi::service('avatar')->get($uid, 'xxlarge', false);
            $data['mini_avatar'] = Pi::service('avatar')->get($uid, 'normal', false);
        }

        $fields = array(
            'nick_name',
            'gender',
            'login_time',
            'register_time',
            'introduce',
            'avatar',
            'mini_avatar'
        );

        $this->sendData($this->formatUserinfo($data, $fields));
    }

    public function setPasswordAction()
    {
        $id       = $this->params('uid');
        $password = $this->params('password');
        $code     = $this->params('code');
        $key      = 'eefocus!$^cndzz@#&';

        $status = false;
        if ($id && $password && $code == md5($id . $password . $key)) {
            $status = Pi::api('user','user')->updateUser($id, array('credential'=>$password));
        }

        $this->sendData( $status === true ? 'true' : 'false' );
    }


    private function sendData( $data ) {
        echo json_encode($data);
        exit();
    }

    private function formatUserinfo( $result, $fields=array() ) {
        static $interest    = array();

        if (empty($interest)) {
            $optionsFile = sprintf('%s/%s/config/label.php', Pi::path('module'), $this->getModule());
            $options     = include $optionsFile;
            $interest    = $options['interest'];
        }

        $ret    = array();

        if (!empty($result)) {
            foreach ($fields as $val) {
                if ($val=='gender') {
                    if ($result[$val]=='male') {
                        $result[$val]   = 'M';
                    } else {
                        $result[$val]   = 'F';
                    }
                }
                if ($val=='interests' && is_array($result[$val])) {
                    $tmp    = array();
                    foreach ($result[$val] as $v) {
                        $tmp[$v]    = isset($interest[$v]) ? $interest[$v] : '';
                    }
                    $result[$val]   = $tmp;
                }
                $ret[$val]  = isset($result[$val]) ? $result[$val] : '';
            }
        }

        return $ret;
    }

    private function getUserinfo($uid, $type)
    {
        if (!$uid || !type) {
            return false;
        }

        $uids    = (array) $uid;
        $type   = (array) $type;

        $result = array();
        $meta   = $this->getMetadata();
        if (in_array('basic', $type)) {
            $rs_basic   = Pi::api('user','user')->get($uids, array_keys($meta['basic']));
            foreach ($rs_basic as $key=>$val) {
                $tmp    = array();
                foreach ($val as $k=>$v) {
                    if (isset($meta['basic'][$k])) {
                        $tmp[$meta['basic'][$k]]    = $v;
                    }
                }
                $result[$key]   = array_merge(isset($result[$key])?$result[$key]:array(), $tmp);
            }
        }
        if (in_array('work', $type)) {
            $rs_work    = Pi::api('user','user')->get($uids, 'work');
            foreach ($rs_work as $key=>$val) {
                $tmp    = array();
                foreach ($val[0] as $k=>$v) {
                    if (isset($meta['work'][$k])) {
                        $tmp[$meta['work'][$k]]    = $v;
                    }
                }
                $result[$key]   = array_merge(isset($result[$key])?$result[$key]:array(), $tmp);
            }
        }
        if (in_array('login', $type)) {
            $rs_count_login    = Pi::user()->data()->get($uids, 'count_login');
            $rs_last_login    = Pi::user()->data()->get($uids, 'last_login');

            $tmp    = array();
            if ($rs_count_login) {
                foreach ($rs_count_login as $key=>$val) {
                    $tmp[$key]['login_count']   = $val;
                }
            }
            if ($rs_last_login) {
                foreach ($rs_last_login as $key=>$val) {
                    $tmp[$key]['login_time']    = $val;
                }
            }
            foreach ($tmp as $key=>$val) {
                $result[$key]   = array_merge(isset($result[$key])?$result[$key]:array(), $val);
            }
        }
        if (in_array('interest', $type)) {
            $rs_interest    = Pi::api('user','user')->get($uids, 'interest');
            foreach ($rs_interest as $key=>$val) {
                $result[$key]   = array_merge(isset($result[$key])?$result[$key]:array(), array('interests'=>$val));
            }
        }

        if (is_scalar($uid)) {
            $result = isset($result[$uid]) ? $result[$uid] : array();
        }

        return $result;
    }

    private function getMetadata()
    {
        return array(
            'basic'     => array(
                'id'                => 'user',
                'identity'          => 'username',
                'name'              => 'nick_name',
                'fullname'          => 'real_name',
                'email'             => 'email',
                'gender'            => 'gender',
                'time_created'      => 'register_time',
                'time_activated'    => 'active_time',
                'time_modify'       => 'modify_time',
                'tel'               => 'tel',
                'telephone'         => 'phone',
                'country'           => 'country',
                'province'          => 'province',
                'city'              => 'city',
                'postcode'           => 'zip',
                'address'           => 'address',
                'signature'         => 'introduce',
                'last_login'        => 'login_time',
                'count_login'       => 'login_count',
                'signature'         => 'introduce',
                'registered_source' => 'from',
            ),
            'work'      => array(
                'company'       => 'company',
                'department'    => 'department',
                'title'         => 'position_title',
                'position'      => 'position',
                'sector'        => 'industry',
                'industry'      => 'sphere',
            ),
            'interest'  => array(

            ),
        );
    }
}