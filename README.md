# contao-search-console-bundle
This bundle provides a powerfull search in the contao admin

## Installation
composer require gebi84/contao-search-console-bundle

## Config
```php
<?php
//system/mymodule/config/config.php
$GLOBALS['search_console']['modules']['SOME_UNIQUE_NAME'] = array();
```

| key  | type | mandatory | description |
| ---- | ---  | --- | --- |
| module | string| M | the contao module name
| shortcut | string| O | shortcut for new and  go to
| enableNew | boolean | O | enables the new shortcut link (n ...)
| enableGoTo | boolean | O | enables the new shortcut link (g ...)
| defaultSearchFields | array | O | if no search field is specified, it does a like search on this fields
| doNotSearch | boolean | O | will not be used for search query only for shortcuts
| customSearch | array | O | class,method will be called for buildCustomQuery see customquery
| table | string | O | table for example tl_content
| label | array | O | &$GLOBALS['TL_LANG']['CTE']['alias'][0]
