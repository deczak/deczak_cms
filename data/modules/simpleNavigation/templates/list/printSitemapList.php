<?php

function printSitemapList(array &$_sitemap, int $_sitemapIndex, int $_sitemapLevel, &$_objectParams, $_pagePath, $_printRoodNode)
{
	$timestamp 		= time();

	if(count($_sitemap) === 1)
	{
		$typeTest = reset($_sitemap);
		if($typeTest -> listing_type === 'page')
			$_sitemapIndex = 0;
	}
	

	$childIsActive 	= false;
	for($i = $_sitemapIndex; $i < count($_sitemap); $i++)
	{



		if(	(		$_sitemap[$i] ->  listing_hidden == 1
				&&	$_sitemap[$i] -> hidden_state === 2 
				&& $_sitemap[$i] -> page_auth == 0
			)

			||	(	($_sitemap[$i] -> hidden_state == 5 && $_sitemap[$i] -> publish_from  < $timestamp)
				&&	($_sitemap[$i] -> hidden_state == 5 && $_sitemap[$i] -> publish_until > $timestamp && $_sitemap[$i] -> publish_until != 0)
				&& $_sitemap[$i] -> page_auth == 0
				)

			||	$_sitemap[$i] -> hidden_state === 0 && $_sitemap[$i] -> page_auth == 0
			||  CMS_BACKEND

		); else continue;

		if(!$childIsActive)
				$childIsActive = in_array($_sitemap[$i] -> node_id, array_column($_pagePath, 'nodeId'));
	}

	echo '<ul class="'. ($childIsActive ? 'active-path' : '') .' '. ($_sitemapIndex === (int)!$_printRoodNode ? 'simple-navigation template-list' : '') .'">';

	for($i = $_sitemapIndex; $i < count($_sitemap); $i++)
	{




		if($_sitemapLevel !== $_sitemap[$i] -> level && $_sitemap[$i] -> listing_type == 'subpages')
		{
			$i = $_sitemap[$_sitemapIndex - 1] -> offspring + $_sitemapIndex - 1;


			break;
		}

		if(	(		$_sitemap[$i] -> listing_hidden == 1
				&&	$_sitemap[$i] -> hidden_state === 2 
				&& $_sitemap[$i] -> page_auth == 0
			)

			||	(	($_sitemap[$i] -> hidden_state == 5 && $_sitemap[$i] -> publish_from  < $timestamp)
				&&	($_sitemap[$i] -> hidden_state == 5 && $_sitemap[$i] -> publish_until > $timestamp && $_sitemap[$i] -> publish_until != 0)
				&& $_sitemap[$i] -> page_auth == 0
				)

			||	$_sitemap[$i] -> hidden_state === 0 && $_sitemap[$i] -> page_auth == 0
			||  CMS_BACKEND

		) ; else { 


			$i = $i + $_sitemap[$i] -> offspring;

			continue;

		}  

		$issActive = in_array($_sitemap[$i] -> node_id, array_column($_pagePath, 'nodeId'));

		if(CMS_BACKEND)
			echo '<li class="'. ($issActive ? 'active-page' : '') .'"><a data-node="'. $_sitemap[$i] -> node_id .'" class="darkblue" href="'. CMS_SERVER_URL_BACKEND .'pages/view/'. $_sitemap[$i] -> page_language .'/'. $_sitemap[$i] -> node_id .'" title="'. $_sitemap[$i] -> page_title .'">'. $_sitemap[$i] -> page_name .'</a>';
		else
			echo '<li class="'. ($issActive ? 'active-page' : '') .'"><a data-node="'. $_sitemap[$i] -> node_id .'" class="darkblue" href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($_sitemap[$i] -> page_path, 1) .'" title="'. $_sitemap[$i] -> page_title .'" '. ($_sitemap[$i] -> menu_follow == 0 ? 'rel="nofollow"' : '') .'>'. $_sitemap[$i] -> page_name  .'</a>';

		if($_sitemap[$i] -> offspring != 0)
		{
			$i = printSitemapList($_sitemap, ($i + 1), ($_sitemapLevel + 1), $_objectParams, $_pagePath, $_printRoodNode);
		}
	}

	echo '</ul>';


	return $i;
}
