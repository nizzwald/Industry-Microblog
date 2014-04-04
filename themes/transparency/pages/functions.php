<?php

function showNote($gnote, $permalink = false) {
	global $_USER;
	extract(loadNote($gnote));
	?>
	<?php if ($viewable == true): ?>
	<?php if ($ntype == 'normal'): ?>
	<?php if (in_array($_USER['ID'], $reply_user)): ?>
	<div class="note unread" id="status_<?php echo $ID ?>">
	<?php else: ?>
	<div class="note" id="status_<?php echo $ID ?>">
	<?php endif; ?>
	<?php else: ?>
	<div class="note_t" id="status_<?php echo $ID ?>">
	<?php endif; ?>
		<div class="avatar_note">
			<?php if ($ntype == 'normal'): ?>
			<img src="<?php get_avatar($user_id, 48) ?>" alt="avatar" onmouseover="return tooltip.ajax_delayed(event, 'profile', '<?php echo $user_id ?>');" onmouseout="tooltip.hide(event);" width="48" height="48"/>
			<?php else: ?>
			<img src="<?php show($avatar) ?>" alt="avatar" width="48" height="48"/>
			<?php endif; ?>
		</div>
		<div class="info_note">
			<div class="user_note" style="float:left;">
				<?php if ($ntype == 'normal'): ?>
				  <a href="<?php anchor($username) ?>" onmouseover="return tooltip.ajax_delayed(event, 'profile', '<?php echo $user_id ?>');" onmouseout="tooltip.hide(event);"><?php show($username) ?></a>
				<?php else: ?>
				<a href="http://www.twitter.com/<?php show($username) ?>"><?php echo $username ?></a> 				
			</div>
				<?php endif; ?>
			</div>
			<div class="actions_note" id="actions_<?php echo $ID ?>">
				<?php if ($ntype == 'normal'): ?>
				<?php if ($_USER['ID'] == $user_id): ?>
				&nbsp;<a href="<?php anchor('notes', 'delete', $ID) ?>" onclick="if (document.getElementById('note_list')) {deleteNote(<?php show($ID) ?>);return false;} else {return confirm('<?php t('Are you sure? There is NO UNDO!') ?>');}"><img src="<?php get_permalink('img/icons/delete.png') ?>" height="16" width="16" alt="<?php t('Delete note') ?>" title="<?php t('Delete note') ?>" /></a>
				<?php endif; ?>
				<?php if ($attached_file): ?>
					<a href="<?php anchor('download', $ID, $attached_file) ?>" rel="nofollow">
					<img src="<?php get_permalink('img/icons/download.png') ?>" alt="<?php t('Download file') ?>" title="<?php t('Download file') ?>" />
					</a>
				<?php endif; ?>
				<?php if ($type == 'public'): ?>
				<?php if ($_USER): ?>
					<?php if ($is_favorite): ?>
					<a href="<?php anchor(array("id=$ID"), 'favorite') ?>" onclick="return false;"><img src="<?php get_permalink('img/icons/fav_del.png') ?>" id="fav<?php echo $ID ?>" alt="<?php t('Delete favorite') ?>" title="<?php t('Delete favorite') ?>" onclick="favorite(<?php echo $ID ?>);" style="cursor: pointer;" /></a>
					<?php else: ?>
					<a href="<?php anchor(array("id=$ID"), 'favorite') ?>" onclick="return false;"><img height="16" width="16" src="<?php get_permalink('img/icons/fav_add.png') ?>" id="fav<?php echo $ID ?>" alt="<?php t('Add to favorites') ?>" title="<?php t('Add to favorites') ?>" onclick="favorite(<?php echo $ID ?>);" style="cursor: pointer;" /></a>
					<?php endif; ?>
				<?php endif; ?>
				<a href="<?php anchor($username, $ID) ?>" title="<?php t('Permalink') ?>"><img src="<?php get_permalink('img/icons/plink.png') ?>" height="16" width="16" alt="Permalink" /></a>
				<?php endif; ?>
				<?php if ($_USER and $_USER['username'] != $username): ?>
				<?php if ($type != 'private'): ?>
				<a onclick="if (document.getElementById('ttnote')) {$('#in_reply_to').val(<?php echo $ID ?>);$('#ttnote').val('@<?php echo $username ?> '+$('#ttnote').val());$('#ttnote').focus(); return false;}" href="<?php anchor(array('note=@'.$username, 'in_reply_to='.$ID), 'notes') ?>"
				<?php else: ?>
				<a onclick="if (document.getElementById('ttnote')) {$('#in_reply_to').val(<?php echo $ID ?>);$('#ttnote').val('!<?php echo $username ?> '+$('#ttnote').val());$('#ttnote').focus(); return false;}" href="<?php anchor(array('note=!'.$username, 'in_reply_to='.$ID), 'notes') ?>"
				<?php endif; ?>
				title="<?php t('Reply') ?>"><img height="16" width="16" src="<?php get_permalink('img/icons/reply.png') ?>" alt="<?php t('Reply') ?>" /></a>
				<?php endif; ?>
				<?php else: ?>
				<a href="http://www.twitter.com/<?php show($username) ?>/status/<?php show($twitid) ?>" title="<?php t('Permalink') ?>"><img height="16" width="16" src="<?php get_permalink('img/icons/plink.png') ?>" alt="<?php t('Permalink') ?>" /></a>
				<a onclick="$('#ttnote').append('%<?php echo $username ?> ');$('#ttnote').focus(); return false;" href="<?php anchor(array('note=%'.$username), 'notes') ?>" title="<?php t('Reply') ?>"><img height="16" width="16" src="<?php get_permalink('img/icons/reply.png') ?>" alt="<?php t('Reply') ?>" /></a>
				<?php endif; ?>
			</div>
			
			<?php if ($ntype == 'normal'): ?>
			<?php if ($type != 'private'): ?>
			<?php if (countReplies($ID) > 0): ?>
			<div class="replies_note">
				<a href="<?php anchor($username, $ID) ?>"><?php nt('%d reply', '%d replies', countReplies($ID)) ?></a>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="text_note" style="margin-left:60px;margin-top:-3px;">
            <?php echo $_USER['location'] ?><br />
            <?php echo showTimeAgo($timestamp) ?> ago
        <div id="text_note_<?php echo $ID ?>">
        <?php 
            $noteArray = processNote($gnote);
    $tipAmt = $noteArray['tip_amount'];
    $billAmt = $noteArray['bill_amount'];
    if($tipAmt != "0.00" && $billAmt != "0.00"){
     echo "I got a $".$tipAmt." tip";
    echo " on a $".$billAmt." bill!";
    }
        ?>
        </div>
			<div id="text_note_<?php echo $ID ?>">
                <?php 
        
            echo $noteArray['note'];
                ?></div>
		</div>
		<div class="separator"></div>
	</div>
	<?php endif; ?>
	<?php
}

