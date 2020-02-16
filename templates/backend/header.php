<header>
	
	<div class="main-nav-container">
		<div class="wrapper-inside">
			<div class="left"><a href="<?php echo CMS_SERVER_URL_BACKEND; ?>"><?php echo strtoupper(CMS_BACKEND_STARTBUTTON); ?></a></div>
			<div class="right" style="display:flex;">

				<?php

				if($session -> isAuthed(LOGIN_OBJECT_BACKEND) !== false)
				{
					$backendMenuGroups	= file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'backend/backend-menu.json');
					$backendMenuGroups	= json_decode($backendMenuGroups);

					$backendMenu	= file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'backend/backend.json');
					$backendMenu	= json_decode($backendMenu);

					foreach($backendMenuGroups as $menuGroup)
					{
						echo '<div class="menu-group-container">';

						if(!empty($menuGroup -> menu_icon))
							echo '<span style="font-family:icons-solid">'. $menuGroup -> menu_icon .'</span> &nbsp; ';

						if(!empty($menuGroup -> menu_name))
							echo $menuGroup -> menu_name;

						echo '<div class="menu-group-subs">';

						foreach($backendMenu as $menuItem)
						{

							if(empty($menuItem -> page_path))
								continue;

							if($menuItem -> menu_group !== $menuGroup -> menu_group)
								continue;

							echo '<a  class="menu-group-item" href="'. CMS_SERVER_URL_BACKEND . $menuItem -> page_path .'/">'. $menuItem -> page_name .'</a>';
						}

						echo '</div>';
						echo '</div>';
					}
				}
				?>

				<style>

					.menu-group-container { position:relative; display:flex; align-items:center; padding:0px 20px; min-width:200px; border-right: 1px solid rgb(149, 149, 149);}
					.menu-group-container:first-child { border-left: 1px solid rgb(149, 149, 149);}

					.menu-group-subs { display:none; position:absolute; top:100%; left:-1px; z-index:999999; width:calc(100% + 2px); border-style:solid; border-width:0px 1px 1px 1px; border-color:rgb(102, 102, 102); }

					.menu-group-container:hover { background:white; color:black; }
					.menu-group-container:hover .menu-group-subs { display:flex; flex-direction:column; }

					.menu-group-item { display:block; padding: 6px 20px; background:white; font-size:0.85em; color:black; }
					.menu-group-item:hover { background:rgb(230,230,230); }

				</style>

				<?php /*
				if($session -> isAuthed(LOGIN_OBJECT_BACKEND) !== false)
				{
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'login-objects/">Login Objects</a>';
					echo '&nbsp;&nbsp;&nbsp;';	
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'right-groups/">Right Groups</a>';
					echo '&nbsp;&nbsp;&nbsp;';	
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'pages/">Pages</a>';
					echo '&nbsp;&nbsp;&nbsp;';				
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'backend-users/">Backend Users</a>';	
					echo '&nbsp;&nbsp;&nbsp;';										
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'denied-remote/">Denied Remote</a>';	
					echo '&nbsp;&nbsp;&nbsp;';										
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'useragents/">User Agents</a>';	
					echo '&nbsp;&nbsp;&nbsp;';										
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'sessions/">Sessions</a>';	
					echo '&nbsp;&nbsp;&nbsp;';										
					echo '<a  class="yellow" href="'. CMS_SERVER_URL_BACKEND .'modules/">Modules</a>';	
				} */
				?>
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
					echo '<a class="darkblue" href="'. CMS_SERVER_URL_BACKEND . $pageRequest -> page_path .'/'. $sub['page_path'] .'">'. $sub['menu_name'] .'</a>';
				}
				?>
			</div>
		</div>	
	</div>

</header>
