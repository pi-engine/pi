# Contributing

We welcome bug fixes and patches from 3rd party contributors. Please see the [Contributor Agreement](https://www.arangodb.com/documents/cla.pdf) for details.
Please follow these guidelines if you want to contribute to ArangoDB-PHP:


## Reporting Bugs

When reporting bugs, please use our issue tracker on GitHub.
Please make sure to include the version number of ArangoDB and ArangoDB-PHP in your bug report, along with the platform you are using (e.g. `Linux OpenSuSE x86_64`, `PHP 7.0.14`).
Please also include any special configuration.
This will help us reproducing and finding bugs.

Please also take the time to check there are no similar/identical issues open yet.



## Contributing features, documentation, tests

__Important: You must use the Apache License for your changes and have signed our [CLA](https://www.arangodb.com/documents/cla.pdf). We cannot accept pull requests from contributors that did not sign the CLA.__

* Create a new branch in your fork, based on the devel branch
* Develop and test your modifications there
* Commit as you like, but preferably in logical chunks. Use meaningful commit messages and make sure you do not commit unnecessary files. It is normally a good idea to reference the issue number from the commit message so the issues will get updated automatically with comments
* Make sure the ArangoDB version is the correct one to use with your client version. The client version follows the ArangoDB version. [More info on this, here.](https://github.com/arangodb/ArangoDB-PHP/wiki/Important-versioning-information-on-ArangoDB-PHP)
* Make sure the unmodified clone works locally before making any code changes. You can do so by running the included test suite (phpunit --testsuite ArangoDB-PHP)
* If the modifications change any documented behavior or add new features, __please document the changes in the PHPDOC section__ of the method(s) and/or class(es). We recently agreed that future documentation should be written in American English (AE).
* If a feature was added, please also write accompanying tests.
* When done, run the complete test suite and make sure all tests pass
* When finished, push the changes to your GitHub repository and send a pull request from your fork to the ArangoDB-PHP repository's **devel** branch.
* Please let us know if you plan to work on an issue. This way we can make sure we avoid redundant work
* For feature requests: please clearly describe the proposed feature, additional configuration options, and side effects



## Generating documentation
(This should only be done prior to tagged releases, by release managers, in order to not excessively recreate all documentation files for small changes in the devel branch)

Documentation is generated with the apigen generator with the following parameters (beside the source and destination definition):

```
--template-theme bootstrap --title "ArangoDB-PHP API Documentation" --deprecated
```


Example:
```
php -f apigen.phar generate -s ./lib/ArangoDBClient -d ./docs --template-theme bootstrap --title "ArangoDB-PHP API Documentation" --deprecated
```


## Additional Resources

* [ArangoDB website](https://www.arangodb.com/)
* [ArangoDB on Twitter](https://twitter.com/arangodb)
* [ArangoDB-PHP on Twitter](https://twitter.com/arangodbphp)
* [General GitHub documentation](https://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/send-pull-requests)
