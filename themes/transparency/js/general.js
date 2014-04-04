var section, page;
var username, islogged;

function count(input, inputcount, limit) {
	if (input.value.length > limit) {
		input.style.backgroundColor = 'red';
		input.style.color = 'white';
		$('#btsend').attr('disabled', 'disabled');
	}
	else {
		input.style.backgroundColor = 'white';
		input.style.color = 'black';
		if ($('#btsend').attr('disabled')) {
			$('#btsend').removeAttr('disabled');
		}
	}
	inputcount.value = limit - input.value.length;
}

function shortURLs() {
	var note = escape($("#ttnote").val());
	$.getJSON(baseURL.replace('%s', 'ajax').replace('%i', 'short_urls').replace('%d', ''), {note:note}, function(data) {
		if (data.error) {
			$("#post_status").html('<div class="error">'+data.error+'</div>');
			$("#post_status").fadeIn(1000);
			setTimeout("$('#post_status').fadeOut(1000)", 5000);
		}
		else {
			$("#ttnote").val(data.note);
			count(document.note_form.note, document.note_form.remLen, 140);
		}
	});
}

function follow_user(who, button) {
	if (button.length === 0) { var divID = '#follow'; } else { var divID = '#'+button; }
	$(divID).attr('disabled', 'disabled');
	$("#loading").html(translations[0]);
	$('#loading').fadeIn('fast');
	$.post(baseURL.replace('%s', 'ajax').replace('%i', 'follow').replace('%d', ''),{who:who},function(data) {
		if (data.error) {
			alert(data.error);
			$("#loading").hide();
		} else {
			var allFollowers = $("#sfollowers").html();
			if (data.following === true) {
				$(divID).val(translations[1]);
				$("#sfollowers").html(++allFollowers);
			} else {
				$(divID).val(translations[2]);
				if (allFollowers == 1) { var modify = 0; }
				else { var modify = --allFollowers; }
				$("#sfollowers").html(modify);	
			}
			$("#loading").fadeOut("fast");
		}
	}, 'json');
	$(divID).removeAttr('disabled');
}

function ignore_user(who) {
	$('#ignore').attr('disabled', 'disabled');
	$('#loading').html(translations[0]);
	$('#loading').fadeIn('fast');
	$.post(baseURL.replace('%s', 'ajax').replace('%i', 'ignore').replace('%d', ''),{who:who},function(json) {
		if (json.ignored === true) {
			$('#follow').val(translations[2]);
			$('#follow').attr('disabled', true);
			$('#ignore').val(translations[3]);
		}
		else {
			$('#ignore').val(translations[4]);
			$('#follow').removeAttr('disabled');			
		}
		$("#loading").hide();
		$('#ignore').removeAttr('disabled');
	}, 'json');
}

function favorite(note) {
	$('#loading').html(translations[0]);
	$('#loading').fadeIn('fast');
	$.getJSON(baseURL.replace('%s', 'ajax').replace('%i', 'favorite').replace('%d', ''),{id:note,rand:Math.random()},function(data) {
		if (data.favorited === true) {
			$('#fav'+note).attr('src', themesURL+'img/icons/fav_del.png');
			var allFavorites = $("#sfavorites").html();
			$('#sfavorites').html(++allFavorites);
			$("#loading").fadeOut("fast");
		}
		else if (data.favorited === false) {
			$('#fav'+note).attr('src', themesURL+'img/icons/fav_add.png');
			var allFavorites = $("#sfavorites").html();
			$('#sfavorites').html(--allFavorites);
			$("#loading").fadeOut("fast");
			if (section == 'favorites') {
				$("#status_"+note).fadeOut(1800, function () {
					$('#note_list').remove('#status_'+note);
				});
			}
		}
	});
}

function getTipAmountFromForm() {
    alert("This is called");
var total = "";
    total = document.getElementById("tip_04").document.getElementById("tip_03").document.getElementById("tip_02").document.getElementById("tip_01");
    alert(total);
}

function doSimpleNoteForm() {
	$('#upload_send').html('<input type="file" class="green_input" name="attach">');
	$('#note_form').removeAttr('onsubmit');
	$("#btsend, #ttnote").unbind();
	$("#ttnote").keypress(function (e) {
		if (e.which == 13) {
			document.getElementById('note_form').submit();
			return false;
		}
	});
}

