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
			$files[$date] = $file;
		}
	}
	closedir($handle);
}
krsort($files);
$previous = "";
$next = "";
$find = false;
$current_file = basename(init('pathfile'));
foreach ($files as $date => $file) {
	if ( $find )
	{
		$previous = $file;
		break;
	}
	if ( $file == $current_file )
		$find = true;
	else
		$next = $file;
}

echo '<center>';
if ( $previous != "" )
	echo '<button type="button" data-role="none" class="previousImage" aria-label="{{Précédente}}" role="button" style="" data-src="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . urlencode($dir . '/' . $previous) . '" data-id='.init('id').'>{{Précédente}}</button>';
if ( $next != "" )
	echo '<button type="button" data-role="none" class="nextImage" aria-label="{{Suivante}}" role="button" style="" data-src="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . urlencode($dir . '/' . $next) . '" data-id='.init('id').'>{{Suivante}}</button>';
echo '<img class="img-responsive" src="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . init('pathfile') . '"/>';
echo '</center>';
?>
<script>
    $('.previousImage').on('click', function() {
        $('#md_modal2').dialog({title: "Image"});
        $('#md_modal2').load('index.php?v=d&plugin=ftpd&modal=ftpd.displayImage&pathfile='+ $(this).attr('data-src')+'&id='+ $(this).attr('data-id')).dialog('open');
    });
    $('.nextImage').on('click', function() {
        $('#md_modal2').dialog({title: "Image"});
        $('#md_modal2').load('index.php?v=d&plugin=ftpd&modal=ftpd.displayImage&pathfile='+ $(this).attr('data-src')+'&id='+ $(this).attr('data-id')).dialog('open');
    });
</script>
