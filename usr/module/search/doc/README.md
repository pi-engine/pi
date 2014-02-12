Search module
=============

Entry and aggregator for module search.

Two modes:
* Built-in function with module callback
* Use third-party search service like google API


How to implement built-in search in a module
============================================

Step 1. Set up search specification in module meta configuration, optional

- Code

    In `config/module.php`
    ```
        'resource'  => array(
            ...
            'search'   => 'Module\<ModuleName>\Api\SearchClass',
        ),
    ```

Step 2. Build callback for search query

- The callback is required to extend `Pi\Search\AbstractSearch`
- The callback is recommended to locate in module api folder
- Check `Module\Article\Api\Search` and `Module\Demo\Api\Search` for example

Step 3. Increase module version number and update


How to implement Google Custom Search Engine
============================================

Step 1. Apply for Google Custom Search service from `https://www.google.com/cse`

Step 2. Copy your `Search engine ID` and paste to search module config `Google Search Code`