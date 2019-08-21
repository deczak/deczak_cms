<?php
/**
 * 	This function create the page menu in a ul-tag structure
 */

function
createMenu(&$sitemap, $_pos = 1, $_level = 2 )
{
	echo '<ul id="'. ($_pos === 1 ? 'menu-stucture' : '') .'">';
	for($i = $_pos; $i < count($sitemap); $i++)
	{
		if($_level !== intval($sitemap[$i] -> level)) 
			break;

		if(CMS_BACKEND)
			echo '<li><a href="'. CMS_SERVER_URL_BACKEND .'sites/edit/'. $sitemap[$i] -> page_language .'/'. $sitemap[$i] -> node_id .'" title="'. $sitemap[$i] -> page_title .'">'. $sitemap[$i] -> page_name  .'</a>';
		else
			echo '<li><a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($sitemap[$i] -> page_path, 1) .'" title="'. $sitemap[$i] -> page_title .'">'. $sitemap[$i] -> page_name  .'</a>';

		if($sitemap[$i] -> offspring != 0)
			$i = createMenu($sitemap, ($i + 1), ($_level + 1));

		echo '</li>';
	}
	echo '</ul>';
	return $i - 1;
}

?>


<div class="outer-wrapper">

<header>

	<div class="inner-wrapper">

		<div id="page-headline">

			Project Name
			
		</div>

		<div>
			<?php createMenu($sitemap); ?>
		</div>

		<div id="language-selection">
			<?php
			foreach(CFG::LANG_SUPPORTED as $_lang)
			{
				$_url= '';

				if(CMS_BACKEND)
				{
					if(isset($page -> alternate_path[ $_lang['key'] ]))
					{
						$_url = CMS_SERVER_URL_BACKEND .'sites/edit/'. $_lang['key'] .'/'. $page -> alternate_path[ $_lang['key'] ]['node_id'];
						echo '<a href="'. $_url .'" title="'. $_lang['name'] .'">'. $_lang['key'] .'</a>';
					}
				}
				else
				{		
					if(isset($page -> alternate_path[ $_lang['key'] ]))
						$_url = substr($page -> alternate_path[ $_lang['key'] ]['path'],1);

					$_url = CMS_SERVER_URL . ((CFG::LANG_DEFAULT_SUFFIX || $_lang['key'] !== CFG::LANG_DEFAULT) ? $_lang['key'] .'/' : '') . ($_url === '/' ? '' : $_url);

					echo '<a href="'. $_url .'" title="'. $_lang['name'] .'">'. $_lang['key'] .'</a>';
				}
			}
			?>
		</div>

	</div>
	
	<div class="inner-wrapper">
		<div id="crumb-path">
			<?php
			foreach($page -> crumb_path as $_crumb)
			{
				if($_crumb -> level !== 1)
					echo '<span class="crumb-delimeter">&rang;</span>';

				if($_crumb -> node_id !== $page -> node_id)
				{
					if(CMS_BACKEND)
						echo '<a href="'. CMS_SERVER_URL_BACKEND .'sites/edit/'. $_crumb -> page_language .'/'. $_crumb -> node_id .'" title="'. $_crumb -> page_title .'">'. $_crumb -> page_name  .'</a>';
					else
						echo '<a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($_crumb -> page_path, 1) .'" title="'. $_crumb -> page_title .'">'. $_crumb -> page_name .'</a>';
				}

				if($_crumb -> node_id === $page -> node_id)
					echo '<span>'. $_crumb -> page_name .'</span>';
			}
			?>
		</div>

	</div>

</header>
