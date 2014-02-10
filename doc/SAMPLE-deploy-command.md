
# Deployment of Pi Engine with nginx

## Shared common files
- common code root: `/home/pi/common/code/deploy/`
- the common code is symlink'ed from a source code path, for instance `/home/pi/code/pi-release-2.4.0`

## pi-demo.org skeleton
- web root: `/home/pi/deploy/pi-demo/www`
- data directory: `/home/pi/deploy/pi-demo/var`
- asset directory: `/home/pi/deploy/pi-demo/asset`
- upload directory: `/home/pi/deploy/pi-demo/upload`
- lib, symlink from common root: `/home/pi/deploy/pi-demo/lib`
- usr, symlink from common root: `/home/pi/deploy/pi-demo/usr`
- static, symlink from common root: `/home/pi/deploy/pi-demo/static`

## Deployment command, for installation and update

### Fetch code package and unzip to `/home/pi/common/code/pi-develop`
```
rm -f tmp.zip && wget -qO- -O tmp.zip https://github.com/pi-engine/pi/archive/develop.zip && rm -Rf /home/pi/common/code/pi-develop && unzip -qo tmp.zip -d /home/pi/common/code && rm tmp.zip
rm -f /home/pi/common/code/deploy && ln -sf /home/pi/common/code/pi-develop /home/pi/common/code/deploy
chown -Rf www-data:www-data /home/pi/common/code/deploy/
```

## Installation command, for first deployment only

### Prepare for www `/home/pi/deploy/pi-demo/www`
```
rm -Rf /home/pi/deploy/pi-demo/www && mkdir /home/pi/deploy/pi-demo/www
```

### Copy folders/files to `/home/pi/deploy/pi-demo/www`
```
rm -Rf /home/pi/deploy/pi-demo/www/setup && cp -R /home/pi/common/code/deploy/www/setup /home/pi/deploy/pi-demo/www/setup
rm -Rf /home/pi/deploy/pi-demo/www/public/ && cp -R /home/pi/common/code/deploy/www/public /home/pi/deploy/pi-demo/www/
rm -f /home/pi/deploy/pi-demo/www/.htaccess && cp /home/pi/common/code/deploy/www/.htaccess /home/pi/deploy/pi-demo/www/.htaccess
rm -f /home/pi/deploy/pi-demo/www/boot.php && cp /home/pi/common/code/deploy/www/boot.php /home/pi/deploy/pi-demo/www/boot.php
```

### Symlink files to `/home/pi/deploy/pi-demo/www`
```
rm -Rf /home/pi/deploy/pi-demo/www/script && ln -sf /home/pi/common/code/deploy/www/script /home/pi/deploy/pi-demo/www/script
rm -Rf /home/pi/deploy/pi-demo/www/module && ln -sf /home/pi/common/code/deploy/www/module /home/pi/deploy/pi-demo/www/module
rm -f /home/pi/deploy/pi-demo/www/index.php && ln -sf /home/pi/common/code/deploy/www/index.php /home/pi/deploy/pi-demo/www/index.php
rm -f /home/pi/deploy/pi-demo/www/favicon.ico && ln -sf /home/pi/common/code/deploy/www/favicon.ico /home/pi/deploy/pi-demo/www/favicon.ico
rm -f /home/pi/deploy/pi-demo/www/robots.txt && ln -sf /home/pi/common/code/deploy/www/robots.txt /home/pi/deploy/pi-demo/www/robots.txt

rm -f /home/pi/deploy/pi-demo/www/admin.php && ln -sf /home/pi/common/code/deploy/www/admin.php /home/pi/deploy/pi-demo/www/admin.php
rm -f /home/pi/deploy/pi-demo/www/api.php && ln -sf /home/pi/common/code/deploy/www/api.php /home/pi/deploy/pi-demo/www/api.php
rm -f /home/pi/deploy/pi-demo/www/app.php && ln -sf /home/pi/common/code/deploy/www/app.php /home/pi/deploy/pi-demo/www/app.php
rm -f /home/pi/deploy/pi-demo/www/feed.php && ln -sf /home/pi/common/code/deploy/www/feed.php /home/pi/deploy/pi-demo/www/feed.php
```


### Symlink paths
```
rm -f /home/pi/deploy/pi-demo/lib && ln -sf /home/pi/common/code/deploy/lib /home/pi/deploy/pi-demo/lib
rm -f /home/pi/deploy/pi-demo/usr && ln -sf /home/pi/common/code/deploy/usr /home/pi/deploy/pi-demo/usr
rm -f /home/pi/deploy/pi-demo/static && ln -sf /home/pi/common/code/deploy/www/static /home/pi/deploy/pi-demo/static
```

### Copy folders
```
rm -Rf /home/pi/deploy/pi-demo/var && cp -R /home/pi/common/code/deploy/var /home/pi/deploy/pi-demo
rm -Rf /home/pi/deploy/pi-demo/asset && cp -R /home/pi/common/code/deploy/www/asset /home/pi/deploy/pi-demo
rm -Rf /home/pi/deploy/pi-demo/upload && cp -R /home/pi/common/code/deploy/www/upload /home/pi/deploy/pi-demo
```

### Change owner/group
```
chown -Rf www-data:www-data /home/pi/deploy/pi-demo/
```

### Write mode
```
chmod -f 0777 /home/pi/deploy/pi-demo/www/.htaccess
chmod -f 0777 /home/pi/deploy/pi-demo/www/boot.php
chmod -Rf 0777 /home/pi/deploy/pi-demo/www/public/
chmod -Rf 0777 /home/pi/deploy/pi-demo/var/
chmod -Rf 0777 /home/pi/deploy/pi-demo/asset/
chmod -Rf 0777 /home/pi/deploy/pi-demo/upload/
````

### Clear folders/files after installation
```
chmod -f 0444 /home/pi/deploy/pi-demo/www/.htaccess
chmod -f 0444 /home/pi/deploy/pi-demo/www/boot.php
rm -Rf /home/pi/deploy/pi-demo/www/setup
````


-----------
By [@taiwen](https://github.com/taiwen)

2014-02-08