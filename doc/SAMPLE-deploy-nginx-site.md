# nginx conf sample for Pi Engine Installation

## Assumption
- site: `pi-demo.org`
- conf: `pi-demo.conf`
- check `SAMPLE-deploy-command.md` for skeleton

## pi-demo main site
```
server {
    listen 80;
    # Change the server_name according to your applications
    server_name pi-demo.org www.pi-demo.org;
    # Change the root according to your applications
    root /apps/pi-demo/www;
    index index.html index.php index.htm;

    # Usually you don't need to change the following settings
    # Dispatch to Pi Engine entry
    # Admin subdoamin
    if ($host ~* ^admin\.(.*)$) {
        rewrite ^(.*)$      /admin.php   last;
        break;
    }
    # API subdoamin
    if ($host ~* ^api\.(.*)$) {
        rewrite ^(.*)$      /api.php   last;
        break;
    }
    # Feed subdomain
    if ($host ~* ^feed\.(.*)$) {
        rewrite ^(.*)$      /feed.php   last;
        break;
    }
    # Widget subdoamin
    if ($host ~* ^widget\.(.*)$) {
        rewrite ^(.*)$      /widget.php   last;
        break;
    }
    if (!-e $request_filename) {
        # Admin route
        rewrite ^/admin(/(.*)?)?$   /admin.php   last;
        # API route
        rewrite ^/api(/(.*)?)?$     /api.php    last;
        # Feed route
        rewrite ^/feed(/(.*)?)?$    /feed.php   last;
        # Widget route
        rewrite ^/widget(/(.*)?)?$  /widget.php last;
        # Default app route
        rewrite ^/(.+)$             /app.php    last;
        break;
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    location ~ \.php$ {
        # For FastCGI mode
        #fastcgi_pass   127.0.0.1:9000;
        # For php-fpm mode
        fastcgi_pass   unix:/var/run/php5-fpm.sock;
        fastcgi_index  index.php;
        include  fastcgi_params;
    }

    # Static files.
    # Set expire headers, Turn off access log
    location ~* \.(jpg|jpeg|gif|png|ico|css|js|html|htm|ttf|otf|eot|woff)$ {
        access_log off;
        expires max;
        add_header Cache-Control public;
    }
}
```

## pi-demo static
```
server {
    listen 80;
    # Change the server_name according to your applications
    server_name static.pi-demo.org;
    # Change the root according to your applications
    root /apps/pi-demo/static;
    index index.html index.htm;

    access_log off;
    expires max;
    add_header Cache-Control public;
}
```

## pi-demo upload
```
server {
    listen 80;
    # Change the server_name according to your applications
    server_name upload.pi-demo.org;
    # Change the root according to your applications
    root /apps/pi-demo/upload;
    index index.html index.htm;

    access_log off;
    expires max;
    add_header Cache-Control public;
}
```


-----------
By [@taiwen](https://github.com/taiwen)

2014-02-08