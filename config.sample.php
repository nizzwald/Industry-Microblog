<?php
# Jisko: An open-source microblogging application
# Copyright (C) 2008-2010 Rubén Díaz <outime@gmail.com>
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
# 
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# -------------------------------
# mySQL database configuration
# -------------------------------

//Host of the mySQL database
define('DB_HOST', 'localhost');
//Port of the mySQL database
define('DB_PORT', '3306');
//Username to access the mySQL database
define('DB_USER', 'jisko');
//Password to access the mySQL database
define('DB_PASSWORD', '');
//Name of the mySQL database where Jisko is located
define('DB_NAME', 'jisko');


# -------------------------------
# Miscellaneous
# -------------------------------

# define('SHARED_HOST', true); // if you got any trouble in your shared hosting, just remove #

# Menubar links
# If you want to display a link to another location in the menubar, just add it here.
# The syntax is 'TITLE' => 'URL'. You have to follow the PHP Array syntax
# You must put http:// at the beggining of the URL
# For example: array('Jisko project' => 'http://www.jisko.org', 'My hosting provider' => 'http://www.myhostingprovider.com')

$globals['menubar_links'] = array();

?>