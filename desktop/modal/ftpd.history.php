<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
if (init('id') == '') {
	throw new Exception(__('L\'id ne peut etre vide', __FILE__));
}
$cmd = ftpdCmd::byId(init('id'));
if (!is_object($cmd)) {
	throw new Exception('Commande ID virtuel inconnu : ' . init('id'));
}
$EqLogic = $cmd->getEqLogic();
if (!is_object($EqLogic)) {
	throw new Exception(__('L\'équipement est introuvable : ', __FILE__) . init('id'));
}
if ($EqLogic->getEqType_name() != 'ftpd') {
	throw new Exception(__('Cet équipement n\'est pas de type ftpd : ', __FILE__) . $EqLogic->getEqType_name());
}
$dir = calculPath(config::byKey('recordDir', 'ftpd')) . '/' . $EqLogic->getLogicalId();
$files = array();
if ($handle = opendir($dir))
{
	while (false !== ($file = readdir($handle)))
	{
		if ($file != "." && $file != "..")
		{
			$date = filemtime($dir."/".$file);
			$files[date("Ymd", $date)][date("H:i:s", $date)] = $file;
		}
	}
	closedir($handle);
}
krsort($files);
?>
<div id='div_ftpdRecordAlert' style="display: none;"></div>
<?php
echo '<a class="btn btn-danger bt_removeftpdFile pull-right" data-all="1" data-filtre="' . $EqLogic->getId() . '"><i class="fa fa-trash-o"></i> {{Tout supprimer}}</a>';
echo '<a class="btn btn-success  pull-right" href="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . urlencode($dir . '/*') .'" ><i class="fa fa-download"></i> {{Tout télécharger}}</a>';
?>
<?php
foreach ($files as $date => $file)
{
	echo '<div class="div_dayContainer">';
	echo '<legend>';
	echo substr($date, 6, 2).'/'.substr($date, 4, 2).'/'.substr($date, 0, 4);
	echo ' <a class="btn btn-xs btn-default toggleList"><i class="fa fa-chevron-down"></i></a> ';
	echo '</legend>';
	echo '<div class="ftpdThumbnailContainer">';
	krsort($file);
	foreach ($file as $time => $filename)
	{
   	    if ( ! strpos($filename,'_mini.jpg') )
		{
			echo '<div class="ftpdDisplayCard" style="background-color: #e7e7e7;padding:5px;height:167px;">';
			if ( strpos(mime_content_type($dir . '/' . $filename),'video') !== false )
			{
				echo '<center><i class="fa fa-video-ftpd pull-right"></i>  ' . $time . ' <i class="fa fa-video-camera"></i></center>';
				echo '<center><img class="img-responsive cursor displayVideo lazy" src="plugins/ftpd/core/img/no-image.png" data-original="plugins/ftpd/core/api/ftpd.api.php?action=downloadmini&pathfile=' . urlencode($dir . '/' . $filename) . '" data-src="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . urlencode($dir . '/' . $filename) . '" width="150"/></center>';
			}
			else
			{
				echo '<center><i class="fa fa-ftpd pull-right"></i>  ' . $time . ' <i class="fa fa-photo"></i></center>';
				echo '<center><img class="img-responsive cursor displayImage lazy" src="plugins/ftpd/core/img/no-image.png" data-original="plugins/ftpd/core/api/ftpd.api.php?action=downloadmini&pathfile=' . urlencode($dir . '/' . $filename) . '" data-id='.init('id').' width="150"/></center>';
			}
			echo '<center style="margin-top:5px;"><a href="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . urlencode($dir . '/' . $filename) . '" class="btn btn-success btn-xs" style="color : white"><i class="fa fa-download"></i></a>';
			echo ' <a class="btn btn-danger bt_removeftpdFile btn-xs" style="color : white" data-filtre="' . $EqLogic->getLogicalId() . '/' . $filename . '"><i class="fa fa-trash-o"></i></a></center>';
			echo '</div>';
		}
	}
echo '</div>';
	echo '</div>';
}
?>
<script>
    $('.ftpdThumbnailContainer').packery({gutter : 5});
    $('.displayImage').on('click', function() {
        $('#md_modal2').dialog({title: "Image"});
        $('#md_modal2').load('index.php?v=d&plugin=ftpd&modal=ftpd.displayImage&src='+ $(this).attr('src')+'&id='+ $(this).attr('data-id')).dialog('open');
    });
	$('.displayVideo').on('click', function() {
        $('#md_modal2').dialog({title: "Vidéo"});
        $('#md_modal2').load('index.php?v=d&plugin=ftpd&modal=ftpd.displayVideo&src='+ $(this).attr('data-src')).dialog('open');
    });
    $('.bt_removeftpdFile').on('click', function() {
        var filtre = $(this).attr('data-filtre');
        var card = $(this).closest('.ftpdDisplayCard');
		action = 'removeRecord';
        if($(this).attr('data-day') == 1){
            card = $(this).closest('.div_dayContainer');
        }
        if($(this).attr('data-all') == 1){
            action = 'removeAllSnapshot';
            card = $('.div_dayContainer');
        }
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/ftpd/core/ajax/ftpd.ajax.php", // url du fichier php
            data: {
                action: action,
                filtre: filtre,
            },
            dataType: 'json',
            error: function(request, status, error) {
                handleAjaxError(request, status, error,$('#div_ftpdRecordAlert'));
            },
            success: function(data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_ftpdRecordAlert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            card.remove();
			$(".ftpdThumbnailContainer").slideToggle(1);
			$('.ftpdThumbnailContainer').packery({gutter : 5});
			$(".ftpdThumbnailContainer").slideToggle(1);
        }
    });
    });

    $(".ftpdThumbnailContainer").slideToggle(1);
    $(".ftpdThumbnailContainer").eq(0).slideToggle(1);
    $('.toggleList').on('click', function() {
        $(this).closest('.div_dayContainer').find(".ftpdThumbnailContainer").slideToggle("slow");
    });

    $("img.lazy").lazyload({
      container: $("#md_modal")
  });
</script>