function showLoading() {
	$('#loading').html(translations[0]);
	$('#loading').fadeIn('fast');
}

function datos(json) {
	if (!json.error) {
		var nodes = [];
		for(i=0; i < json.length; i++) {
			if (json[i].replying === true) {
				var classn = 'note unread';
			}
			else if (json[i].type == 'twitter') {
				var classn = 'note_t';
			}
			else {
				var classn = 'note';
			}
			

			var inner = '<img src="' + json[i].avatar + '" alt="avatar"';
			if (json[i].type != 'twitter' && (json[i].type != 'twitter_reply')) {
				inner += ' onmouseover="tooltip.ajax_delayed(event, \'profile\', \'' + json[i].user_id + '\');" onmouseout="tooltip.hide(event);"';
			}
			
			lastID = json[i].id;
			
			var divNote = $('<div id="status_' + json[i].id+'" class="'+classn+'"><div class="avatar_note">'+inner + ' width="48" height="48"/></div></div>');
			
			if (json[i].type != 'twitter'  && (json[i].type != 'twitter_reply')) {
				inner = '<a href="'+baseURL.replace('%s', json[i].username).replace('%i', '').replace('%d', '') + '" onmouseover="return tooltip.ajax_delayed(event, \'profile\', \'' + json[i].user_id + '\');" onmouseout="tooltip.hide(event);"';
			}
			else {
				inner = '<a href="http://www.twitter.com/'+ json[i].username + '"';
			}
			
			var actions = '';
			if (islogged === true) {
				if (json[i].user_id == userID) {
					actions = '&nbsp;<a href="' + baseURL.replace('%s', 'notes').replace('%i', 'delete').replace('%d', json[i].id) + '" onclick="if (document.getElementById(\'note_list\')) {deleteNote('+json[i].id+');return false;} else {return confirm(\''+translations[10]+'\');}"><img height="16" width="16" src="'+themesURL+'img/icons/delete.png" alt="'+translations[11]+'" title="'+translations[11]+'" /></a>';
				}
			}
			if (json[i].attached_file) {
				actions += '&nbsp;<a href="'+baseURL.replace('%s', 'download').replace('%i', json[i].id).replace('%d', json[i].attached_file) + '" rel="nofollow">';
				actions += '<img src="'+themesURL+'img/icons/download.png" alt="'+translations[5]+'" title="'+translations[5]+'" /></a>';
			}
			if (islogged === true && (json[i].type == 'public' && (json[i].type != 'twitter' && (json[i].type != 'twitter_reply')))) {
				if (json[i].favorite) {
					actions += '&nbsp;<img src="'+themesURL+'img/icons/fav_del.png" id="fav' + json[i].id + '" alt="'+translations[6]+'" title="'+translations[6]+'" onclick="favorite(' + json[i].id + ');" style="cursor: pointer;" />';
				}
				else {
					actions += '&nbsp;<img height="16" width="16"src="'+themesURL+'img/icons/fav_add.png" id="fav' + json[i].id + '" alt="'+translations[7]+'" title="'+translations[7]+'" onclick="favorite(' + json[i].id + ');" style="cursor: pointer;" />';
				}
			}
			if (json[i].type != 'private') {
				if (json[i].type != 'twitter' && (json[i].type != 'twitter_reply')) {
					actions += '&nbsp;<a href="'+baseURL.replace('%s', json[i].username).replace('%i', json[i].id).replace('%d', '') + '" title="'+translations[8]+'"><img height="16" width="16" src="'+themesURL+'img/icons/plink.png" alt="'+translations[8]+'" /></a>';
				}
				else {
					actions += '&nbsp;<a href="http://twitter.com/' + json[i].username.replace('<', '&lt;') + '/statuses/' + json[i].id + '" title="'+translations[8]+'"><img height="16" width="16" src="'+themesURL+'img/icons/plink.png" alt="'+translations[8]+'" /></a>';
				}
			}
			if (islogged === true) {
				if (json[i].type == 'twitter' || json[i].type == 'twitter_reply') {
					actions += '&nbsp;<a onclick="$(\'#ttnote\').val(\'%'+json[i].username+' \'+$(\'#ttnote\').val());$(\'#ttnote\').focus(); return false;" href="" title="'+translations[9]+'"><img src="'+themesURL+'img/icons/reply.png" alt="'+translations[9]+'" /></a>';
				}
				else {
					if (json[i].username != username) {
						if (json[i].type == 'private') {
							var network = '!';
						}
						else {
							var network = '@';
						}
						actions += '&nbsp;<a onclick="$(\'#ttnote\').val(\'@'+json[i].username+' \'+$(\'#ttnote\').val());$(\'#ttnote\').focus(); return false;" href="" title="'+translations[9]+'"><img src="'+themesURL+'img/icons/reply.png" alt="'+translations[9]+'" /></a>';
					}
				}
			}
			
			if (json[i].from == 'mobile') {
				datenote = '<img src="'+themesURL+'img/icons/phone.png" title="'+translations[12]+'">';
			}
			else if (json[i].type == 'twitter') {
				datenote = 'twitter';
			}
			else {
				datenote = json[i].from;
			}
			
			divInfoNote = $('<div class="info_note"><div class="user_note">'+inner + '>' + json[i].username.replace('<', '&lt;') + '</a></div><div id="actions_'+json[i].id+'" class="actions_note">'+actions+'</div><div class="date_note" data="{time:'+json[i].timestamp+'}"></div></div>');
			
			if (json[i].replies) {
				var textNormal = translations[13];
				var textPlural = translations[14];
				
				if (json[i].replies == 1) {
					var textReplies = textNormal;
				}
				else {
					var textReplies = textPlural;
				}
				
				$('<div class="replies_note"><a href="'+baseURL.replace('%s', json[i].username).replace('%i', json[i].id).replace('%d', '') + '">'+json[i].replies+' '+textReplies+'</div>').appendTo(divInfoNote);
			}
			divInfoNote.appendTo(divNote);

			$('<div class="text_note" style="margin-left:60px;margin-top:-3px"><div id="text_note_'+json[i].id+'">'+json[i].location+'<br />'+json[i].time_ago+'<br />I got a $'+json[i].tip_amount+'tip on a $'+json[i].bill_amount+' bill!<br/>'+json[i].text+'</div></div>').appendTo(divNote);
			$('<div class="separator"></div>').appendTo(divNote);
			
			var length = $('#note_list').children().length;

			if ((length + 1) > notes_per_page) {
				var lid = $('#note_list').children()[(length-1)];
				$('#'+lid.id).remove();
			}
			if ($('#note_list').children()[0].id.length === 0) {
				$('#note_list').html('');
			}
			$(divNote).hide();
			divNote.prependTo('#note_list');
			$(divNote).fadeIn(1800);
		}
		
	}
	else {
		$('#note_list').html('<div class="warning">'+json.error+'</div>');
	}
	
	$("#loading").fadeOut("fast");
	$("#ttnote,#btsend").attr("disabled",false);
}

