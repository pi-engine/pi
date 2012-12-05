# USING THE GIT REPOSITORY
### Currently the repository is still in private, DON'T fork to a public repo.

 1. Setup a GitHub account (http://github.com/), if you haven't yet
 2. Clone the repo locally and enter it (use your own GitHub username
    in the statement below)

    ```sh
    % git clone https://github.com/taiwen/pi.git
    % cd framework
    ```


### Pre-Commit Hook (Optional)

The Pi Engine Travis-CI will confirm that code style standards are met
by using ```php-cs-fixer``` (https://github.com/fabpot/PHP-CS-Fixer) during it's build runs.

To reduce the number of red Travis-CI builds, the following Git pre-commit hook
can help catch code style issues before committing. Save it as
```.git/hooks/pre-commit```, and make sure it is executable.

```php
#!/usr/bin/env php
<?php
/**
 * .git/hooks/pre-commit
 *
 * This pre-commit hooks will check for PHP errors (lint), and make sure the
 * code is PSR-2 compliant.
 *
 * Dependecy: PHP-CS-Fixer (https://github.com/fabpot/PHP-CS-Fixer)
 *
 * @author  Mardix  http://github.com/mardix
 * @author  Matthew Weier O'Phinney http://mwop.net/
 * @since   4 Sept 2012
 */

$exit = 0;

/*
 * collect all files which have been added, copied or
 * modified and store them in an array called output
 */
$output = array();
exec('git diff --cached --name-status --diff-filter=ACM', $output);

foreach ($output as $file) {
    if ('D' === substr($file, 0, 1)) {
        // deleted file; do nothing
        continue;
    }

    $fileName = trim(substr($file, 1));

    /*
     * Only PHP files
     */
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!preg_match('/^ph(p|tml)$/', $extension)) {
        continue;
    }

    /*
     * Check for parse errors
     */
    $output = array();
    $return = 0;
    exec("php -l " . escapeshellarg($fileName), $output, $return);

    if ($return != 0) {
        echo "PHP file fails to parse: " . $fileName . ":" . PHP_EOL;
        echo implode(PHP_EOL, $lintOutput) . PHP_EOL;
        $exit = 1;
        continue;
    }

    /*
     * PHP-CS-Fixer
     */
    $output = array();
    $return = null;
    exec("php-cs-fixer --dry-run --level=psr2 " . escapeshellarg($fileName), $output, $return);
    if ($return != 0 || !empty($output)) {
        echo "PHP file fails contains CS issues: " . $fileName . ":" . PHP_EOL;
        echo implode(PHP_EOL, $output) . PHP_EOL;
        $exit = 1;
        continue;
    }
}

exit($exit);
```
