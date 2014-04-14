
# Deployment of Pi Engine with nginx

## Shared common files
- common code root: `/apps/pi/deploy/`
- the common code is symlink'ed from a source code path, for instance `/apps/pi/source/pi-develop`

## pi-demo.org skeleton
- web root: `/apps/pi-demo/www`
- data directory: `/apps/pi-demo/var`
- upload directory: `/apps/pi-demo/upload`
- lib, symlink from common root: `/apps/pi-demo/lib`
- usr, symlink from common root: `/apps/pi-demo/usr`
- static, symlink from common root: `/apps/pi-demo/static`

## Deployment command, for installation and update

### Fetch code package and unzip to `/apps/pi/source/pi-develop`
```
rm -f tmp.zip && wget -qO- -O tmp.zip https://github.com/pi-engine/pi/archive/develop.zip && rm -Rf /apps/pi/source/pi-develop && unzip -qo tmp.zip -d /apps/pi/source && rm tmp.zip
rm -f /apps/pi/deploy && ln -sf /apps/pi/source/pi-develop /apps/pi/deploy
chown -Rf www-data:www-data /apps/pi/deploy/
```

## Installation command, for first deployment only

### Prepare for www `/apps/pi-demo/www`
```
rm -Rf /apps/pi-demo/www && mkdir /apps/pi-demo/www
```

### Copy folders/files to `/apps/pi-demo/www`
```
rm -Rf /apps/pi-demo/www/setup && cp -R /apps/pi/deploy/www/setup /apps/pi-demo/www/
rm -Rf /apps/pi-demo/www/asset/ && cp -R /apps/pi/deploy/www/asset /apps/pi-demo/www/
rm -f /apps/pi-demo/www/.htaccess && cp /apps/pi/deploy/www/.htaccess /apps/pi-demo/www/.htaccess
rm -f /apps/pi-demo/www/boot.php && cp /apps/pi/deploy/www/boot.php /apps/pi-demo/www/boot.php
```

### Symlink files to `/apps/pi-demo/www`
```
rm -Rf /apps/pi-demo/www/public && ln -sf /apps/pi/deploy/www/public /apps/pi-demo/www/public
rm -Rf /apps/pi-demo/www/script && ln -sf /apps/pi/deploy/www/script /apps/pi-demo/www/script
rm -Rf /apps/pi-demo/www/module && ln -sf /apps/pi/deploy/www/module /apps/pi-demo/www/module

rm -f /apps/pi-demo/www/index.php && ln -sf /apps/pi/deploy/www/index.php /apps/pi-demo/www/index.php
rm -f /apps/pi-demo/www/favicon.ico && ln -sf /apps/pi/deploy/www/favicon.ico /apps/pi-demo/www/favicon.ico
rm -f /apps/pi-demo/www/robots.txt && ln -sf /apps/pi/deploy/www/robots.txt /apps/pi-demo/www/robots.txt

rm -f /apps/pi-demo/www/admin.php && ln -sf /apps/pi/deploy/www/admin.php /apps/pi-demo/www/admin.php
rm -f /apps/pi-demo/www/api.php && ln -sf /apps/pi/deploy/www/api.php /apps/pi-demo/www/api.php
rm -f /apps/pi-demo/www/app.php && ln -sf /apps/pi/deploy/www/app.php /apps/pi-demo/www/app.php
rm -f /apps/pi-demo/www/feed.php && ln -sf /apps/pi/deploy/www/feed.php /apps/pi-demo/www/feed.php
```

### Symlink paths
```
rm -f /apps/pi-demo/lib && ln -sf /apps/pi/deploy/lib /apps/pi-demo/lib
rm -f /apps/pi-demo/usr && ln -sf /apps/pi/deploy/usr /apps/pi-demo/usr
rm -f /apps/pi-demo/static && ln -sf /apps/pi/deploy/www/static /apps/pi-demo/static
```

### Copy folders
```
rm -Rf /apps/pi-demo/var && cp -R /apps/pi/deploy/var /apps/pi-demo
rm -Rf /apps/pi-demo/upload && cp -R /apps/pi/deploy/www/upload /apps/pi-demo
```

### Change owner/group
```
chown -Rf www-data:www-data /apps/pi-demo/
```

### Write mode
```
chmod -f 0777 /apps/pi-demo/www/.htaccess
chmod -f 0777 /apps/pi-demo/www/boot.php
chmod -Rf 0777 /apps/pi-demo/www/asset/
chmod -Rf 0777 /apps/pi-demo/var/
chmod -Rf 0777 /apps/pi-demo/upload/
````

### Clear folders/files after installation
```
chmod -f 0444 /apps/pi-demo/www/.htaccess
chmod -f 0444 /apps/pi-demo/www/boot.php
rm -Rf /apps/pi-demo/www/setup
````


-----------
By [@taiwen](https://github.com/taiwen)

2014-02-08