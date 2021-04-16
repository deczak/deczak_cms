
<?php

require_once 'printSitemapList.php'; 
printSitemapList($sitemap, 1, current($sitemap) -> level + 1 , $object -> params, $pageRequest -> crumbsList, false);

?>