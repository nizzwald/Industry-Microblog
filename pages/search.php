<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008-2010 Rubén Díaz <outime@gmail.com>
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

global $db, $sidebar;
global $jk;

if (isset($_GET['page'])) $jk->current_page = (int) $_GET['page'];
else $jk->current_page = 1;

if ($_POST) $jk->search_string = $_POST['info'];
elseif ($_GET['query']) $jk->search_string = urldecode($_GET['query']);

if ($jk->search_string) {
	$jk->search_string = stripslashes($jk->search_string);

	if ($_GET['format'] == 'rss') {
		$format = 'rss';
		if ($_GET['count']) $count_u = (int) $_GET['count'];
		else $count_u = $jk->notes_per_page;
	}
	else $count_u = $jk->notes_per_page;

	if (filter_var($jk->search_string, FILTER_VALIDATE_EMAIL)) $jk->search_result = $db->searchUser($jk->search_string, getStart($jk->current_page, $count_u), $count_u, true);
	else $jk->search_result = $db->searchUser($jk->search_string, getStart($jk->current_page, $count_u), $count_u);
	$jk->count_search = count($jk->search_result);

	if ($format != 'rss') {
		$jk->search_string = utf8_htmlentities($jk->search_string);

		$jk->title = __('Search results');
		$sidebar = 'my_profile';

		$jk->load('functions');
		$jk->load('header');
		$jk->load('search');
		$jk->load('sidebar');
		$jk->load('footer');
	}
	else {
		header('Content-type: application/rss+xml');

		$XMLWriter = new XMLWriter();
		$XMLWriter->openURI('php://output');
		$XMLWriter->startDocument('1.0', 'UTF-8');

		$XMLWriter->setIndent(true);

		$XMLWriter->startElement('rss');
		$XMLWriter->writeAttribute('version', '2.0');
		$XMLWriter->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
		$XMLWriter->writeAttribute('xmlns:opensearch', 'http://a9.com/-/spec/opensearch/1.1/');

		$XMLWriter->startElement('channel');
		$XMLWriter->writeElement('title', 'Users search: '.$jk->search_string);
		$XMLWriter->writeElement('description', 'Search results for: '.$jk->search_string.' at '.$jk->base);
		$XMLWriter->writeElement('link', coreLink(array('query='.$jk->search_string, 'page='.$jk->current_page, 'format=rss'), 'search'));
		$XMLWriter->writeElement('opensearch:totalResults', $jk->count_search);
		$XMLWriter->writeElement('opensearch:itemsPerPage', $count_u);
		$XMLWriter->writeElement('opensearch:startIndex', (string)getStart($jk->current_page, $count_u));
		$XMLWriter->startElement('atom:link');
		$XMLWriter->writeAttribute('rel', 'search');
		$XMLWriter->writeAttribute('type', 'application/opensearchdescription+xml');
		$XMLWriter->writeAttribute('href', coreLink('opensearch'));
		$XMLWriter->endElement();
		$XMLWriter->startElement('opensearch:Query');
		$XMLWriter->writeAttribute('role', 'request');
		$XMLWriter->writeAttribute('searchTerms', $jk->search_string);
		$XMLWriter->writeAttribute('startPage', $jk->current_page);
		$XMLWriter->endElement();
		$XMLWriter->writeElement('generator', 'Jisko');
		$XMLWriter->startElement('atom:link');
		$XMLWriter->writeAttribute('href', coreLink(array('query='.$jk->search_string, 'page='.$jk->current_page, 'format=rss'), 'search'));
		$XMLWriter->writeAttribute('rel', 'self');
		$XMLWriter->writeAttribute('type', 'application/rss+xml');
		$XMLWriter->endElement();
		$XMLWriter->startElement('image');
		$XMLWriter->writeElement('link', coreLink(array('query='.$jk->search_string, 'page='.$jk->current_page, 'format=rss'), 'search'));
		$XMLWriter->writeElement('title', 'Users search: '.$jk->search_string);
		$XMLWriter->writeElement('url', $jk->base.'static/img/logos/'.$jk->logo);
		$XMLWriter->endElement();

		foreach ($jk->search_result as $item) {
			$info = $db->getUserOptions($item, array('realname', 'username', 'profile'));
			$XMLWriter->startElement('item');
			if ($info['realname']) $XMLWriter->writeElement('title', $info['realname'].' (@'.$info['username'].')');
			else $XMLWriter->writeElement('title', '@'.$info['username']);
			$XMLWriter->writeElement('link', coreLink($info['username']));
			$XMLWriter->writeElement('guid', coreLink($info['username']));
			if ($info['profile']['bio']) $XMLWriter->writeElement('description', $info['profile']['bio']);
			$XMLWriter->endElement();
		}
		$XMLWriter->endElement();
		$XMLWriter->endElement();
	}
}
else {
	/*
	$jk->load('functions');
	$jk->load('header');
	$jk->load('search');
	$jk->load('sidebar');
	$jk->load('footer');*/
}

?>