function timestamp_lastnote($user) {
	global $db;
	$last_note = $db->getLastNotes($user, 1);
	if ($last_note) return $last_note[0]['timestamp'];
	else return false;
}

function showUser($ID) { 
	global $_USER;
	extract(loadUser($ID));
	?>
	<div class="note">
		<div class="avatar_note"><img src="<?php get_avatar($ID, 48) ?>" alt="avatar" /></div>
		<div class="info_note">
            
			<div class="user_note">
				<a href="<?php anchor($username) ?>"><?php show($username) ?></a>
			</div>
			<?php if ($realname): ?>
			<div class="date_note"><?php show($realname) ?></div>
			<?php endif; ?>
		</div>
		<div id="text_note">
			<?php if ($_USER): ?>
				<?php if ($_USER['ID'] != $ID): ?>
					<?php if (is_ignored($ID) == false): ?>
						<?php if (is_following($_USER['ID'], $ID)): ?>
							<input type="button" value="<?php t('Stop following') ?>" class="submit" onclick="follow_user(<?php show($ID) ?>, 'follow_<?php show($ID) ?>');" id="follow_<?php show($ID) ?>" />
						<?php else: ?>
							<input type="button" value="<?php t('Follow') ?>" class="submit" onclick="follow_user(<?php show($ID) ?>, 'follow_<?php show($ID) ?>');" id="follow_<?php show($ID) ?>" />
						<?php endif; ?>
						<?php if (is_following($ID, $_USER['ID'])): ?>
						<span style="padding-left:15px"><img src="<?php get_permalink('img/icons/accept.png') ?>"></span>
						<?php endif; ?>
						<?php if (timestamp_lastnote($ID)): ?>
						<span style="font-size:11px;float:right;margin-right:56px;margin-top:8px;"><strong><?php t('Last note:') ?></strong> <?php echo showtimeago(timestamp_lastnote($ID)) ?>
						<?php endif; ?>
					<?php else: ?>
					<?php t('To follow this user, first you have to stop ignoring him.') ?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="separator"></div>
	</div>
	<?php
}

