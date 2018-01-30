function addCmdToTable(_cmd) {
   if (!isset(_cmd)) {
        var _cmd = {type: 'info',
					subType: 'binary',
					logicalId: 'pattern',
					display: {
						icon: '',
						invertBinary: '1',
						generic_type: 'PRESENCE'},
					configuration: {}};
    }
    if ( ! isset(_cmd.configuration) ) {
        _cmd.configuration = {};
    }

    if (init(_cmd.type) == 'info') {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" >';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="id" style="display: none;"></span>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}"></td>';
		tr += '</td>';
		if (_cmd.logicalId == 'pattern' ) {
			tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="pattern" style="width : 98%;"></td>';
		} else if (_cmd.logicalId == 'notify' ) {
			tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="notify_dest" placeholder="Destinataire séparés par des virgules" style="margin-bottom : 5px;width : 70%; display : inline-block;"><a class="btn btn-default btn-sm cursor listEquipementNotify" data-input="notify_dest" style="margin-left : 5px;"><i class="fa fa-list-alt "></i> Rechercher équipement</a></td>';
		} else {
			tr += '<td></td>';
		}
		tr += '<td class="expertModeVisible">';
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
        tr += '<span class="cmdAttr form-control type input-sm" data-l1key="subType" value="' + init(_cmd.subType) + '" disabled style="margin-bottom : 5px;"></span>';
		tr += '<input type=hidden class="cmdAttr form-control input-sm" data-l1key="unite" value="">';
        tr += '</td>';
        tr += '<td>';
		if (_cmd.logicalId == 'notify' ) {
			tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="notify_reduce"/> {{Réduit les images envoyées}}<br/></span>';
		}
		tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/> {{Historiser}}<br/></span>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
		if (init(_cmd.subType) == 'binary') {
			tr += '<span class="expertModeVisible"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary" /> {{Inverser}}<br/></span>';
		}
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        }
        tr += '</td>';
		table_cmd = '#table_cmd';
		if ( $(table_cmd+'_'+_cmd.eqType ).length ) {
			table_cmd+= '_'+_cmd.eqType;
		}
        $(table_cmd+' tbody').append(tr);
        $(table_cmd+' tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
    if (init(_cmd.type) == 'action') {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="id" style="display: none;"></span>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
        tr += '</td>';
        tr += '<td>';
        tr += '</td>';
        tr += '<td>';
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
        tr += '<span class="cmdAttr form-control type input-sm" data-l1key="subType" value="' + init(_cmd.subType) + '" disabled style="margin-bottom : 5px;"></span>';
        tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" >';
        tr += '</td>';
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '</td>';
        tr += '</tr>';

		table_cmd = '#table_cmd';
		if ( $(table_cmd+'_'+_cmd.eqType ).length ) {
			table_cmd+= '_'+_cmd.eqType;
		}
        $(table_cmd+' tbody').append(tr);
        $(table_cmd+' tbody tr:last').setValues(_cmd, '.cmdAttr');
        var tr = $(table_cmd+' tbody tr:last');
        jeedom.eqLogic.builSelectCmd({
            id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
            filter: {type: 'info'},
            error: function (error) {
                $('#div_alert').showAlert({message: error.message, level: 'danger'});
            },
            success: function (result) {
                tr.find('.cmdAttr[data-l1key=value]').append(result);
                tr.setValues(_cmd, '.cmdAttr');
            }
        });
    }
}

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function saveEqLogic(_eqLogic) {
	_eqLogic.configuration.mode = $('input[type=radio][name=mode]:checked').val();
	return _eqLogic;
}

$("#table_cmd").delegate(".listEquipementNotify", 'click', function () {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {
      type: 'action',
      subType: 'message'
    }}, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
        calcul.atCaret('insert', result.human);
    });
});

$('.eqLogicDetect').on('click', function() {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/ftpd/core/ajax/ftpd.ajax.php", // url du fichier php
        data: {
            action: "force_detect_ftpd",
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, $('#div_DetectBin'));
        },
        success: function(data) { // si l'appel a bien fonctionné
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
			}
			window.location.reload();
		}
    });
});

  $('#bt_resetDir').on('click',function(){
  		bootbox.confirm('{{Etes-vous sûr de vouloir forcer la reinitialisation du réperoire de stockage des captures ?}}', function (result) {
  			if (result) {
  				$('#recordDirFtpd').value('');
  				savePluginConfig();
  			}
  		});
  	});
