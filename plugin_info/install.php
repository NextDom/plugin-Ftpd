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

include_file('core', 'Ftpd', 'class', 'FtpdConstants');

function Ftpd_install() {
    config::save('port', 8888, 'Ftpd');
    config::save('local_ip', '0.0.0.0', 'Ftpd');
    config::save('authorized_ip', '', 'Ftpd');
    config::save('recordDir', jeedom::getTmpFolder('Ftpd') . '/Ftpd_records', 'Ftpd');
    jeedom::getApiKey('Ftpd');
    if (config::byKey('api::Ftpd::mode') == '') {
        config::save('api::Ftpd::mode', 'localhost');
    }
}

function Ftpd_update() {
    foreach (eqLogic::byType('Ftpd') as $eqLogic) {
        $_CaptureDir = calculPath(config::byKey('recordDir', 'Ftpd')).'/'.$eqLogic->getLogicalId();
        if ( is_dir($_CaptureDir) && $handle = opendir($_CaptureDir) )
        {
            while (false !== ($filename = readdir($handle)))
            {
                if ($filename != "." && $filename != ".." && ! strpos($filename,'_mini.jpg'))
                {
                       $path_parts=pathinfo(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId()."/".$filename);
                    if ( ! file_exists($path_parts['dirname'] . "/" . $path_parts['filename'] . "_mini.jpg") )
                    {
                        if ( strpos(mime_content_type(calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId()."/".$filename)),'video') !== false )
                        {
                            # Convertion en mini
                            $cmd = 'ffmpeg -i '.calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId()."/".$filename).' -r 1 -s 320x200 -frames:v 1 '.calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId())."/".$path_parts['filename'].'_mini.jpg';
                            exec($cmd);
                        }
                        else
                        {
                            list($width, $height) = getimagesize(calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId())."/".$filename);
                            if ( $width > 150 )
                            {
                                $tmpfname = calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId())."/".$path_parts['filename'].'_mini.jpg';
                                $modwidth = 150;
                                //$width * $size;
                                $modheight = round($height/$width*$modwidth);
                                //$height * $size;
                                // Resizing the Image
                                $tn = imagecreatetruecolor($modwidth, $modheight);
                                $image = imagecreatefromjpeg(calculPath(config::byKey('recordDir', 'Ftpd').'/'.$eqLogic->getLogicalId())."/".$filename);
                                imagecopyresampled($tn, $image, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);
                                // Outputting a .jpg, you can make this gif or png if you want
                                //notice we set the quality (third value) to 100
                                //imagejpeg($tn, null, 80);
                                imagejpeg($tn, $tmpfname, 80);
                                imagedestroy($tn);
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        $eqLogic->save();
    }
    $daemon = cron::byClassAndFunction('Ftpd', 'daemon');
    if (is_object($daemon)) {
        $daemon->remove();
    }
    jeedom::getApiKey('Ftpd');
    if (config::byKey('api::Ftpd::mode') == '') {
        config::save('api::Ftpd::mode', 'localhost');
    }
    $plugin = plugin::byId('Ftpd');

    $plugin->deamon_stop();
    $plugin->deamon_start();
}

function Ftpd_remove() {
    $daemon = cron::byClassAndFunction('Ftpd', 'daemon');
    if (is_object($daemon)) {
        $daemon->remove();
    }
    $plugin = plugin::byId('Ftpd');
    $plugin->deamon_stop();
}
