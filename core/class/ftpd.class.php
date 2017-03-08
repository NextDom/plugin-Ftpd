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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class ftpd extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

	public static $_widgetPossibility = array('custom' => true);

	public static function deamon_info() {
		$return = array();
		$return['log'] = '';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction('ftpd', 'daemon');
		if (is_object($cron) && $cron->running()) {
			$return['state'] = 'ok';
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ( $deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		log::add('ftpd','debug',__('Prepare conf daemon', __FILE__));
		$xml = false;
		if ( file_exists($ftpd_path.'/ftpd.xml') )
		{
			$xml = simplexml_load_file ($ftpd_path.'/ftpd.xml');
		}
		if ( $xml === false )
		{
			$xml = new SimpleXMLElement('<config/>');
			log::add('ftpd','debug','New config');
		}

		if ( ! isset($xml->daemon) )
		{
			$daemon = $xml->addChild('daemon');
			log::add('ftpd','debug','New daemon');
		}
		else
		{
			$daemon = $xml->daemon;
		}
		if ( ! isset($daemon->port) )
		{
			$daemon->addChild('port', config::byKey('port', 'ftpd', '8888'));
		}
		else
		{
			$daemon->port = config::byKey('port', 'ftpd', '8888');
		}
		if ( ! isset($daemon->port) )
		{
			$daemon->addChild('port', config::byKey('port', 'ftpd', '8888'));
		}
		else
		{
			$daemon->port = config::byKey('port', 'ftpd', '8888');
		}
		if ( ! isset($daemon->local_ip) )
		{
			$daemon->addChild('local_ip', config::byKey('local_ip', 'ftpd', '0.0.0.0'));
		}
		else
		{
			$daemon->local_ip = config::byKey('local_ip', 'ftpd', '0.0.0.0');
		}
		if ( ! isset($daemon->authorized_ip) )
		{
			$daemon->addChild('authorized_ip', config::byKey('authorized_ip', 'ftpd', ''));
		}
		else
		{
			$daemon->authorized_ip = config::byKey('authorized_ip', 'ftpd', '');
		}
		if ( ! isset($daemon->log_file) )
		{
			$daemon->addChild('log_file', dirname(__FILE__) . '/../../../../log/ftpd-daemon');
		}
		else
		{
			$daemon->log_file = dirname(__FILE__) . '/../../../../log/ftpd-daemon';
		}
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));
		if ( ! isset($daemon->ftp_dir) )
		{
			$daemon->addChild('ftp_dir', $_CaptureDir.'/');
		}
		else
		{
			$daemon->ftp_dir = $_CaptureDir.'/';
		}
		if ( ! isset($daemon->pid_file) )
		{
			$daemon->addChild('pid_file', $ftpd_path."/ftpd.pid");
		}
		else
		{
			$daemon->pid_file = $ftpd_path."/ftpd.pid";
		}
		if ( ! isset($daemon->debug) )
		{
			$daemon->addChild('debug', config::byKey('debug', 'ftpd', '0'));
		}
		else
		{
			$daemon->debug = config::byKey('debug', 'ftpd', '0');
		}
		$pathjeedom = config::byKey("internalComplement");
		if ( substr($pathjeedom, 0, 1) != "/" ) {
			$pathjeedom = "/".$pathjeedom;
		}
		if ( substr($pathjeedom, -1) != "/" ) {
			$pathjeedom = $pathjeedom."/";
		}
		$url = "http://".config::byKey("internalAddr").":".config::byKey("internalPort")."/plugins/ftpd/core/ajax/ftpd.ajax.php?action=force_detect_ftpd";
		if ( ! isset($daemon->url_force_scan) )
		{
			$daemon->addChild('url_force_scan', $url);
		}
		else
		{
			$daemon->url_force_scan = $url;
		}

		if ( isset($xml->ftpd_client) )
		{
			unset($xml->ftpd_client);
		}
		$xml->addChild('ftpd_client');
		foreach (self::byType('ftpd') as $eqLogic) {
			if ( $eqLogic->getIsEnable() ) {
				$state = $eqLogic->getCmd(null, 'state');
				if ( is_object($state) )
				{
					$url = "http://".config::byKey("internalAddr").":".config::byKey("internalPort")."/core/api/jeeApi.php?api=".config::byKey('api')."&type=ftpd&id=".$state->getId()."&value=1";
					$xml->ftpd_client->addChild($eqLogic->getLogicalId());
					$xml->ftpd_client->{$eqLogic->getLogicalId()} = $url;
				}
			}
		}

		file_put_contents(dirname(__FILE__) . '/../../ressources/ftpd.xml', $xml->asXML());
		$cmd = "cd ".$ftpd_path.";python ./ftpd.py start";
		exec($cmd . ' &');
		log::add('ftpd','debug',__('daemon start : ', __FILE__).$cmd);
	}

	public static function deamon_stop() {
		// Initialisation de la connexion
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		$cmd = "cd ".$ftpd_path.";python ./ftpd.py stop";
		exec($cmd . ' >> ' . log::getPathToLog('ftpd') . ' 2>&1 &');
		log::add('ftpd','debug','daemon stop');
	}

	public static function force_detect_ftpd() {
		// Initialisation de la connexion
		log::add('ftpd','debug','force_detect_ftpd');
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));
		$new_ftpd = false;
		if ( is_dir($_CaptureDir)) {
			if ($dh = opendir($_CaptureDir)) {
				while (($file = readdir($dh)) !== false) {
					if ( is_dir($_CaptureDir . '/'.$file) && $file != "." && $file != ".." )
					{
						log::add('ftpd','debug','Find ftpd : '.$file);
						if ( ! is_object(self::byLogicalId($file, 'ftpd')) ) {
							log::add('ftpd','debug','Creation ftpd : '.$file);
							$eqLogic = new ftpd();
							$eqLogic->setLogicalId($file);
							$eqLogic->setName($file);
							$eqLogic->setEqType_name('ftpd');
							$eqLogic->setIsEnable(1);
							$eqLogic->save();
							$new_ftpd = true;
						}
					}
				}
				closedir($dh);
			}
		}
		if ( $new_ftpd )
		{
			ftpd::deamon_start();
		}
	}

	public function postInsert()
	{
        $state = $this->getCmd(null, 'state');
        if ( ! is_object($state) ) {
            $state = new ftpdCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('generic_type','PRESENCE');
			$state->setTemplate('dashboard', 'presence');
			$state->setTemplate('mobile', 'presence');
			$state->save();
		}
        $lastfilename = $this->getCmd(null, 'lastfilename');
        if ( ! is_object($lastfilename) ) {
            $lastfilename = new ftpdCmd();
			$lastfilename->setName('Nom du dernier fichier');
			$lastfilename->setEqLogic_id($this->getId());
			$lastfilename->setType('info');
			$lastfilename->setSubType('string');
			$lastfilename->setLogicalId('lastfilename');
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		}
	}

	public function postUpdate()
	{
        $state = $this->getCmd(null, 'state');
        if ( ! is_object($state) ) {
            $state = new ftpdCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('generic_type','PRESENCE');
			$state->setTemplate('dashboard', 'presence');
			$state->setTemplate('mobile', 'presence');
			$state->save();
		}
        $lastfilename = $this->getCmd(null, 'lastfilename');
        if ( ! is_object($lastfilename) ) {
            $lastfilename = new ftpdCmd();
			$lastfilename->setName('Nom du dernier fichier');
			$lastfilename->setEqLogic_id($this->getId());
			$lastfilename->setType('info');
			$lastfilename->setSubType('string');
			$lastfilename->setLogicalId('lastfilename');
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		}
		else
		{
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		}
	}

	public function preRemove() {
		$this->removeAllSnapshot(true);
	}

	public function postRemove() {
		ftpd::deamon_start();
	}

	public function removeAllSnapshot($anddir = false) {
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd')).'/'.$this->getLogicalId();
		if ($handle = opendir($_CaptureDir))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
				   unlink($_CaptureDir."/".$file);
				}
			}
			closedir($handle);
			if ( $anddir )
				rmdir($_CaptureDir);
		}
	}

	public static function removeSnapshot($file) {
		$record_dir = calculPath(config::byKey('recordDir', 'ftpd'));
		unlink ($record_dir . '/' . $file);
	}

	public static function event() {
        $cmd = ftpdCmd::byId(init('id'));
        if (!is_object($cmd)) {
            throw new Exception('Commande ID virtuel inconnu : ' . init('id'));
        }
		$EqLogic = $cmd->getEqLogic();
		log::add('ftpd','debug',"Receive push notification for ".$EqLogic->getLogicalId()." ".$cmd->getName()." (". init('id')."-".init('file').")");
		$cmd->setCollectDate('');
		$cmd->event(1);
		$files = array();
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd')).'/'.$EqLogic->getLogicalId();
		if ($handle = opendir($_CaptureDir))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
				   $files[filemtime($_CaptureDir."/".$file)] = $file;
				}
			}
			closedir($handle);
		}
		if ( count($files) > $EqLogic->getConfiguration('nbfilemax', 10) )
		{
			// sort
			ksort($files);
			$filetodelete = count($files) - $EqLogic->getConfiguration('nbfilemax', 10);
			foreach($files as $file)
			{
				if ( $filetodelete > 0 )
				{
					log::add('ftpd','debug',"delete ".$file);
					unlink($_CaptureDir."/".$file);
				}
				$filetodelete--;
			}
		}
        $lastfilename = $EqLogic->getCmd(null, 'lastfilename');
		$lastfilename->setCollectDate('');
		$lastfilename->event(config::byKey('recordDir', 'ftpd').'/'.$EqLogic->getLogicalId()."/".init('file'));
 		sleep(10);
		$cmd->setCollectDate('');
		$cmd->event(0);
    }
}

class ftpdCmd extends cmd 
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
}
?>
