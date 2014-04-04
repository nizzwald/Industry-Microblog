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


class Install {
	private $mysqlfd;

	function __construct($DB_HOST, $DB_PORT, $DB_USERNAME, $DB_PASSWORD, $DB_NAME)
	{
		$this->mysqlfd = mysql_connect($DB_HOST.':'.$DB_PORT, $DB_USERNAME, $DB_PASSWORD);
		if ($this->mysqlfd) {
			if (!mysql_select_db($DB_NAME)) {
				return 'mysql';
			}
		}
		return 'mysql';
	}


	function upgrade()
	{
		$this->mysql_import(dirname(__FILE__).'/install.sql');
	}

	/*
		MYSQL Import Script 0.1
		Function from sean-barton.co.uk
		
		http://www.sean-barton.co.uk/2009/03/sql-import-from-a-file-using-php/
		
	*/
	function mysql_import($filename)
	{
		$return = false;
		$sql_start = array('INSERT', 'UPDATE', 'DELETE', 'DROP', 'GRANT', 'REVOKE', 'CREATE', 'ALTER');
		$sql_run_last = array('INSERT');

		if (file_exists($filename)) {
			$lines = file($filename);
			$queries = array();
			$query = '';

			if (is_array($lines)) {
				foreach ($lines as $line) {
					$line = trim($line);

					if (!preg_match("'^--'", $line)) {
						if (!trim($line)) {
							if ($query != '') {

								$first_word = trim(strtoupper(substr($query, 0, strpos($query, ' '))));
								if (in_array($first_word, $sql_start)) {
									$pos = strpos($query, '`')+1;
									$query = substr($query, 0, $pos). substr($query, $pos);
								}

								$priority = 1;
								if (in_array($first_word, $sql_run_last)) {
									$priority = 10;
								}

								$queries[$priority][] = $query;
								$query = '';
							}
						} else {
							$query .= $line;
						}
					}
				}

				ksort($queries);

				foreach ($queries as $priority=>$to_run) {
					foreach ($to_run as $i=>$sql) {
						if (!mysql_query($sql)) return 'query';
					}
					if ($query) {
						if (!mysql_query($query)) return 'query';
					}
				}
			}
		}
	}

}

?>