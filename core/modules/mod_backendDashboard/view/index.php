
<div class="be-module-container">

	<div class="dashboard">

		<?php
		if(!empty($dashboardInfo -> widgetList))
		foreach($dashboardInfo -> widgetList as $item)
		{
			if(!property_exists($item,'instance'))
			{
				echo '<div class="dashboard-widget dashboard-widget-size-'. $item -> size .'"></div>';
				continue;
			}

			echo '<div class="dashboard-widget dashboard-widget-size-'. $item -> size .'">';

			$item -> instance -> view($item -> size);

			echo '</div>';
		}
		?>

	</div>

</div>

<style>

:root
{
	--module-dashboard-widget-size: 16px;
}

.dashboard .bgcolor-red { background: rgba(255,0,0,1); }

.dashboard { display:flex; flex-wrap:wrap; padding-top:20px; justify-content:space-between; padding-left:var(--module-dashboard-widget-size);}
.dashboard-widget { background:white; border:1px solid rgba(0,0,0,0.2); padding:15px; flex-shrink:0; box-shadow:2px 2px 10px 1px rgb(0 0 0 / 10%); margin-bottom:var(--module-dashboard-widget-size); }
.dashboard-widget:empty { opacity:0; }
.dashboard-widget { margin-right:var(--module-dashboard-widget-size); }

.dashboard-widget-size-33 { width:calc(33.333333% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-20 { width:calc(20% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-66 { width:calc(66.666666% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-25 { width:calc(25% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-50 { width:calc(50% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-75 { width:calc(75% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-60 { width:calc(60% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-80 { width:calc(80% - var(--module-dashboard-widget-size)); }
.dashboard-widget-size-100 { width:calc(100% - var(--module-dashboard-widget-size)); }


.dashboard-widget .short-stat { height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; text-align:center; }
.dashboard-widget .short-stat .value { font-size:1.6em; }
.dashboard-widget .short-stat .divider { height:2px; width:85%; margin:5px; }
.dashboard-widget .short-stat .title { font-size:0.9em; }
</style>