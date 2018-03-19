require_once '../core/class/ftpd.class.php' ;
require_once './eqLogic.class.php' ;

include '../plugin_info/install.php';

ftpd::forceDetectFtpd();
$instance = new ftpd;
$instance->setConfiguration('mock_date',"2018-03-07");
$instance->setConfiguration('mock_file',"veolia_sudest_data/veolia_html_3March.htm");
$instance->displayConfig();
