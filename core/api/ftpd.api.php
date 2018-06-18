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

require_once __DIR__ . '/../../../../core/php/core.inc.php';
include_file('core', 'Ftpd', 'class', 'Ftpd');

try {
    include_file('core', 'authentification', 'php');
    if ( !isConnect() && !jeedom::apiAccess(init('api'), PLUGIN_ID) ) {
        throw new \Exception('Clé API non valide (ou vide) ou non connecté. Demande venant de :' . getClientIp() . '. Clé API : ' . secureXSS(init('api')));
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'forceDetectFtpd') {
        Ftpd::forceDetectFtpd();
        exit;
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'newcapture') {
        $Ftpd = eqlogic::byLogicalId(init('LogicalId'), PLUGIN_ID);
        if (!is_object($Ftpd)) {
            throw new \Exception(__('Impossible de trouver la Ftpd : ' . init('LogicalId'), __FILE__));
        }
        $Ftpd->newcapture(init('lastfilename'), init('orginalfilname'));
        exit;
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'downloadcapture' || init(FtpdConstants::FIELD_ACTION) == 'downloadmini') {

        if (init('pathfile') == '') {
            $pathfile = FtpdConstants::DEFAULT_IMAGE;
            $path_parts = pathinfo($pathfile);
            log::add(PLUGIN_ID, DEBUG_FACILITY, __('Pathfile not receive', __FILE__));
        } else {
            $pathfile = calculPath(urldecode(init('pathfile')));
            $path_parts = pathinfo($pathfile);

            if (init(FtpdConstants::FIELD_ACTION) == 'downloadmini') {
                if (file_exists($path_parts['dirname'] . "/" . $path_parts['filename'] . "_mini.jpg")) {
                    $pathfile = $path_parts['dirname'] . "/" . $path_parts['filename'] . "_mini.jpg";
                }
            }
            if (file_exists($pathfile)) {
                $_CaptureDir = calculPath(config::byKey('recordDir', PLUGIN_ID));

                if (is_dir($pathfile)) {

                    if (strpos($pathfile, $_CaptureDir) === false) {
                        log::add(PLUGIN_ID, DEBUG_FACILITY, __('Pathfile not in CaptureDir : ', __FILE__) . $pathfile . " " . $_CaptureDir);
                        $pathfile = FtpdConstants::DEFAULT_IMAGE;
                    } else {
                        log::add(PLUGIN_ID, DEBUG_FACILITY, __('Prepare archive ', __FILE__));
                        system('cd ' . dirname($pathfile) . ';tar cfz ' . jeedom::getTmpFolder('downloads') . '/archive.tar.gz * > /dev/null 2>&1');
                        $pathfile = jeedom::getTmpFolder('downloads') . '/archive.tar.gz';
                        $path_parts['basename'] = 'archive.tar.gz';
                    }
                } else {

                    if (strpos($pathfile, $_CaptureDir) === false) {
                        log::add(PLUGIN_ID, DEBUG_FACILITY, __('Pathfile not in CaptureDir : ', __FILE__) . $pathfile . " " . $_CaptureDir);
                        $pathfile = FtpdConstants::DEFAULT_IMAGE;
                    } else {
                        log::add(PLUGIN_ID, DEBUG_FACILITY, __('Prepare file ', __FILE__));
                    }
                }
            } else {
                log::add(PLUGIN_ID, DEBUG_FACILITY, __('Pathfile not found : ', __FILE__) . $pathfile);
                $pathfile = FtpdConstants::DEFAULT_IMAGE;
            }
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $path_parts['basename']);
        readfile($pathfile);
        if (file_exists(jeedom::getTmpFolder('downloads') . '/archive.tar.gz')) {
            unlink(jeedom::getTmpFolder('downloads') . '/archive.tar.gz');
        }
        exit;
    }
    if (init(FtpdConstants::FIELD_ACTION) == 'lastcapture') {
        log::add(PLUGIN_ID, DEBUG_FACILITY, __('get lastcapture ', __FILE__) . init('Id'));
        $Ftpd = eqlogic::byId(init('Id'), 'Ftpd');
        if (!is_object($Ftpd)) {
            throw new \Exception(__('Impossible de trouver la Ftpd : ' . init('Id'), __FILE__));
        }
        $pathfile = $Ftpd->getLastCapture();
        log::add(PLUGIN_ID, DEBUG_FACILITY, __('filename ', __FILE__) . $pathfile);
        $path_parts = pathinfo($pathfile);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . init('Id') . '.' . $path_parts['extension']);
        readfile($pathfile);
        exit;
    }

    throw new \Exception(__('Aucune methode correspondante à : ', __FILE__) . init(FtpdConstants::FIELD_ACTION));

} catch (\Exception $e) {
    throw new \Exception(displayException($e), $e->getCode());
}
