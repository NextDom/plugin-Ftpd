function addCmdToTable(_cmd) {
   if (!isset(_cmd)) {
        var _cmd = {type: 'info',
					subType: 'binary',
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
		if (_cmd.logicalId == 'state' || _cmd.logicalId == 'lastfilename' ) {
			tr += '<td></td>';
		} else {
			tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="pattern" style="width : 98%;"></td>';
		}
		tr += '<td>';
		tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icone</a>';
		tr += '<span class="cmdAttr cmdAction" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
        tr += '</td>';
		tr += '<td class="expertModeVisible">';
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
        tr += '<span class="cmdAttr form-control type input-sm" data-l1key="subType" value="' + init(_cmd.subType) + '" disabled style="margin-bottom : 5px;"></span>';
		tr += '<input type=hidden class="cmdAttr form-control input-sm" data-l1key="unite" value="">';
        tr += '</td>';
        tr += '<td>';
		tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/> {{Historiser}}<br/></span>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
		if (init(_cmd.subType) == 'binary') {
			tr += '<span class="expertModeVisible"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary" /> {{Inverser}}<br/></span>';
		}
        tr += '</td>';
        tr += '<td>';
		if (_cmd.logicalId != 'state' && _cmd.logicalId != 'lastfilename' ) {
			tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i><br>';
		}
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
}

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function saveEqLogic(_eqLogic) {
	_eqLogic.configuration.mode = $('input[type=radio][name=mode]:checked').val();
	return _eqLogic;
}

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
