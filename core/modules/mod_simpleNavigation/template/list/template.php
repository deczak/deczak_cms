
<?php

require_once 'printSitemapList.php'; 

foreach($nodeList as $nodeLevel1)
{
	printSitemapList($nodeLevel1, 1, current($nodeLevel1) -> level + 1 , $object -> params, $pageRequest -> crumbsList, false);
}
