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

include_file('core', 'ftpd', 'class', 'ftpdConstants');
try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new \Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init(ftpdConstants::FIELD_ACTION) == 'forceDetectFtpd') {
        $ftpdCmd = ftpd::forceDetectFtpd();
        ajax::success($ftpdCmd);
    }

    if (init(ftpdConstants::FIELD_ACTION) == 'removeRecord') {
        ftpd::removeSnapshot(init(ftpdConstants::FIELD_FILTRE));
        ajax::success();
    }

    if (init(ftpdConstants::FIELD_ACTION) == 'removeAllSnapshot') {
        $ftpd = ftpd::byId(init(ftpdConstants::FIELD_FILTRE));
        if (!is_object($ftpd)) {
            throw new Exception(__('Impossible de trouver la ftpd : ' . init(ftpdConstants::FIELD_FILTRE), __FILE__));
        }
        $ftpd->removeAllSnapshot();
        ajax::success();
    }
    throw new \Exception(__('Aucune methode correspondante à : ', __FILE__) . init(ftpdConstants::FIELD_ACTION));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
