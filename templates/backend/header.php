<header>
	
	<div class="main-nav-container">
		<div class="wrapper-inside">
			<div class="left"><a href="<?php echo CMS_SERVER_URL_BACKEND; ?>"><?php echo strtoupper(CMS_BACKEND_STARTBUTTON); ?></a></div>
			<div class="right" style="display:flex;">

				<?php
				if($session -> isAuthed(LOGIN_OBJECT_BACKEND) !== false)
				{
					include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackendMenu.php';	
					include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackendSitemap.php';	

					$pDatabase = CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);;

					$condBackendMenu = new CModelCondition();
					$condBackendMenu -> orderBy('menu_order_by');

					$modelBackendMenu = new modelBackendMenu();
					$modelBackendMenu -> load($pDatabase, $condBackendMenu);
					
					$modelBackendSitemap = new modelBackendSitemap();
					$modelBackendSitemap -> load($pDatabase, null, SITEMAP_BACKEND_EXTDATA);

					foreach($modelBackendMenu -> getResult() as $menuGroup)
					{
						$menuItemList = [];

						foreach($modelBackendSitemap -> getResult() as $menuItem)
						{
							if(empty($menuItem -> page_path))
								continue;

							if((int)$menuItem -> menu_group !== (int)$menuGroup -> menu_group)
								continue;
						
							if(empty($menuItem -> objects) || !is_array($menuItem -> objects))
								continue;

							$moduleId = current($menuItem -> objects) -> module_id;
							if(!$modules -> existsRights($moduleId, 'index'))
								continue;
								
							$menuItemList[] = $menuItem;
						}

						if(count($menuItemList) == 0)
							continue;

						if(count($menuItemList) == 1)
							echo '<a class="menu-group-link" href="'. CMS_SERVER_URL_BACKEND . substr(reset($menuItemList) -> page_path, 1) .'">';

						echo '<div class="menu-group-container '. (empty($menuGroup -> menu_name) ? 'menu-icon-only' : '' ) .'">';
				
						if(!empty($menuGroup -> menu_icon))
							echo '<span style="font-family:icons-solid">'. $menuGroup -> menu_icon .'</span> &nbsp; ';

						if(!empty($menuGroup -> menu_name))
							echo CLanguage::string($menuGroup -> menu_name, 'regular', true);
						
						if(count($menuItemList) > 1)
						{
							echo '<div class="menu-group-subs">';

							foreach($menuItemList as $menuItem)
							{
								echo '<a  class="menu-group-item" href="'. CMS_SERVER_URL_BACKEND . substr($menuItem -> page_path, 1) .'">'. $menuItem -> page_name .'</a>';
							}

							echo '</div>';
						}

						echo '</div>';

						if(count($menuItemList) == 1)
								echo '</a>';
					}
				}
				?>

				<a class="menu-group-link" href="<?= CMS_SERVER_URL_BACKEND; ?>?logout"><div class="menu-group-container menu-icon-only"><span style="font-family:icons-solid">&#xf2f5</span> &nbsp; </div></a>

				<style>

					.menu-group-container { position:relative; display:flex; align-items:center; padding:0px 20px; border-right: 1px solid rgb(149, 149, 149); height: 100%;}
					.menu-group-container:not(.menu-icon-only) { min-width:200px; }
					.menu-group-container:not(.menu-icon-only):first-child { border-left: 1px solid rgb(149, 149, 149);}

					.menu-group-container .menu-group-subs { display:none; position:absolute; top:100%; left:-1px; z-index:999999; width:calc(100% + 2px); border-style:solid; border-width:0px 1px 1px 1px; border-color:rgb(102, 102, 102); }
					.menu-group-container.menu-icon-only .menu-group-subs { left:unset; right:-1px; width:202px; }

					.menu-group-container:hover { background:white; color:black; }
					.menu-group-container:hover .menu-group-subs { display:flex; flex-direction:column; }

					.menu-group-item { display:block; padding: 6px 20px; background:white; font-size:0.85em; color:black; }
					.menu-group-item:hover { background:rgb(230,230,230); }

					.menu-group-link { color:white; }
					.menu-group-link:hover { background:white; color:black; }

				</style>


			</div>
		</div>
	</div>
	
	<div class="sub-nav-container">		
		<div class="wrapper-inside">			
			<div class="crumbs">
				<a class="darkblue" href="<?php echo CMS_SERVER_URL_BACKEND; ?>">Home</a> 
				<?php
				$_pathExtension = '';

				foreach($pageRequest -> crumbsList as $_crumbKey => $_crumb)
				{
					if(!isset($_crumb -> name)) break;
					if($_crumb -> urlPart === '/') break;
					$_pathExtension .= $_crumb -> urlPart;
					echo '<span class="crumb-delimeter">&rang;</span>';
					if(!empty($_crumb -> urlPart) || $_crumb -> urlPart !== false)
						echo '<a class="darkblue" href="'. CMS_SERVER_URL_BACKEND . $_pathExtension .'">'. $_crumb -> name .'</a>';
					else
						echo $_crumb -> name;
				}
				?> 
			</div>			
			<div class="sub-nav-items">				
				<?php
				foreach($pageRequest -> subs as $sub)
				{
					echo '<a class="darkblue" href="'. CMS_SERVER_URL_BACKEND . $pageRequest -> page_path .''. $sub['page_path'] .'">'. $sub['menu_name'] .'</a>';
				}
				?>
			</div>
		</div>	
	</div>

</header>
