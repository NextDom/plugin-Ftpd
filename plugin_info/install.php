<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function ftpd_install() {
	config::save('port', 8888, 'ftpd');
	config::save('local_ip', '0.0.0.0', 'ftpd');
	config::save('authorized_ip', '', 'ftpd');
	config::save('recordDir', 'tmp/ftpd_records', 'ftpd');
}

function ftpd_update() {
	foreach (eqLogic::byType('ftpd') as $eqLogic) {
		$eqLogic->save();
	}
	$daemon = cron::byClassAndFunction('ftpd', 'daemon');
	if (is_object($daemon)) {
        $daemon->remove();
	}
	$plugin = plugin::byId('ftpd');
	$plugin->deamon_stop();
	$plugin->deamon_start();
}

function ftpd_remove() {
    $daemon = cron::byClassAndFunction('ftpd', 'daemon');
    if (is_object($daemon)) {
        $daemon->remove();
    }
	$plugin = plugin::byId('ftpd');
	$plugin->deamon_stop();
}
?>