function txtReplies($count) {
	if ($count == 1) $txt = __('reply');
	else $txt = __('replies');
	return $count.' '.$txt;
}

/* PAGINATION (DIGG-STYLE PAGINATION BY STRANGER STUDIOS) */
function getPaginationString($targetStart, $totalitems, $page, $extraget = array()) {
	
	if (!$page) $page = 1;
	global $jk;
	
	$prev = $page - 1;									//previous page is page - 1
	$next = $page + 1;									//next page is page + 1
	$lastpage = ceil($totalitems / $jk->notes_per_page);				//lastpage is = total items / items per page, rounded up.
	$lpm1 = $lastpage - 1;								//last page minus 1
	$adjacents = 1;
	
	$pagination = "";
	if($lastpage > 1)
	{	
		$pagination .= "<br /><div class=\"pagination\"";
		if($margin || $padding)
		{
			$pagination .= " style=\"";
			if($margin)
				$pagination .= "margin: $margin;";
			if($padding)
				$pagination .= "padding: $padding;";
			$pagination .= "\"";
		}
		$pagination .= ">";
		
		//previous button
		if ($page > 1) 
			$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$prev"))), $targetStart)).'">'.__('previous').'</a>';
		else
			$pagination .= "<span class=\"disabled\">".__('previous')."</span>";
			
		//pages	
		if ($lastpage < 7 + ($adjacents * 2))
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
					$pagination .= "<span class=\"current\">$counter</span>";
				else
					$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";					
			}
		}
		elseif($lastpage >= 7 + ($adjacents * 2))
		{
			if($page < 1 + ($adjacents * 3))		
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
				$pagination .= "...";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$lpm1"))), $targetStart))."\">$lpm1</a>";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$lastpage"))), $targetStart))."\">$lastpage</a>";
			}
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=1"))), $targetStart))."\">1</a>";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=2"))), $targetStart))."\">2</a>";
				$pagination .= "...";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
				$pagination .= "...";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$lpm1"))), $targetStart))."\">$lpm1</a>";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$lastpage"))), $targetStart))."\">$lastpage</a>";	
			}
			else
			{
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=1"))), $targetStart))."\">1</a>";
				$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=2"))), $targetStart))."\">2</a>";
				$pagination .= "...";
				for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
			}
		}
		
		if ($page < $counter - 1) 
			$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$next"))), $targetStart)).'">'.__('next').'</a>';
		else
			$pagination .= "<span class=\"disabled\">".__('next')."</span>";
		$pagination .= "</div>\n";
	}		
	
	echo $pagination;

}

function doNoteForm($note = '', $in_reply_to = false) {
	global $_USER; ?>
	<div id="post_status" style="padding-left:15px;display:none;width:511px;"><?php echo showStatus('CACA', 'error'); ?></div>
	<div class="note_form" id="thenoteform">
		<form id="note_form" enctype="multipart/form-data" onsubmit="return false;" action="<?php anchor('post') ?>" method="post" name="note_form">
			<div class="text_send">
                Tip: <input type="text" id="tip_amount" name="tip"><br />
                Bill:<input type="text" id="bill_amount" name="bill"><br />
				<textarea name="note" class="textarea_notes" id="ttnote" rows="3" cols="50" onkeyup="count(this.form.note, this.form.remLen, 140);" onkeypress="count(this.form.note, this.form.remLen, 140);"><?php echo $note ?></textarea>
			</div>
			<div id="upload_send" class="upload_send">
				<a href="javascript:doSimpleNoteForm()"> <?php t('Attach file') ?></a>
			</div>
			<div class="button_send">
				<p><input type="submit" name="button" value="<?php t('Send') ?>" class="submit" id="btsend" /></p>
			</div>
			
			<div class="counter_send">
				<div class="short_urls">
					<a href="javascript:shortURLs()"><?php t('Shrink URLs') ?></a>
				</div>
				<p><input type="text" id="counter" value="140" readonly="readonly" name="remLen"/></p>
			</div>
			<p id="note_operations">
				<input type="hidden" name="auth" value="<?php echo md5($_USER['salt']) ?>" id="hiddenkey" />
				<?php if ($in_reply_to == false): ?>
				<input type="hidden" name="in_reply_to" value="" id="in_reply_to" />
				<?php else: ?>
				<input type="hidden" name="in_reply_to" value="<?php show($in_reply_to) ?>" id="in_reply_to" />
				<?php endif; ?>
			</p>
		</form>
	</div> <?php
}

