<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo LANG ?>" lang="<?php echo LANG ?>">
<head>
	<title><?php display_title() ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<?php get_permalink('css/styles.min.css') ?>"  type="text/css" />
	<link rel="stylesheet" href="<?php get_permalink('css/facebook.css') ?>"  type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Alegreya+Sans:400,700,900' rel='stylesheet' type='text/css'>
	<link rel="shortcut icon" href="<?php show($this->base) ?>static/img/favicon.png" type="image/png" /> 
	<?php load_feeds() ?>
	<meta name="keywords" content="<?php show(replacePatterns($this->meta_keywords)) ?>" />
	<meta name="robots" content="<?php show(replacePatterns($this->meta_robots)) ?>" />
	<meta name="description" content="<?php show(replacePatterns($this->meta_description)) ?>" />
	<link rel="search" type="application/opensearchdescription+xml" href="<?php anchor('opensearch') ?>" title="<?php t('Users search') ?>" />
	<script src="<?php get_permalink('js/jquery-1.4.2.min.js') ?>" type="text/javascript"></script>
	<script src="<?php get_permalink('js/jsoc.min.js') ?>" type="text/javascript"></script>
	<script src="<?php get_permalink('js/tooltip.min.js') ?>" type="text/javascript"></script>
	<script src="<?php get_permalink('js/general.js') ?>" type="text/javascript"></script>
	<script type="text/javascript">
		var lastID = <?php echo getLastNote() ?>;
		var timerID = setInterval("reloadNotes()",<?php echo ($this->ajax_refresh * 1000) ?>);
		var datesID = setInterval("changeDates()", 5000);
		ajaxRefresh = <?php echo ($this->ajax_refresh * 1000) ?>;
		userID = '<?php echo $_USER['ID'] ?>';
		username = '<?php echo $_USER['username'] ?>';
		notes_per_page = <?php echo $this->notes_per_page ?>;
		baseURL = '<?php anchor('%s', '%i', '%d') ?>';
		themesURL = '<?php get_permalink('') ?>';
		translations = new Array(
			"<?php t('Working...') ?>",
			"<?php t('Stop following') ?>",
			"<?php t('Follow') ?>",
			"<?php t('Stop ignoring') ?>",
			"<?php t('Ignore') ?>",
			"<?php t('Download file') ?>",
			"<?php t('Delete favorite') ?>",
			"<?php t('Add to favorites') ?>",
			"<?php t('Permalink') ?>",
			"<?php t('Reply') ?>",
			"<?php t('Are you sure? There is NO UNDO!') ?>",
			"<?php t('Delete note') ?>",
			"<?php t('mobile') ?>",
			"<?php t('reply') ?>",
			"<?php t('replies') ?>",
			"<?php t('Fetching notes...') ?>",
			"<?php t('right now') ?>",
			"<?php t('%ss ago') ?>",
			"<?php t('%sm ago') ?>",
			"<?php t('about %sh ago') ?>",
			"<?php t('about %sd ago') ?>",
			"<?php t('The note is too short') ?>",
			"<?php t('The note is too long') ?>",
			"<?php t('Note successfully sent') ?>",
			"<?php t('Error:') ?>",
			"<?php t('user or note not found') ?>",
			"<?php t('Name:') ?>",
			"<?php t('Location:') ?>",
			"<?php t('Bio:') ?>",
			"<?php t('Web:') ?>",
			"<?php t('Since:') ?>",
			"<?php t('In reply to:') ?>",
			"<?php t('No notes were found') ?>"
		);
	</script>
	<?php loadCustomCSS() ?>
</head>
<body>
	<div id="loading"></div>
	<div class="web">
        <div id="header_container">
		<div id="header">
			
            
                <a href="<?php echo $this->base ?>" class="logo"><img height="80" src="<?php get_logo() ?>" alt="<?php echo $this->name ?>" /></a>
            
            <form method="post" action="<?php anchor('search') ?>">
<div id="searchBar" style="float:left;margin:2px 0 0 100px;">
<div class="searchBox" style="float:left;"><input name="info" class="search_user" value="<?php t('Search...') ?>" onblur="if(this.value=='') this.value='<?php t('Search...') ?>';" onfocus="if(this.value=='<?php t('Search...') ?>') this.value='';" type="text" />
</div>
<div class="searchButton" style="float:left;margin:4px 0 0 5px;">
<input type="image" src="<?php get_permalink('img/icons/search.png') ?>" alt="<?php echo t('Search') ?>" /></div>
</div>
</form>
            
			<div class="menu">
            <div class="top_menu_links" style="float:left;">
			<ul>
                <?php if (is_logged()): ?>
				<li><a href="<?php anchor('home') ?>"><?php t('Home') ?></a></li> 
				<?php if (is_admin()): ?>
				<li><a href="<?php anchor('admin') ?>"><?php t('Administration') ?></a></li> 
				<?php endif; ?>
				<li><a href="<?php anchor('logout') ?>"><?php t('Logout') ?></a></li>
			<?php else: ?>
				<li><a href="<?php anchor('home') ?>"><?php t('Home') ?></a></li>
                <li><a href="<?php anchor('public') ?>"><?php t('Public notes') ?></a><?php get_menubar_extra_links('|'); ?></li>
			<?php endif; ?>
</ul>
</div>
			</div>
          </div>     
		</div>
		
		<div id="content">
		<div class="content_top">
			<div class="content_left_top">