<?php
/**
 * 	This function create the page menu in a ul-tag structure
 */

function
createMenu(&$sitemap, $_pos = 1, $_level = 2 )
{
	$timestamp = time();
	echo '<ul id="'. ($_pos === 1 ? 'menu-stucture' : '') .'">';
	$locrr = 0;
	for($i = $_pos; $i < count($sitemap); $i++)
	{
		if($_level !== intval($sitemap[$i] -> level))
		{
			if(!CMS_BACKEND)
				$locrr = ($sitemap[$i-1] -> offspring ?? 1) - 1;
			break;
		}

		if(
				($sitemap[$i] -> hidden_state == 0)
			&&	(empty($sitemap[$i] -> page_auth) || (!empty($sitemap[$i] -> page_auth) && CSession::instance() -> isAuthed($sitemap[$i] -> page_auth) === true))
			||	(	($sitemap[$i] -> hidden_state == 5 && $sitemap[$i] -> publish_from  < $timestamp)
				&&	($sitemap[$i] -> hidden_state == 5 && $sitemap[$i] -> publish_until > $timestamp && $sitemap[$i] -> publish_until != 0)
				)
			||  CMS_BACKEND
		  ); else
		  {
			$i = $i +  $sitemap[$i] -> offspring;
			continue;
		  }

		if(CMS_BACKEND)
			echo '<li><a href="'. CMS_SERVER_URL_BACKEND .'pages/view/'. $sitemap[$i] -> page_language .'/'. $sitemap[$i] -> node_id .'" title="'. $sitemap[$i] -> page_title .'">'. $sitemap[$i] -> page_name  .'</a>';
		else
			echo '<li><a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($sitemap[$i] -> page_path, 1) .'" title="'. $sitemap[$i] -> page_title .'" '. ($sitemap[$i] -> menu_follow == 0 ? 'rel="nofollow"' : '') .'>'. $sitemap[$i] -> page_name  .'</a>';

		if($sitemap[$i] -> offspring != 0)
		{
			$i = createMenu($sitemap, ($i + 1), ($_level + 1));
		}

		echo '</li>';
	}
	echo '</ul>';

	return (!$locrr ? $i - 1 : $i + $locrr);
}
?>

<header>

	<div class="header-title inner-wrapper">

		<div>

			This is the header area

		</div>

		<div class="header-language">

			<?php
			foreach(CLanguage::getLanguages() as $_lang)
			{
				$_url= '';

				if(CMS_BACKEND)
				{
					if(isset($pageRequest -> alternate_path[ $_lang -> lang_key ]))
					{
						$_url = CMS_SERVER_URL_BACKEND .'pages/view/'. $_lang -> lang_key .'/'. $pageRequest -> alternate_path[ $_lang -> lang_key ]['node_id'];
						echo '<a href="'. $_url .'" title="'. $_lang -> lang_name .'">'. $_lang -> lang_key .'</a>';
					}
				}
				else
				{		
					if($_lang -> lang_hidden)	continue;
					if($_lang -> lang_locked)	continue;	

					if(isset($pageRequest -> alternate_path[ $_lang -> lang_key ]))
						$_url = substr($pageRequest -> alternate_path[ $_lang -> lang_key ]['path'],1);

					$_url = CMS_SERVER_URL . ((CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $_lang -> lang_key !== CLanguage::getDefault()) ? $_lang -> lang_key .'/' : '') . ($_url === '/' ? '' : $_url);

					echo '<a href="'. $_url .'" title="'. $_lang -> lang_name .'">'. $_lang -> lang_key .'</a>';
				}
			}
			?>

		</div>

	</div>

	<div class="header-menu-banner">

		<div class="inner-wrapper">

			<?php createMenu($sitemap); ?>

		</div>

	</div>

	<div class="header-crumbs inner-wrapper">
		<?php
		foreach($pageRequest -> crumbsList as $crumb)
		{
			if($crumb -> level !== 1)
				echo '<span class="crumb-delimeter">&rang;</span>';

			if($crumb -> nodeId != $pageRequest -> node_id)
			{
				if(CMS_BACKEND)
					echo '<a href="'. CMS_SERVER_URL_BACKEND .'pages/view/'. $crumb -> language .'/'. $crumb -> nodeId .'" title="'. $crumb -> title .'">'. $crumb -> name  .'</a>';
				else
					echo '<a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($crumb -> urlPart, 1) .'" title="'. $crumb -> page_title .'">'. $crumb -> name .'</a>';
			}

			if($crumb -> nodeId === $pageRequest -> node_id)
				echo '<span>'. $crumb -> name .'</span>';
		}
		?>
	</div>

</header>
