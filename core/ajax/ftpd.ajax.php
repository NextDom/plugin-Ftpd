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

include_file('core', 'Ftpd', 'class', 'FtpdConstants');
try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new \Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init(FtpdConstants::FIELD_ACTION) == 'forceDetectFtpd') {
        $FtpdCmd = Ftpd::forceDetectFtpd();
        ajax::success($FtpdCmd);
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'migrePlugin') {
        $FtpdCmd = Ftpd::migrePlugin();
        ajax::success($FtpdCmd);
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'removeRecord') {
        Ftpd::removeSnapshot(init(FtpdConstants::FIELD_FILTRE));
        ajax::success();
    }

    if (init(FtpdConstants::FIELD_ACTION) == 'removeAllSnapshot') {
        $Ftpd = Ftpd::byId(init(FtpdConstants::FIELD_FILTRE));
        if (!is_object($Ftpd)) {
            throw new Exception(__('Impossible de trouver la Ftpd : ' . init(FtpdConstants::FIELD_FILTRE), __FILE__));
        }
        $Ftpd->removeAllSnapshot();
        ajax::success();
    }
    throw new \Exception(__('Aucune methode correspondante à : ', __FILE__) . init(FtpdConstants::FIELD_ACTION));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
