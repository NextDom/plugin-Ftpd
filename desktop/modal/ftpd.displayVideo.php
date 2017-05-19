<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
echo '<video width="1180" height="664" controls autoplay loop>
  <source src="plugins/ftpd/core/api/ftpd.api.php?action=downloadcapture&pathfile=' . init('pathfile') . '">
Your browser does not support the video tag.
</video>';
?>