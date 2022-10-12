<?php
use Cleantalk\CleantalkRequest;
use Cleantalk\Cleantalk;
use Cleantalk\CleantalkHelper;
if(!isset($_SESSION['ct_submit_time']))
{
    $_SESSION['ct_submit_time'] = time();
}
class Validate extends ValidateCore {

    public static function spamCheckUser($name = '', $email = '') 
    { 
        $object_dir = _PS_MODULE_DIR_.'cleantalkantispam/lib/php-antispam';
        require_once ($object_dir)."/Cleantalk.php";
        require_once ($object_dir)."/CleantalkRequest.php";
        require_once ($object_dir)."/CleantalkResponse.php";
        require_once ($object_dir)."/CleantalkHelper.php";
        $config_url = 'https://moderate.cleantalk.org';
        $access_key = Configuration::get('access_key');
        $ct_request = new CleantalkRequest();
        $ct_request->auth_key = $access_key; 
        $ct_request->agent = 'php-api'; 
        $ct_request->sender_email = $email; 
        $ct_request->sender_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        $ct_request->sender_nickname = $name; 
        $ct_request->submit_time = time() - (int) $_SESSION['ct_submit_time'];
        $ct_request->js_on = 1; 
        $ct = new Cleantalk();
        $ct->server_url = $config_url; 
        // Check 
        $ct_result = $ct->isAllowUser($ct_request); 

        return $ct_result;   
    } 
    public static function spamCheckMessage($name = '', $email = '', $message = '') 
    { 
        $object_dir = _PS_MODULE_DIR_.'cleantalkantispam/lib/php-antispam';
        require_once ($object_dir)."/Cleantalk.php";
        require_once ($object_dir)."/CleantalkRequest.php";
        require_once ($object_dir)."/CleantalkResponse.php";
        require_once ($object_dir)."/CleantalkHelper.php";
        $config_url = 'https://moderate.cleantalk.org';
        $access_key = Configuration::get('access_key');
        $ct_request = new CleantalkRequest(); 
        $ct_request->auth_key = $access_key; 
        $ct_request->agent = 'php-api'; 
        $ct_request->sender_email = $email; 
        $ct_request->sender_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        $ct_request->sender_nickname = $name; 
        $ct_request->submit_time = time() - (int) $_SESSION['ct_submit_time'];
        $ct_request->message = $message; 
        $ct_request->js_on = 1; 
        $ct = new Cleantalk(); 
        $ct->server_url = $config_url; 
        // Check 
        $ct_result = $ct->isAllowMessage($ct_request);

        return $ct_result; 
    }
}
