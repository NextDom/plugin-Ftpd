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
		$return['launchable'] = 'ok';
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		$pid_file = $ftpd_path."/ftpd.pid";
		if ( file_exists($pid_file) )
		{
			if (posix_getsid(trim(file_get_contents($pid_file))))
			{
				$return['state'] = 'ok';
			}
			else
			{
				log::add('ftpd','debug',__('Process not found', __FILE__));
			}
		}
		else
		{
			$processlist = system::ps("python ./ftpd.py start");
			if ( count($processlist) > 0 )
			{
				foreach ($processlist as $value)
				{
					log::add('ftpd','debug',__('Retrieve ftpd.py process with PID : ', __FILE__).$value["pid"]);
					$return['state'] = 'ok';
				}
			}
		}
		if ( config::byKey("internalAddr") == '' || config::byKey("internalPort") == "") {
			$return['launchable'] = 'nok';
		}
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
			$daemon->addChild('log_file', dirname(__FILE__) . '/../../../../log/ftpd_daemon');
		}
		else
		{
			$daemon->log_file = dirname(__FILE__) . '/../../../../log/ftpd_daemon';
		}
		if ( ! isset($daemon->api_key) )
		{
			$daemon->addChild('api_key', jeedom::getApiKey('ftpd'));
		}
		else
		{
			$daemon->api_key = jeedom::getApiKey('ftpd');
		}
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));
		if ( ! is_dir($_CaptureDir) )
		{
			log::add('ftpd','debug','mkdir '.$_CaptureDir);
			mkdir($_CaptureDir, 0777, true);
		}
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
		if ( ! isset($daemon->internalProtocol) )
		{
			$daemon->addChild('internalProtocol', config::byKey('internalProtocol', 'core', 'http://'));
		}
		else
		{
			$daemon->internalProtocol = config::byKey('internalProtocol', 'core', 'http://');
		}
		if ( ! isset($daemon->internalPort) )
		{
			$daemon->addChild('internalPort', config::byKey('internalPort', 'core', '80'));
		}
		else
		{
			$daemon->internalPort = config::byKey('internalPort', 'core', '80');
		}
		if ( config::byKey("internalComplement") != "" )
		{
			if ( ! isset($daemon->internalComplement) )
			{
				$daemon->addChild('internalComplement', config::byKey('internalComplement', 'core', ''));
			}
			else
			{
				$daemon->internalComplement = config::byKey('internalComplement', 'core', '');
			}
		}
		else
		{
			unset($daemon->internalComplement);
		}
		if ( isset($daemon->url_new_capture) )
		{
			unset($daemon->url_new_capture);
		}
		if ( isset($daemon->url_force_scan) )
		{
			unset($daemon->url_force_scan);
		}

		file_put_contents(dirname(__FILE__) . '/../../ressources/ftpd.xml', $xml->asXML());
		$cmd = "cd ".$ftpd_path.";python ./ftpd.py start";
		log::add('ftpd','info',__('daemon start : ', __FILE__).$cmd);
		ftpd::exec($cmd);
		sleep(5);
		$deamon_info = self::deamon_info();
	}

	public static function deamon_stop() {
		// Initialisation de la connexion
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		$pid_file = $ftpd_path."/ftpd.pid";
		if ( ! file_exists($pid_file) )
		{
			log::add('ftpd','debug',__('Pid file not found', __FILE__));
			$processlist = system::ps("python ./ftpd.py start");
			if ( count($processlist) > 0 )
			{
				foreach ($processlist as $value)
				{
					log::add('ftpd','debug',__('Retrieve ftpd.py process and kill with PID : ', __FILE__).$value["pid"]);
					exec("kill ".$value["pid"]);
				}
				sleep(60);
			}
		}
		else
		{
			$pid = trim(file_get_contents($pid_file));
			if ( ! posix_getsid($pid) )
			{
				log::add('ftpd','debug',__('Process not found', __FILE__)." (".$pid.")");
			}
			else
			{
				$cmd = "cd ".$ftpd_path.";python ./ftpd.py stop";
				log::add('ftpd','info','daemon stop');
				ftpd::exec($cmd);
				sleep(60);
			}
		}
	}

	public static function exec($commande) {
		$descriptorspec = array(
		   0 => array("pipe", "r"),  // stdin
		   1 => array("pipe", "w"),  // stdout
		   2 => array("pipe", "w"),  // stderr
		);
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		$process = proc_open($commande, $descriptorspec, $pipes, $ftpd_path, null);
		$stdout = stream_get_contents($pipes[1]);
		foreach(explode ("\n", $stdout) as $line)
		{
			if ( $line != "" )
			{
				log::add('ftpd','debug','daemon stdout : '.$line);
			}
		}
		fclose($pipes[1]);

		$stderr = stream_get_contents($pipes[2]);
		foreach(explode ("\n", $stderr) as $line)
		{
			if ( $line != "" )
			{
				log::add('ftpd','debug','daemon stderr : '.$line);
			}
		}
		fclose($pipes[2]);
		return proc_close($process);
	}

	public static function force_detect_ftpd() {
		// Initialisation de la connexion
		log::add('ftpd','info','force_detect_ftpd');
		$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));
		if ( is_dir($_CaptureDir)) {
			if ($dh = opendir($_CaptureDir)) {
				while (($file = readdir($dh)) !== false) {
					if ( is_dir($_CaptureDir . '/'.$file) && $file != "." && $file != ".." && substr($file, 0, 1) != "." )
					{
						log::add('ftpd','debug','Find ftpd : '.$file);
						if ( ! is_object(self::byLogicalId($file, 'ftpd')) ) {
							log::add('ftpd','info','Creation ftpd : '.$file);
							$eqLogic = new ftpd();
							$eqLogic->setLogicalId($file);
							$eqLogic->setName($file);
							$eqLogic->setEqType_name('ftpd');
							$eqLogic->setIsEnable(1);
							$eqLogic->save();
						}
					}
				}
				closedir($dh);
			}
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
			$state->setDisplay('invertBinary',1);
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

	public static function postConfig_recordDir() {
		if ( config::byKey('recordDir', 'ftpd') == '' )
		{
			config::save('recordDir', jeedom::getTmpFolder('ftpd') . '/ftpd_records', 'ftpd');
		}
	}

	public function postUpdate()
	{
		$restart = false;
		foreach($this->getCmd() as $cmd)
		{
			if ( $cmd->getLogicalId() == '' )
			{
				$cmd->setLogicalId('pattern');
				$cmd->save();
			}
		}
		foreach($this->getCmd(null, 'pattern', null, true) as $cmd)
		{
			if ( $cmd->getName() == 'Etat' )
			{
				$cmd->setLogicalId('state');
				$cmd->save();
				$restart = true;
			}
			if ( $cmd->getName() == 'Nom du dernier fichier' )
			{
				$cmd->setLogicalId('lastfilename');
				$cmd->save();
				$restart = true;
			}
		}
        $state = $this->getCmd(null, 'state');
        if ( ! is_object($state) ) {
            $state = new ftpdCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('invertBinary',1);
			$state->setDisplay('generic_type','PRESENCE');
			$state->setTemplate('dashboard', 'presence');
			$state->setTemplate('mobile', 'presence');
			$state->save();
			$restart = true;
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
			$restart = true;
		}
		else
		{
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		}
		if ( $restart )
		{
			$plugin = plugin::byId('ftpd');
			$plugin->deamon_start();
		}
	}

	public function preRemove() {
		$this->removeAllSnapshot(true);
	}

	public function removeAllSnapshot($anddir = false) {
		log::add('ftpd','debug',"Remove All Snapshot");
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

	public static function removeSnapshot($file)
	{
		log::add('ftpd','debug',"Remove Snapshot ".$file);
		$record_dir = calculPath(config::byKey('recordDir', 'ftpd'));
		unlink ($record_dir . '/' . $file);
		$path_parts=pathinfo($file);
		if ( strpos(mime_content_type($file),'video') !== false )
		{
		   $file = $path_parts['filename'] . '_mini.jpg';
		   log::add('ftpd','debug',"Remove Snapshot mini ".$file);
		   unlink($_CaptureDir."/".$file);
		}
	}

	public function newcapture($filename, $orginalfilname) {
		if ( $this->getIsEnable() ) {
			$state = $this->getCmd(null, 'state');
			log::add('ftpd','info',"Receive push notification for ".$this->getLogicalId()." ".$filename." ".$orginalfilname);
			$lastfilename = $this->getCmd(null, 'lastfilename');
			$lastfilename->setCollectDate('');
			$lastfilename->event(config::byKey('recordDir', 'ftpd').'/'.$this->getLogicalId()."/".$filename);
			$state->setCollectDate('');
			$state->event(1);
			foreach($this->getCmd(null, 'pattern', null, true) as $cmd)
			{
				log::add('ftpd','debug',$cmd->getName()." : ".$cmd->getConfiguration('pattern'). " match ? ".$orginalfilname);
				if ( preg_match ($cmd->getConfiguration('pattern'), $orginalfilname) )
				{
					log::add('ftpd','info',"match with ".$cmd->getName());
					$cmd->setCollectDate('');
					$cmd->event(1);
				}
			}
			if ( strpos(mime_content_type(calculPath(config::byKey('recordDir', 'ftpd').'/'.$this->getLogicalId()."/".$filename)),'video') !== false )
			{
				# Converntion en mini
				$path_parts=pathinfo(config::byKey('recordDir', 'ftpd').'/'.$this->getLogicalId()."/".$filename);
				log::add('ftpd','debug','Convertion de l image en miniature');
				$cmd = 'ffmpeg -i '.calculPath(config::byKey('recordDir', 'ftpd').'/'.$this->getLogicalId()."/".$filename).' -r 1 -s 320x200 -frames:v 1 '.calculPath(config::byKey('recordDir', 'ftpd').'/'.$this->getLogicalId())."/".$path_parts['filename'].'_mini.jpg';
				log::add('ftpd','debug',$cmd);
				exec($cmd);
			}
			$files = array();
			$_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd')).'/'.$this->getLogicalId();
			if ($handle = opendir($_CaptureDir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file != "." && $file != ".." && !strpos($file,'_mini.jpg'))
					{
					   $files[filemtime($_CaptureDir."/".$file)] = $file;
					}
				}
				closedir($handle);
			}
			if ( count($files) > $this->getConfiguration('nbfilemax', 10) )
			{
				// sort
				ksort($files);
				$filetodelete = count($files) - $this->getConfiguration('nbfilemax', 10);
				foreach($files as $file)
				{
					if ( $filetodelete > 0 )
					{
						log::add('ftpd','debug',"delete ".$file);
						unlink($_CaptureDir."/".$file);
						$path_parts=pathinfo($file);
						if ( strpos(mime_content_type($file),'video') !== false ) {
							$file = $path_parts['filename'] . '_mini.jpg';
							log::add('ftpd','debug',"delete ".$file);
							unlink($_CaptureDir."/".$file);
						}
					}
					$filetodelete--;
				}
			}
			sleep($this->getConfiguration('delairesetstatus', 10));
			$state->setCollectDate('');
			$state->event(0);
			foreach($this->getCmd(null, 'pattern', null, true) as $cmd)
			{
				if ( preg_match ($cmd->getConfiguration('pattern'), $orginalfilname) )
				{
					$cmd->setCollectDate('');
					$cmd->event(0);
				}
			}
		}
		else
		{
			log::add('ftpd','info',"Equipement not enable. Del ".$this->getLogicalId()." ".$filename." ".$orginalfilname);
			unlink(calculPath(config::byKey('recordDir', 'ftpd')).'/'.$this->getLogicalId()."/".$filename);
		}
    }

	public static function compilationOk() { 
		$ftpd_path = dirname(__FILE__) . '/../../ressources';
		$cmd = "cd ".$ftpd_path.";python ./ftpd.py test 2>/dev/null 1>&2";
		system($cmd,$code);
		log::add('ftpd','debug','daemon test return '.$code);
		if ( $code == 0 )
			return true; 
		else
			return false;
	} 
 
	public static function dependancy_info() { 
		$return = array(); 
		$return['log'] = 'ftpd_update'; 
		$return['progress_file'] = '/tmp/ftpd_in_progress'; 
		$return['state'] = (self::compilationOk()) ? 'ok' : 'nok'; 
		return $return; 
	} 

	public static function dependancy_install() { 
		if (file_exists('/tmp/ftpd_in_progress')) { 
			return; 
		} 
		log::remove('ftpd_update'); 
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh'; 
		$cmd .= ' >> ' . log::getPathToLog('ftpd_update') . ' 2>&1 &'; 
		exec($cmd); 
	} 

}

class ftpdCmd extends cmd 
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
	public function postInsert()
	{
		if ( ! defined($this->logicalId) || $this->logicalId == "" )
			$this->logicalId = 'pattern';
	}
}
?>
