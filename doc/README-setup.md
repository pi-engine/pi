
Pi Engine Setup

## Single site
1. Empty `setup/tmp/` and make it writable;
2. Access `setup/index.php` to start installation;
3. After the installation, remove `setup` folder.

## Multi-site
1. Install master site: install as `master` from `setup/master.php`
2. Remove `setup/master.php`
3. Install slave site: install as `slave` from `setup/slave.php`