<?php if ($this->page_module != 'stats'): ?>
<div class="header_title"><?php t('Notes with the tag %s', "#".$this->tag_name) ?></div>
<div id="note_list">
	<?php if ($this->notes_count == 0): ?>
	<?php echo showStatus(__("There are no notes with this tag."), 'warning'); ?>
	<?php else: ?>
		<?php foreach ($this->notes_result as $note) showNote($note);?>
		
		<?php getPaginationString(array("tag", $this->tag_name), $this->notes_count, $this->current_page); ?>
	<?php endif; ?>
</div>
<?php else: ?>
<script src="<?php get_permalink('js/jquery-ui-1.8.custom.min.js') ?>" type="text/javascript"></script>
<script src="<?php get_permalink('js/jgcharts.pack.js') ?>" type="text/javascript"></script>
<link rel="stylesheet" href="<?php get_permalink('css/redmond/jquery-ui-1.7.2.custom.css') ?>" type="text/css" />
<script>$(document).ready(function(){
	$("#tabs").tabs({ fx: { opacity: 'toggle' } });
	var api = new jGCharts.Api();
	var opt = {
		size:'500x170',
		colors : ['4b9b41'],
	  <?php parse_js_graphs('today') ?>,
		type : 'bhg'
	};
	if (opt.data.length == 0) {
		$("#bar1").html('<?php echo showStatus(__('There is not enough data to gererate statistics'), 'warning') ?>');
	}
	else $('<img>').attr('src', api.make(opt)).prependTo("#bar1");
	api = new jGCharts.Api();
	opt = {
		size:'500x170',
		colors: ['81419b'],
	  <?php parse_js_graphs('week') ?>,
		type : 'bhg'
	};
	if (opt.data.length == 0) {
		$("#bar2").html('<?php echo showStatus(__('There is not enough data to gererate statistics'), 'warning') ?>');
	}
	else $('<img>').attr('src', api.make(opt)).prependTo("#bar2");
	api = new jGCharts.Api();
	opt = {
		size:'500x170',
		colors: ['41599b'],
	  <?php parse_js_graphs('month') ?>,
		type : 'bhg'
	};
	if (opt.data.length == 0) {
		$("#bar3").html('<?php echo showStatus(__('There is not enough data to gererate statistics'), 'warning') ?>');
	}
	else $('<img>').attr('src', api.make(opt)).prependTo("#bar3");
	api = new jGCharts.Api();
	opt = {
		size:'500x170',
		colors: ['2c50f2'],
	  <?php parse_js_graphs('everytime') ?>,
		type : 'bhg'
	};
	if (opt.data.length == 0) {
		$("#bar4").html('<?php echo showStatus(__('There is not enough data to gererate statistics'), 'warning') ?>');
	}
	else $('<img>').attr('src', api.make(opt)).prependTo("#bar4");
  });</script>
<div class="header_title"><?php t('Tags statistics') ?></div>
<br />
<div id="tabs">
	<ul>
		<li><a href="#today"><span><?php t('Today') ?></span></a></li>
		<li><a href="#week"><span><?php t('Last week') ?></span></a></li>
		<li><a href="#month"><span><?php t('Last month') ?></span></a></li>
		<li><a href="#everytime"><span><?php t('Everytime') ?></span></a></li>
	</ul>
	<div id="today">
		<div id="bar1">
		</div>
	</div>
	<div id="week">
		<div id="bar2">
		</div>
	</div>
	<div id="month">
		<div id="bar3">
		</div>
	</div>
	<div id="everytime">
		<div id="bar4">
		</div>
	</div>
</div>
<br /><br />
<div id="tag_cloud">
	<?php
		$stats = get_tagsStatsByTime('today', 20);
		if ($stats) {
			foreach ($stats as $key) $count[] = $key['COUNT(`id`)'];
			$maxi = max(array_values($count));
			for ($i = 10; $i > 0; $i--) $per[] = round($maxi * '0.'.$i);
			shuffle($stats);
			foreach ($stats as $papa) {
				if ($papa['COUNT(`id`)'] >= $per[8]) $style=30;
				else if ($papa['COUNT(`id`)'] >= $per[7]) $style=27;
				else if ($papa['COUNT(`id`)'] >= $per[6]) $style=24;
				else if ($papa['COUNT(`id`)'] >= $per[5]) $style=22;
				else if ($papa['COUNT(`id`)'] >= $per[4]) $style=20;
				else if ($papa['COUNT(`id`)'] >= $per[3]) $style=18;
				else if ($papa['COUNT(`id`)'] >= $per[2]) $style=16;
				else if ($papa['COUNT(`id`)'] >= $per[1]) $style=15;
				else if ($papa['COUNT(`id`)'] >= $per[0]) $style=14;
				else $style=13;
				
				echo '<span style="font-size: '.$style.'px;"><a href="'.coreLink('tag', $papa['tag']).'">#'.$papa['tag'].'</a></span> ';
			}
		}
	?>
</div>
<br /><br />

<?php
	$stats = get_tagsStatsWrRate();
	if ($stats): 
?>
<div style="width:260px;float:right">
	<div class="header_title"><?php t('Tags with better writing rate') ?></div>
	<table style="width:260px">
		<thead>
			<tr style="font-size:13px;font-family:Arial;font-weight:600;color: rgb(74, 101, 107)">
				<td><?php t('Name') ?></td>
				<td style="text-align:center"><?php t('Writing rate') ?></td>
				<td style="text-align:center;width:60px"><?php t('Number of notes') ?></td>
			</tr>
		</thead>
		<?php foreach ($stats as $rate): ?>
		<tr>
			<td><a href="<?php anchor('tag', $rate['tag']) ?>">#<?php show($rate['tag']) ?></a></td>
			<td style="text-align:center"><?php show($rate['calc']) ?></td>
			<td style="text-align:center"><?php show($rate['count']) ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php
	$stats = get_tagsStatsFounder();
	if ($stats): 
?>
<div style="width:250px;min-height:182px">
	<div class="header_title"><?php t('Users that created more tags') ?></div>
	<table style="width:250px">
		<thead>
			<tr style="font-size:13px;font-family:Arial;font-weight:600;color: rgb(74, 101, 107)">
				<td><?php t('Username') ?></td>
				<td style="text-align:center;width:60px"><?php t('Created tags') ?></td>
			</tr>
		</thead>
		<?php foreach ($stats as $rate): ?>
		<tr>
			<td><a href="<?php anchor(get_usernameFromID($rate['founder'])) ?>">@<?php show(get_usernameFromID($rate['founder'])) ?></a></td>
			<td style="text-align:center"><?php show($rate['COUNT(name)']) ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>

<?php endif; ?>
<?php endif; ?>