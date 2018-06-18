require_once '../core/class/Ftpd.class.php' ;
require_once './eqLogic.class.php' ;

include '../plugin_info/install.php';

Ftpd::forceDetectFtpd();
$instance = new Ftpd;
$instance->setConfiguration('mock_date',"2018-03-07");
$instance->setConfiguration('mock_file',"veolia_sudest_data/veolia_html_3March.htm");
$instance->displayConfig();
