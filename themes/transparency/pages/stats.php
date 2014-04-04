<script src="<?php get_permalink('js/jquery-ui-1.8.custom.min.js') ?>" type="text/javascript"></script>
<script src="<?php get_permalink('js/jgcharts.pack.js') ?>" type="text/javascript"></script>
<link rel="stylesheet" href="<?php get_permalink('css/redmond/jquery-ui-1.7.2.custom.css') ?>" type="text/css" />
<script>
$(document).ready(function(){
    $("#tabs").tabs({ fx: { opacity: 'toggle' } });
	var api = new jGCharts.Api();
	var opt = {
		size:'350x170',
	  <?php parse_js_user_graphs() ?>,
		type : 'p'
	};
	$('<img>').attr('src', api.make(opt)).prependTo("#bar1");
	api = new jGCharts.Api();
	opt = {
		size:'230x200',
	  <?php parse_js_notes_graphs('days') ?>,
		grid: true,
		grid_x: 5,
		grid_y: 5,
		grid_line: 5,
		grid_blank: 0,
		type : 'lc'
	};
	$('<img>').attr('src', api.make(opt)).appendTo("#graph_day");
	api = new jGCharts.Api();
	opt = {
		size:'230x200',
	  <?php parse_js_notes_graphs('hours') ?>,
		grid: true,
		grid_x: 5,
		grid_y: 5,
		grid_line: 5,
		grid_blank: 0,
		type : 'lc'
	};
	$('<img>').attr('src', api.make(opt)).appendTo("#bar2");
	api = new jGCharts.Api();
	opt = {
		size:'450x200',
	  <?php parse_js_notes_graphs('months') ?>,
		grid: true,
		grid_x: 5,
		grid_y: 5,
		grid_line: 5,
		grid_blank: 0,
		type : 'lc'
	};
	$('<img>').attr('src', api.make(opt)).appendTo("#week");
});
</script>
<div class="header_title"><?php t('Stats') ?></div>

<div id="tabs">
	<ul>
		<li><a href="#numbers"><span>Numbers</span></a></li>
		<li><a href="#today"><span>Users</span></a></li>
		<li><a href="#week"><span>Notes</span></a></li>
	</ul>
	<div id="numbers">
		<h3>We have a total of <?php show(count_everytime_notes()) ?> notes made by <?php show(count_users('active')) ?> users.</h3>
		<br />
		<h3>Last registered user: <a href="<?php anchor(get_last_registered_user()) ?>">@<?php show(get_last_registered_user()) ?></a></h3>
	</div>
	<div id="today">
		<div id="bar1" style="text-align:center">
		</div>
	</div>
	<div id="week" style="text-align: center">
		<div id="bar2" style="text-align:center">
		<br /><br />
			<div style="float:right;padding-right:20px" id="graph_day"><h2>Notes/day</h1></div>
			<h2>Notes/hour</h1>
		</div>
		<br />
		<h2>Notes/month</h1>
	</div>
</div>