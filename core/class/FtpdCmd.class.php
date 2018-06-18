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

class FtpdCmd extends cmd
{

    public function execute($_options = array())
    {
        $eqLogic = $this->getEqLogic();

        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
        $notifyCmd = $eqLogic->getCmd(null, 'notify');
        $recordStateCmd = $eqLogic->getCmd(null, 'recordState');


        if ($this->getLogicalId() == 'notify_on') {
            log::add('Ftpd', 'debug', "Activation des notifications");
            $notifyCmd->setCollectDate('');
            $notifyCmd->event(1);
        } elseif ($this->getLogicalId() == 'notify_off') {
            log::add('Ftpd', 'debug', "Désactivation des notifications");
            $notifyCmd->setCollectDate('');
            $notifyCmd->event(0);
        } elseif ($this->getLogicalId() == 'notify_commute') {
            log::add('Ftpd', 'debug', "Bascule des notifications");
            $notifyCmd->setCollectDate('');
            $notifyCmd->event(($notifyCmd->execCmd() + 1) % 2);
        } elseif ($this->getLogicalId() == 'stopRecordCmd') {
            log::add('Ftpd', 'debug', "Désactivation stockage");
            $recordStateCmd->setCollectDate('');
            $recordStateCmd->event(0);
        } elseif ($this->getLogicalId() == 'startRecordCmd') {
            log::add('Ftpd', 'debug', "Active stockage");
            $recordStateCmd->setCollectDate('');
            $recordStateCmd->event(1);
        } else {
            log::add('Ftpd', 'debug', "Appel non traite : " . $this->getLogicalId());
            return false;
        }
        log::add('Ftpd', 'debug', "Notification : " . $notifyCmd->execCmd());
        return true;
    }

    public function postInsert()
    {
        if (!defined($this->logicalId) || $this->logicalId == "") {
            $this->logicalId = 'pattern';
        }
    }

    public function dontRemoveCmd()
    {
        if ($this->getLogicalId() == 'pattern') {
            return false;
        }
        return true;
    }

}
