sitemap
=======

Features
----------------------
Sitemap module is tools than help you to build customize sitemap.xml from your website, you can add manual links Or supported modules to add automatically new links. You can manage your links and build new sitemap after submit new item on your website.
default path for xml files is `upload/sitemap/sitemap.xml` And you should copy your new sitemap to website root.

* Generat sitemap.xml file manaul 
* Generat sitemap.xml from added links ( add manual link )
* Generat sitemap.xml after add items on each module by API
* Generat module-table-sitemap.xml 
* Add some setting options

ToDo
----------------------
* Generat siteman.xml file by cronjob
* Add other needed setting options
* Add zip and html sitemap

Use on other modules
----------------------
This module have dedicated table for save website links and build sitemap just from links on this table, you should use this codes for Add / Remove your module links on sitemap table.

Add this code after save new row for add or update new item link on sitemap module, By check it exist on DB or not
```
// Add / Edit sitemap link
if (Pi::service('module')->isActive('sitemap')) {
    $loc = Pi::url('YOUR ROTE URL');
    Pi::api('sitemap', 'sitemap')->singleLink($loc, $status, $module, $table, $item);
}

```

Add this code after save new row for add or update new item link on sitemap module, without check it exist on DB or not, good for use import multi links 
```
// Add / Edit sitemap link
if (Pi::service('module')->isActive('sitemap')) {
    $loc = Pi::url('YOUR ROTE URL');
    Pi::api('sitemap', 'sitemap')->groupLink($loc, $status, $module, $table, $item);
}

```

Remove link from sitemap module after remove item on your module
```
// Remove sitemap
if (Pi::service('module')->isActive('sitemap')) {
    $loc = Pi::url('YOUR ROTE URL');
    Pi::api('sitemap', 'sitemap')->remove($loc);
} 
```

Remove all links of your module or your module table from sitemap module , for regenerate all of them
```
// Remove sitemap
if (Pi::service('module')->isActive('sitemap')) {
    Pi::api('sitemap', 'sitemap')->removeAll($module, $table);
} 
```

* **$module** : your module
* **$table** : your item table
* **$item** : your item id (int)
* **$loc** : URL of the page. This URL must begin with the protocol (such as http) and end with a trailing slash, if your web server requires it. This value must be less than 2,048 characters.
* **$status** : your item status (int) , just items by status `1` will add on sitemap.xml file