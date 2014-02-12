User module event list

```

event name:
 login
      controller: login
      action:     process
      params:     $uid, $rememberme(rememberme time)
      code:       Pi::service('event')->trigger('login', array($uid, $rememberme))
 logout
      controller: login
      action:     logout
      params:     $uid
      code  :     Pi::service('event')->trigger('logout', $uid))
 activate
      class:    Module\User\Api\User
      function: activateUser
      params:   $uid
      code:     Pi::service('event')->trigger('activate', $uid)
disable
      class:    Module\User\Api\User
      function: disableUser
      params:   $uid
      code:     Pi::service('event')->trigger('disable', $uid)
enable
      class:    Module\User\Api\User
      function: enableUser
      params:   $uid
      code:     Pi::service('event')->trigger('enable', $uid)
delete
      class:    Module\User\Api\User
      function: deleteUser
      params:   $uid
      code:     Pi::service('event')->trigger('delete', $uid)
update
      class:    Module\User\Api\User
      function: updateAccount
      params:   $uid, $data
      // $data = array('filed1' => $value1, 'filed2' => $value2, ......);
      code:     Pi::service('event')->trigger('update', array($uid, $data))

```