function reloadNotes() {
	if (section && (section != 'favorites')) {
		showLoading();
		
		var url = baseURL.replace('%s', 'ajax').replace('%i', section).replace('%d', '');
		
		if (lastID === false) { lastID = $('#note_list :first-child').attr('id').replace('status_', ''); }
		
		if (lastID) {
			$.getJSON(url,{page:page, rand: Math.random(), since_id: lastID}, datos);
		}
		else {
			$.getJSON(url,{page:page, rand: Math.random()}, datos);
		}
		
		var notes = $("#ajax_notes").html();
		var privates = $("#ajax_privates").html();
		
		if (notes !== null && (privates !== null)) {
			$.getJSON(baseURL.replace('%s', 'ajax').replace('%i', 'mainpage').replace('%d', ''), { notes: notes, privates: privates}, function(json) {
				if (json.notes) {
					$('#ajax_notes').html(json.notes);
				}
				if (json.privates) {
					$('#ajax_privates').html(json.privates);
					content = $('#private_tab').attr('class');
					$('#private_tab').attr('class', content+' unread');
				}
			});
		}
	}
}

function deleteNote(id) {
	if (confirm(translations[10])) {
		$.getJSON(baseURL.replace('%s', 'ajax').replace('%i', 'delete').replace('%d', ''), {id:id}, function (json) {
			if (json.error) { alert('ERROR: '+json.error); }
			else {
				$("#status_"+id).fadeOut(1800, function () {
					$('#note_list').remove('#status_'+id);
					reloadNotes();
					if ($('.note').size() == 1) {
						$('#note_list').html('<div class="warning">'+translations[32]+'</div>');
					}
				});
			}
		});
	}
	return false;
}