function showStatus($text, $status) {
	switch ($status) {
		case 'ok':
			return '<div class="ok">'.$text.'</div>';
			break;
		case 'warning':
			return '<div class="warning">'.$text.'</div>';
			break;
		case 'error':
			return '<div class="error">'.$text.'</div>';
			break;
		case 'info':
			return '<div class="info">'.$text.'</div>';
			break;
	}
}

function parse_js_graphs($type) {
	$lbl = get_tagsStatsByTime($type);
	if ($lbl) {
		$label = array();
		$data = array();
		foreach ($lbl as $t) {
			$label[] = urlencode('#'.$t['tag']);
			$data[] = $t['COUNT(`id`)'];
		}
		echo 'data:'.json_encode($data).',axis_labels:'.json_encode($label);
	}
	else echo 'data:"",axis_labels:""';
}

function parse_js_user_graphs() {
	$lbl = array(count_users('active'), count_users('banned'), count_users('nc'));
	if ($lbl) {
		echo 'data:'.json_encode($lbl).',axis_labels:'.json_encode(array(__('active'), __('banned'), __('not confirmed')));
	}
	else echo 'data:"",axis_labels:""';
}

function parse_js_notes_graphs($type) {
	global $db;
	date_default_timezone_set('UTC');
	if ($type == 'hours') {
		$lbl = array($db->retrieveTimeNotes(21600, 3600), $db->retrieveTimeNotes(18000, 3600), $db->retrieveTimeNotes(14400, 3600), $db->retrieveTimeNotes(10800, 3600), $db->retrieveTimeNotes(7200, 3600), $db->retrieveTimeNotes(3600, 3600));
		if ($lbl) {
			echo 'data:'.json_encode($lbl).',axis_labels:'.json_encode(array(date('H:m', time()-18000), date('H:m', time()-14400), date('H:m', time()-10800), date('H:m', time()-7200), date('H:m', time()-3600), __('now')));
		}
		else echo 'data:"",axis_labels:""';
	}
	elseif ($type == 'days') {
		$lbl = array($db->retrieveTimeNotes(518400), $db->retrieveTimeNotes(432000), $db->retrieveTimeNotes(345600), $db->retrieveTimeNotes(259200), $db->retrieveTimeNotes(172800), $db->retrieveTimeNotes(86400));
		if ($lbl) {
			echo 'data:'.json_encode($lbl).',axis_labels:'.json_encode(array(date('d-m', time()-518400), date('d-m', time()-432000), date('d-m', time()-345600), date('d-m', time()-259200), date('d-m', time()-172800), __('today')));
		}
		else echo 'data:"",axis_labels:""';
	}
	elseif ($type == 'months') {
		$lbl = array($db->retrieveTimeNotes(20736000, 2592000), $db->retrieveTimeNotes(18144000, 2592000), $db->retrieveTimeNotes(15552000, 2592000), $db->retrieveTimeNotes(12960000, 2592000), $db->retrieveTimeNotes(10368000, 2592000), $db->retrieveTimeNotes(7776000, 2592000), $db->retrieveTimeNotes(5184000, 2592000), $db->retrieveTimeNotes(2592000, 2592000));
		if ($lbl) {
			echo 'data:'.json_encode($lbl).',axis_labels:'.json_encode(array(date('m/Y', time()-18144000), date('m/Y', time()-15552000), date('m/Y', time()-12960000), date('m/Y', time()-10368000), date('m/Y', time()-7776000), date('m/Y', time()-5184000), date('m/Y', time()-2592000), __('this month')));
		}
		else echo 'data:"",axis_labels:""';
	}
	
}

?>