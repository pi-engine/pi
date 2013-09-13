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
