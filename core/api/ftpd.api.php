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
include_file('core', 'ftpd', 'class', 'ftpd');

try {
    include_file('core', 'authentification', 'php');
    if ( !isConnect() && !jeedom::apiAccess(init('api'), 'ftpd') ) {
        throw new \Exception('Clé API non valide (ou vide) ou non connecté. Demande venant de :' . getClientIp() . '. Clé API : ' . secureXSS(init('api')));
    }

    if (init(ftpdConstants::FIELD_ACTION) == 'forceDetectFtpd') {
        ftpd::forceDetectFtpd();
        exit;
    }

    if (init(ftpdConstants::FIELD_ACTION) == 'newcapture') {
        $ftpd = eqlogic::byLogicalId(init('LogicalId'), 'ftpd');
        if (!is_object($ftpd)) {
            throw new \Exception(__('Impossible de trouver la ftpd : ' . init('LogicalId'), __FILE__));
        }
        $ftpd->newcapture(init('lastfilename'), init('orginalfilname'));
        exit;
    }

    if (init(ftpdConstants::FIELD_ACTION) == 'downloadcapture' || init(ftpdConstants::FIELD_ACTION) == 'downloadmini') {

        if (init('pathfile') == '') {
            $pathfile = ftpdConstants::DEFAULT_IMAGE;
            $path_parts = pathinfo($pathfile);
            log::add('ftpd', 'debug', __('Pathfile not receive', __FILE__));
        } else {
            $pathfile = calculPath(urldecode(init('pathfile')));
            $path_parts = pathinfo($pathfile);

            if (init(ftpdConstants::FIELD_ACTION) == 'downloadmini') {
                if (file_exists($path_parts['dirname'] . "/" . $path_parts['filename'] . "_mini.jpg")) {
                    $pathfile = $path_parts['dirname'] . "/" . $path_parts['filename'] . "_mini.jpg";
                }
            }
            if (file_exists($pathfile)) {
                $_CaptureDir = calculPath(config::byKey('recordDir', 'ftpd'));

                if (is_dir($pathfile)) {

                    if (strpos($pathfile, $_CaptureDir) === false) {
                        log::add('ftpd', 'debug', __('Pathfile not in CaptureDir : ', __FILE__) . $pathfile . " " . $_CaptureDir);
                        $pathfile = ftpdConstants::DEFAULT_IMAGE;
                    } else {
                        log::add('ftpd', 'debug', __('Prepare archive ', __FILE__));
                        system('cd ' . dirname($pathfile) . ';tar cfz ' . jeedom::getTmpFolder('downloads') . '/archive.tar.gz * > /dev/null 2>&1');
                        $pathfile = jeedom::getTmpFolder('downloads') . '/archive.tar.gz';
                        $path_parts['basename'] = 'archive.tar.gz';
                    }
                } else {

                    if (strpos($pathfile, $_CaptureDir) === false) {
                        log::add('ftpd', 'debug', __('Pathfile not in CaptureDir : ', __FILE__) . $pathfile . " " . $_CaptureDir);
                        $pathfile = ftpdConstants::DEFAULT_IMAGE;
                    } else {
                        log::add('ftpd', 'debug', __('Prepare file ', __FILE__));
                    }
                }
            } else {
                log::add('ftpd', 'debug', __('Pathfile not found : ', __FILE__) . $pathfile);
                $pathfile = ftpdConstants::DEFAULT_IMAGE;
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
    if (init(ftpdConstants::FIELD_ACTION) == 'lastcapture') {
        log::add('ftpd', 'debug', __('get lastcapture ', __FILE__) . init('Id'));
        $ftpd = eqlogic::byId(init('Id'), 'ftpd');
        if (!is_object($ftpd)) {
            throw new \Exception(__('Impossible de trouver la ftpd : ' . init('Id'), __FILE__));
        }
        $pathfile = $ftpd->getLastCapture();
        log::add('ftpd', 'debug', __('filename ', __FILE__) . $pathfile);
        $path_parts = pathinfo($pathfile);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . init('Id') . '.' . $path_parts['extension']);
        readfile($pathfile);
        exit;
    }

    throw new \Exception(__('Aucune methode correspondante à : ', __FILE__) . init(ftpdConstants::FIELD_ACTION));

} catch (\Exception $e) {
    throw new \Exception(displayException($e), $e->getCode());
}
