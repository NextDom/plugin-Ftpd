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

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'ftpd', 'class', 'ftpd');

try {
    if (init('action') == 'force_detect_ftpd') {
		ftpd::force_detect_ftpd();
		exit;
    }

	if (init('action') == 'newcapture') {
		$ftpd = eqlogic::byLogicalId(init('LogicalId'), 'ftpd');
		if (!is_object($ftpd)) {
			throw new Exception(__('Impossible de trouver la ftpd : ' . init('LogicalId'), __FILE__));
		}
		$ftpd->newcapture(init('lastfilename'), init('orginalfilname'));
		exit;
	}

	if (init('action') == 'downloadcapture') {
		include_file('core', 'authentification', 'php');
		if (!isConnect()) {
			if (!jeedom::apiAccess(init('apikey'))) {
				throw new Exception(__('401 - Utilisateur non autorisé', __FILE__));
			}
		}
		if ( init('pathfile') == '' )
		{
			$pathfile = "../img/no-image.png";
			log::add('ftpd','debug',__('Pathfile not found', __FILE__));
		}
		else
		{
			$pathfile = calculPath(urldecode(init('pathfile')));
			if ( !file_exists($pathfile) )
			{
				$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));
				if (strpos($pathfile, $_CaptureDir) === false) {
					log::add('ftpd','debug',__('Pathfile not in CaptureDir : ', __FILE__).$pathfile." ".$_CaptureDir);
					$pathfile = "../img/no-image.png";
				}
			}
			else
			{
				$pathfile = "../img/no-image.png";
				log::add('ftpd','debug',__('Pathfile not found : ', __FILE__).$pathfile);
			}
		}
		$path_parts = pathinfo($pathfile);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $path_parts['basename']);
		readfile($pathfile);
		exit;
	}
    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    throw new Exception(displayExeption($e), $e->getCode());
}
?>
