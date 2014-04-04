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

class Thumbnail
{

	var $src_image = array();
	var $dst_image = array();

	function __construct($src_file, $src_type)
	{

		if (file_exists($src_file) === true) {
			$this->src_image['file'] = $src_file;
			$this->src_image['type'] = $src_type;
		} else {
			return __('File don\'t exists');
		}

		switch ($this->src_image['type']) {
		case 'image/jpeg':
			$this->src_image['type'] = 'image/jpeg';
			$this->src_image['extension'] = 'jpg';
			$this->img = imagecreatefromjpeg($this->src_image['file']);
			break;
		case 'image/jpeg':
			$this->src_image['extension'] = 'jpg';
			$this->img = imagecreatefromjpeg($this->src_image['file']);
			break;
		case 'image/png':
			$this->src_image['extension'] = 'png';
			$this->img = imagecreatefrompng($this->src_image['file']);
			break;
		case 'image/gif':
			$this->src_image['extension'] = 'gif';
			$this->img = imagecreatefromgif($this->src_image['file']);
			break;
		default;
			return __('Format not supported');
		}

		$size = getimagesize($this->src_image['file']);
		$this->src_image['width'] = $size[0];
		$this->src_image['height'] = $size[1];

	}

	function __destroy()
	{
		imagedestroy($this->img);
		imagedestroy($this->thumb);
	}

	function do_thumbnail($dst_width = null, $dst_height = null)
	{

		if (isset($this->thumb)) unset($this->thumb);

		if (($dst_width === null) and ($dst_height === null)) {
			$this->dst_image['width'] = $this->src_image['width'];
			$this->dst_image['height'] = $this->src_image['height'];
		} else {
			if (($dst_width > 0) and ($dst_height > 0)) {
				$this->dst_image['width'] = $dst_width;
				$this->dst_image['height'] = $dst_height;
			} else if (($dst_width > 0) and ($dst_height === null)) {
					$this->dst_image['width'] = $dst_width;
					$this->scale(1);
				} else if (($dst_width === null) and ($dst_height > 0)) {
					$this->dst_image['height'] = $dst_height;
					$this->scale(2);
				} else {
				return __('Bad properties.');
			}
		}

		$this->thumb = imagecreatetruecolor($this->dst_image['width'], $this->dst_image['height']);

		if ($this->src_image['type'] == 'image/png' || $this->src_image['type'] == 'image/gif') {
			imagealphablending($this->thumb, false);
			imagesavealpha($this->thumb, true);
			$transparent = imagecolorallocatealpha($this->thumb, 255, 255, 255, 127);
			imagefilledrectangle($this->thumb, 0, 0, $this->dst_image['width'], $this->dst_image['height'], $transparent);
		} else {
			$white = imagecolorallocate($this->thumb, 255, 255, 255);
			imagefill($this->thumb, 0, 0, $white);
		}

		imagecopyresampled($this->thumb, $this->img, 0, 0, 0, 0, $this->dst_image['width'], $this->dst_image['height'], $this->src_image['width'], $this->src_image['height']);

		return $this->thumb;
	}

	function save($dst_file, $dst_dir = '', $thumb = null, $use_prefix = 0, $use_sufix = 0)
	{

		if ($thumb !== null) $this->thumb = $thumb;

		if ($use_prefix > 0) $prefix = substr(md5(rand()), 0, $use_prefix) . '_';
		if ($use_sufix > 0) $sufix = '_' . substr(md5(rand()), 0, $use_sufix);

		$this->dst_image['file'] = $dst_dir . $prefix . $dst_file . $sufix . '.' . $this->src_image['extension'];

		if (file_exists($this->dst_image['file']) === true) {
			if (($use_prefix > 0) || ($use_sufix > 0)) {
				$this->save($dst_file, $this->thumb, $use_prefix, $use_sufix);
			} else {
				return __('Image exists');
			}
		}

		switch ($this->src_image['type']) {
		case 'image/jpeg':
			$result = imagejpeg($this->thumb, $this->dst_image['file'], 100);
			break;
		case 'image/png':
			$result = imagepng($this->thumb, $this->dst_image['file']);
			break;
		case 'image/gif':
			$result = imagegif($this->thumb, $this->dst_image['file']);
		}

		return $result;
	}

	function show($thumb = null)
	{

		if ($thumb !== null) $this->thumb = $thumb;

		switch ($this->src_image['type']) {
		case 'image/jpeg':
			@ header('content-type: image/jpeg');
			imagejpeg($this->thumb, null, 100);
			break;
		case 'image/png':
			@ header('content-type: image/png');
			imagepng($this->thumb);
			break;
		case 'image/gif':
			@ header('content-type: image/gif');
			imagegif($this->thumb);
		}

		return true;
	}

	function scale($scale_type)
	{
		if ($scale_type == 1) {
			$this->dst_image['height'] = round($this->src_image['height'] / ($this->src_image['width'] / $this->dst_image['width']));
		} else if ($scale_type == 2) {
				$this->dst_image['width'] = round($this->src_image['width'] / ($this->src_image['height'] / $this->dst_image['height']));
			}
	}
}

?>