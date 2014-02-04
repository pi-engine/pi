
#Pi Engine Config Usage

* Get a system config
```
$value = Pi::config(<name>);
```

* Get a config of a module
```
$value = Pi::config(<name>, <module>);
```

* Get a category of system configs
```
$values = Pi::config('', '', <categoryOrDomain>);
```

* Get a category of configs of a module
```
$values = Pi::config('', <module>, <categoryOrDomain>);
```

* Get all system configs
```
$values = Pi::config('');
```

* Get all configs of a module
```
$values = Pi::config('', <module>);
```

* Set a system config
`Pi::config()->set(<name>, <value>);
```

* Set a system config to a category
`Pi::config()->set(<name>, <value>, <category>);
```

* Set a system config to a category
`Pi::config()->set(<name>, <value>, <category>);
```

* Set system configs to a category
```
Pi::config()->set(<configs>, <category>);
Pi::config()->setDomain(<configs>, <category>);
```

* Unset system configs from a category
`Pi::config()->unsetDomain(<category>);
```

* Load system configs to a category
```
Pi::config()->loadDomain(<category>);
```

* Load configs from a file, checking `var/config/custom/<file>` then `var/config/<file>`
```
$configs = Pi::config()->load(<file-name>);
```

* Load configs from a file, only from `var/config/<file>`
```
$configs = Pi::config()->load(<file-name>, false);
```

* Write configs to `var/config/<file>`
```
Pi::config()->load(<[data]>, <file-name>);
```

* Write configs to `var/config/custom/<file>`
```
Pi::config()->load(<[data]>, <file-name>, true);
```