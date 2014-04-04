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

global $db;
global $_USER;

$PARAMS = PARAMS;
list($id) = explode('/', $PARAMS);

$id = intval($id);

if (!$note = $db->getNoteCombined($id)) {
	die(__('Note doesn\'t exists'));
}

$file = 'users_files/'.$note['username'].'/files/'.$note['attached_file'];

if (!file_exists($file)) {
	die(__('File doesn\'t exists'));
}

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

switch ($extension) {
case 'jpg':
case 'jpeg':
	$ext = 'image/jpg';
	break;
case 'gif':
	$ext = 'image/gif';
	break;
case 'png':
	$ext = 'image/png';
	break;
case 'php':
case 'txt':
case 'phps':
case 'txt':
	$ext = 'text/plain';
	break;
case 'pdf':
	$ext = 'application/pdf';
	break;
case 'bin':
case 'sh':
case 'exe':
case 'zip':
	$ext = 'application/octet-stream';
	break;
default:
	$ext = 'application/download';
	break;
}

header("Content-Type: $ext");
header("Content-Length: ".@filesize($file));
header('Connection: close');

$file = fopen($file, 'r');
while (!feof($file)) {
	echo fgets($file, 1024);
}
fclose($file);

die;

?>