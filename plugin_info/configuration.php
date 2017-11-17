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
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<form class="form-horizontal" id="config">
    <div class="form-group">
        <label class="col-lg-4 control-label">{{Port ftpd}}</label>
        <div class="col-lg-3">
            <input class="configKey form-control" data-l1key="port"/>
        </div>
        <div class="col-lg-3">
			Doit être supérieur à 1024.
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-4 control-label">{{Local IP}}</label>
        <div class="col-lg-3">
            <input class="configKey form-control" data-l1key="local_ip"/>
        </div>
        <div class="col-lg-3">
			Adresse IP local sur laquelle le daemon écoute. Laisser 0.0.0.0 pour écouter sur toutes les interfaces réseaux de Jeedom.
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-4 control-label">{{IP Autorisées}}</label>
        <div class="col-lg-3">
            <input class="configKey form-control" data-l1key="authorized_ip"/>
        </div>
        <div class="col-lg-3">
            Format : liste séparé par virgule sans espace. La liste peut contenir des ips (192.168.1.1), des masques ( (192.168.1.0/32) ou des plages (192.168.1.1-192.168.1.12).
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-4 control-label">{{Chemin des enregistrements}}</label>
        <div class="col-lg-3">
            <input class="configKey form-control" data-l1key="recordDir" id="recordDirFtpd"/>
        </div>
        <div class="col-lg-3">
			<a class="btn btn-danger" id="bt_resetDir"><i class="fa fa-check"></i> {{Reinitialisation du répertoire de stockage des captures}}</a>
        </div>
        <div class="col-lg-3">
			Ne pas modifier sauf besoin spécifique.
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-4 control-label">{{Debug daemon}}</label>
        <div class="col-lg-3">
            <input type="checkbox" class="configKey form-control" data-l1key="debug"/>
        </div>
        <div class="col-lg-3">
			Permet d'avoir plus de message dans la log du daemon.
        </div>
    </div>
</form>
<?php include_file('desktop', 'ftpd', 'js', 'ftpd'); ?>