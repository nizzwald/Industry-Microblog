<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008-10 Rubén Díaz <outime@gmail.com>
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

global $db;
global $_USER;
global $jk;

header('Content-type: application/opensearchdescription+xml');

$XMLWriter = new XMLWriter();
$XMLWriter->openURI('php://output');
$XMLWriter->startDocument('1.0', 'UTF-8');

$XMLWriter->setIndent(true);

$XMLWriter->startElement('OpenSearchDescription');
$XMLWriter->writeAttribute('xmlns', 'http://a9.com/-/spec/opensearch/1.1/');
$XMLWriter->writeElement('ShortName', 'User search');
$XMLWriter->writeElement('Description', str_replace('%name', $jk->name, __('Use %name to search the users in %name')));
$XMLWriter->writeElement('Tags', 'microblogging');
if (!empty($jk->admin_mail)) $XMLWriter->writeElement('Contact', $jk->admin_mail);
$XMLWriter->startElement('Url');
$XMLWriter->writeAttribute('type', 'application/rss+xml');
$XMLWriter->writeAttribute('rel', 'results');
$XMLWriter->writeAttribute('indexOffset', '0');
$XMLWriter->writeAttribute('method', 'get');
$XMLWriter->writeAttribute('template', coreLink(array('query={searchTerms}', 'page={startPage?}', 'format=rss'), 'search'));
$XMLWriter->endElement();
$XMLWriter->startElement('Url');
$XMLWriter->writeAttribute('type', 'text/html');
$XMLWriter->writeAttribute('rel', 'results');
$XMLWriter->writeAttribute('indexOffset', '0');
$XMLWriter->writeAttribute('method', 'get');
$XMLWriter->writeAttribute('template', coreLink(array('query={searchTerms}', 'page={startPage?}'), 'search'));
$XMLWriter->endElement();
$XMLWriter->startElement('Query');
$XMLWriter->writeAttribute('role', 'example');
$XMLWriter->writeAttribute('searchTerms', 'cat');
$XMLWriter->endElement();
$XMLWriter->startElement('Image');
$XMLWriter->writeAttribute('height', '80');
$XMLWriter->writeAttribute('width', '200');
$XMLWriter->writeAttribute('type', 'image/png');
$XMLWriter->text($jk->base.'static/img/logos/'.$jk->logo);
$XMLWriter->endElement();
$XMLWriter->startElement('Image');
$XMLWriter->writeAttribute('height', '16');
$XMLWriter->writeAttribute('width', '16');
$XMLWriter->writeAttribute('type', 'image/vnd.microsoft.icon');
$XMLWriter->text($jk->base.'favicon.ico');
$XMLWriter->endElement();
$XMLWriter->endElement();

$XMLWriter->flush();

?>