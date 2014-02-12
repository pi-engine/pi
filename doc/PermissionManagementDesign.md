## Permission Management Methods
### Based on Role
每个用户只能有一个角色，角色之间可以有继承关系。

![permissionModel1](https://raw.github.com/pi-asset/image/master/permission/permissionModel1.jpg)
### Based on Group(in use)
一个用户可以属于多个组，组之间没有关系。

![permissionModel2](https://raw.github.com/pi-asset/image/master/permission/permissionModel2.jpg)
### Combine of Role and Group
资源组合成组，组之间没有关系。一个或多个组构成一个角色，角色之间可以存在继承关系。每个用户有且只有一个角色，同时可以直接把用户加入组，使用户具有该组规定的权限。

采用这种方式避免了使用角色管理权限时需要单独赋予某用户某项权限时的不便，如果使用角色管理权限在这种情况下需要单独为此用户创建一个角色，现在只需要把用户加入有该权限的组中即可。同时，避免了因为使用组造成的管理不便。

![permissionModel3](https://raw.github.com/pi-asset/image/master/permission/permissionModel3.jpg)

## Permission Management Design
权限管理分为对角色、角色权限、用户角色的管理。角色属于各个网站，由各个网站分别管理。<br>
对角色的管理在operation->system->Role下进行，对用户角色的分配在operationg->user/user client下进行，对权限的管理在setting->permission中。

### Permission Management
对权限的管理在setting->permission中进行，每个模块分别管理自己的权限。<br>
权限按照模块划分，模块内分前后台权限。

前台：<br>
每个模块都有总入口，控制用户是否具有查看和管理两种权限，如果关闭，其他权限依然可以编辑，但是无法生效<br>
各模块权限包括模块自己定义的、callback和区块权限

后台：<br>
后台权限包括operation和setting下的各项权限<br>
其中operation下的权限每个模块分别控制，setting下的权限在system->site中统一控制

可批量分配权限。

### Role Management
角色管理在operation->system->Role下进行。<br>
系统默认有前台角色：Guest、Member、Webmaster，以及后台角色Administrator，这些默认角色都不能删除或禁用。
- Guest角色不允许添加或删除用户，所有用户都不属于这个角色，该角色用于定义非站点会员的权限
- Member为所有注册用户默认具有的角色，不能把用户从该组中移除
- WebMaster和Administrator分别为前后台管理员。

角色分前台角色和后台角色，一个用户可以具有多个角色，角色之间没有继承关系。

功能
- 添加角色 添加前台、后台角色
- 删除角色 只能删除用户创建的角色，系统自带角色不能删除或修改，只能编辑其权限
- 修改角色的title

### User Management
用户角色管理是由各个网站分别控制的，在operationg->user/user client下进行。<br>
**注意：每个站点创建的角色只在该站点内有效**<br>

**User**<br>
功能
- 基本信息查看
- 过滤用户
- 批量赋予、取消角色

**Role**<br>
功能
- 查看站点内角色
- 根据ID/Username/Displayname/Email往角色内添加用户
- 从角色内移除用户
