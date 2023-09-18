#!/usr/bin/php
<?

error_reporting(0);
//
//Version 1 for release
$pluginName = basename(dirname(__FILE__));

$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
if (strpos($pluginName, "FPP-Plugin") !== false) {
    $messageQueue_Plugin = "FPP-Plugin-MessageQueue";
}
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$skipJSsettings = 1;

include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include_once 'version.inc';

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile)){
	$pluginSettings = parse_ini_file($pluginConfigFile);
}else{
	$pluginSettings = array(); //There have been no settings saved by the user, create empty array
}

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}


$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
if (strpos($pluginName, "FPP-Plugin") !== false) {
    $MATRIX_MESSAGE_PLUGIN_NAME = "FPP-Plugin-Matrix-Message";
}
//page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php";

require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');

$defaultSettings = [
    'pluginEnabled' => "OFF",
    'IMMEDIATE_OUTPUT' => "OFF",
    'MATRIX_LOCATION' => "127.0.0.1",
    'debug' => false,
    'apiKey' => "",
    'userPrompt' => "Act as Santa and present the song name [MEDIA]",
    'prePrompt' => "Ignore previos questions.",
    'uppercase' => "ON",
    'maxTokens' => 200,
    'toneSelect' => "",
    'tone' => "OFF",
    'maxWords' => "100",
    'forgetPrevios' => "ON"
];

foreach ($defaultSettings as $key => $defaultValue) {
    if (isset($pluginSettings[$key])) {
        $$key = $pluginSettings[$key];

    } else {
        $$key = $defaultValue;
        logEntry("$key not found, using default: $defaultValue");
        echo debugLog("$key not found, using default: $defaultValue");
    }
}



if (!empty($argv[1])) {
    $prompt = "Ignore previos questions." . $argv[1];
} else {
    
    $forgetPrevios == "ON" ? $prompt = $prePrompt : null;
    $tone == "ON" ? $prompt .= ". REPLY WITH THE tone OF " . $toneSelect . ": " : null;
    $prompt .= $userPrompt;
}

$prompt .= ". (LIMIT RESPONSE TO MAX ".$maxWords. " WORDS)";

$mp3FileName = getMP3FileName();
$timeStamp = time();
$DATE_TIME = date("Y-m-d h:i A", $timeStamp);

$variables = [
    "[MEDIA]" => '"' . $mp3FileName . '"',
    "[DATE]" => 'DATETIME ' . $DATE_TIME
];

foreach ($variables as $key => $value) {
    $prompt = str_replace($key, $value, $prompt);
}

$prompt = str_ireplace(".mp3", "", $prompt);
$prompt = str_ireplace(".mp4", "", $prompt);

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;

if (file_exists($pluginConfigFile)){
	$pluginSettings = parse_ini_file($pluginConfigFile);
}else{
	$pluginSettings = array(); //There have been no settings saved by the user, create empty array
}
strtoupper($IMMEDIATE_OUTPUT) !== "ON" ? logEntry("Immediate Output not specifically defined, using default") : null;
strtoupper($pluginEnabled) !== "ON" ? (logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use") && lockHelper::unlock() && exit(0)) : null;

$chatGPTOutput = queryOpenAI("gpt-3.5-turbo", 0.5, (int) $maxTokens, 1.0, $prompt, $apiKey);
echo debugLog("chatGPTOutput:".$chatGPTOutput['content']);

$promptReply = $chatGPTOutput['content'];
$promptReply = str_replace('"', '', $promptReply);

if ($uppercase == "ON") {
    $promptReply = strtoupper($promptReply);
    echo debugLog("Uppercase Active:".$promptReply);    
}

//want to reply even if locked / disabled
if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);
}
$promptReply = preg_replace('!\s+!', ' ', $promptReply);

logEntry("Adding message ".$promptReply. " to message queue: " . $pluginName);
if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
	addNewMessage($messageText,$pluginName,$EVENT_NAME);
} else {
	logEntry("MessageQueue plugin is not enabled/installed: Cannot add message: ".$promptReply);
}

if($IMMEDIATE_OUTPUT != "ON") {
	logEntry("NOT immediately outputting to matrix");
} else {
	logEntry("IMMEDIATE OUTPUT ENABLED" );
	
	$pluginLatest = time ();
	
	$MATRIX_ACTIVE = true;
	logEntry ( "Data for curl command");
	logEntry ( "Matrix location: " . $MATRIX_LOCATION );
	logEntry ( "Matrix Message Plugin: " . $MATRIX_MESSAGE_PLUGIN_NAME );
	logEntry ( "Matrix Exec page: " . $MATRIX_EXEC_PAGE_NAME );
	logEntry ( "plugin name: " . $pluginName );
	
	
	WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );

	logEntry ( "MATRIX ACTIVE: " . $MATRIX_ACTIVE );
	
	$curlURL = "http://" . $MATRIX_LOCATION . "/plugin.php?plugin=" . $MATRIX_MESSAGE_PLUGIN_NAME . "&page=" . $MATRIX_EXEC_PAGE_NAME . "&nopage=1&subscribedPlugin=" . $pluginName . "&onDemandMessage=" . urlencode ( $promptReply );
	logEntry ( "curlURL: " . $curlURL );
    echo debugLog("curlURL: " . $curlURL);
	if ($DEBUG)
		logEntry ( "MATRIX TRIGGER: " . $curlURL );
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $curlURL );
		
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_WRITEFUNCTION, 'do_nothing' );
		curl_setopt ( $ch, CURLOPT_VERBOSE, false );
		
		$result = curl_exec ( $ch );
		logEntry ( "Curl result: " . $result ); 
		curl_close ( $ch );
		
		$MATRIX_ACTIVE = false;
		WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );
}
	lockHelper::unlock();
	exit(0);
?>