function changeDates() {
	var contenedor = document.getElementById('note_list');
	if (contenedor) {
		var contenedor = contenedor.children;
		var count = document.getElementById('note_list').childElementCount;
		for (i = 0; i < count; i++) {
			if (contenedor[i].children[1]) {
				if (contenedor[i].children[1].children[2]) {
					var time = eval(contenedor[i].children[1].children[2].getAttribute('data'));
					var diff = (Math.round(((new Date()).getTime()-Date.UTC(1970,0,1))/1000)) - time;
					if (diff <= 1) {
						var text = translations[16];
					}
					else if (diff < 60) {
						var text = translations[17].replace('%s', diff);
					}
					else {
						var round = diff / 3600;
						if (diff < 3600) {
							round = Math.round(diff / 60);
							var text = translations[18].replace('%s', round);
						}
						else if (round >= 1 && (round < 24)) {
							var floor = Math.floor (round);
							var text = translations[19].replace('%s', floor);
						}
						else if ((diff / 86400) >= 1 && ((diff / 86400) < 25)) {
							var floor = Math.floor(diff / 86400);
							var text = translations[20].replace('%s', floor);
						}
					}
					if (text) {
						contenedor[i].children[1].children[2].children[0].innerHTML = text;
					}
				}
			}
		}
	}
}

$(document).ready(
	function() {
		islogged = (userID ? true : false);
		section = $("#js_options .ajax_section").text();
		page = $("#js_options .ajax_page").text();

		$("#ttnote").focus();
		$("#ttnote, #simplepost").keypress(function (e) {
			if (e.which == 13) {
				document.getElementById("btsend").click();
			return false;
			}
		}); 
	
		$("a[class^='external']").attr('target','_blank');
	 
		$("#btsend").click(function() {
			if ($('#ttnote').attr("value").length < 2) {
				$("#post_status").html('<div class="error">'+translations[21]+'</div>').fadeIn(1000);
				setTimeout("$('#post_status').fadeOut(1000)", 5000);
				return false;
			}
			if ($('#ttnote').attr("value").length > 140) {
				$("#post_status").html('<div class="error">'+translations[22]+'</div>').fadeIn(1000);
				setTimeout("$('#post_status').fadeOut(1000)", 5000);
				return false;
			}
			$('#ttnote, #btsend').attr('disabled', 'disabled');
			var in_reply_to = $('#in_reply_to').val();
			var note = $('#ttnote').val();
            var tip = escape($("#tip_amount").val());
            var bill = escape($("#bill_amount").val());
			var auth = $("#hiddenkey").val();
			clearInterval(timerID);
			$.post(baseURL.replace('%s', 'ajax').replace('%i', 'post').replace('%d', ''),{auth:auth,note:note,tip:tip,bill:bill,in_reply_to:in_reply_to},function(data) {
				if (data.error) {
					$("#post_status").html('<div class="error">'+data.error+'</div>').fadeIn(1000);
					setTimeout("$('#post_status').fadeOut(1000)", 5000);
					$("#ttnote, #btsend", "#tip_amount", "#bill_amount").attr("disabled", false);
				} else {
					$("#ttnote").attr("value", '');
                    $("#tip_amount").attr("value", '');
					$("#bill_amount").attr("value", '');
					$('#ajax_notes').html(($('#ajax_notes').html() / 1 ) + 1);
					reloadNotes();
					timerID = setInterval("reloadNotes()", ajaxRefresh);
					$('#ttnote, #btsend', "#tip_amount", "#bill_amount").attr('disabled', false);
					$(".counter_send .counter").attr('value',140);
					$("#post_status").html('<div class="ok">'+translations[23]+'</div>').fadeIn(1000);
					setTimeout("$('#post_status').fadeOut(1000)", 5000);
				}
			}, 'json');
		});
	}
);