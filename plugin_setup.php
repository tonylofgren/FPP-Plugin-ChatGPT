<?php
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include_once 'version.inc';
//include_once 'load_settings.php';


if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	logEntry("update result: ". $updateResult);//."<br/> \n";
	
	if(file_exists($settings['pluginDirectory']."/".$pluginName."/fpp_install.sh"))
	{
		$updateInstallCMD = $settings['pluginDirectory']."/".$pluginName."/fpp_install.sh";
		logEntry("running upgrade install script: ".$updateInstallCMD);
		exec($updateInstallCMD,$sysOutput);
		//echo $sysOutput;
	
	} else {
		logEntry("No fpp_install.sh upgrade script available");
	}
}
$pluginName = basename(dirname(__FILE__));

$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$promptQueue_Plugin = "FPP-Plugin-MessageQueue";
$matrixMessage_Plugin = "FPP-Plugin-Matrix-Message";
$promptQueuePluginPath = $settings['pluginDirectory'] . "/" . $promptQueue_Plugin."/";
include $promptQueuePluginPath . "functions.inc.php";

$prompt_QUEUE_PLUGIN_ENABLED=false;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$promptQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE", $promptQueue_Plugin));

if(file_exists( $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script) && file_exists( $pluginDirectory."/".$promptQueue_Plugin )&& file_exists( $pluginDirectory."/".$matrixMessage_Plugin )){
	logEntry($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." EXISTS: Enabling");
	$prompt_QUEUE_PLUGIN_ENABLED=true;

} else {
	if (!file_exists($pluginDirectory."/".$fpp_message_queue_Plugin )) {
		logEntry("Message Queue to Matrix Overlay plugin is not installed, cannot use this plugin with out it");
		echo "<h1>Message Queue to Matrix Overlay is not installed. Install the plugin and revisit this page to continue.</h1><br/>";	
	}
	if (!file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script)) {
	logEntry("FPP Matrix tools plugin is not installed, cannot use this plugin with out it");
	echo "<h1>FPP Matrix Tools plugin is not installed. Install the plugin and revisit this page to continue.</h1>";
	}
	if (!file_exists($pluginDirectory."/".$matrixMessage_Plugin)){
		logEntry("FPP Matrix Message plugin is not installed, cannot use this plugin with out it");
		echo "<h1>FPP Matrix Message plugin is not installed. Install the plugin and revisit this page to continue.</h1>";
	}

	exit(0);
}

$gitURL = "https://github.com/tonylofgren/FPP-Plugin-chatGPT.git";

$Plugin_DBName = $promptQueueFile;
	
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');

//create the default tables if they do not exist!
createTables();

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

$forgetPrevios == "ON" ? $prompt = $prePrompt : null;
$prompt .= $userPrompt;
$tone == "ON" ? $prompt .= ". REPLY WITH THE tone OF ".$toneSelect : null;
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


if(isset($_POST['submit']))
{
echo debugLog("Prompt: ".$prompt);
$chatGPTOutput = queryOpenAI("gpt-3.5-turbo", 0.5, (int) $maxTokens, 1.0, $prompt, $apiKey);
echo debugLog("chatGPTOutput:".$chatGPTOutput['content']);

$promptText = $chatGPTOutput['content'];
$promptTokens = $chatGPTOutput['prompt_tokens'];
$responseTokens = $chatGPTOutput['completion_tokens'];
$totalTokens = $chatGPTOutput['total_tokens'];

$promptText = str_replace('"', '', $promptText);
if (strcasecmp($uppercase, "ON") === 0) {
    $promptText= strtoupper($promptText);
}
}

?>

<html>
<head>
<style>

    .marquee {
      width: 100%;
      overflow: hidden;
      position: relative;
      border: 3px solid black;  /* Svart kant */
      border-radius: 5px;  /* Rundar hörnen */
      white-space: nowrap;
    }

    .marquee p {
      white-space: nowrap;  /* Förhindrar radbrytning */
      display: inline-block;
      padding-left: 100%;
      animation: marquee 30s linear infinite;
      font-weight: bold;  
      font-family: 'Courier New', Courier, monospace;
      font-size: 15em;
      background: white;
      color: green;
      position: relative;
    }

    .help {
      position: relative !important;
      padding: 0.75rem 1.25rem !important;
      margin-bottom: 1rem !important;
      border: 1px solid transparent !important;
      border-radius: 0.25rem !important;
    }

    .help-info {
      color: #004085 !important;
      background-color: #aae5ff !important;
      border-color: #b8daff !important;
    }

    .help-link {
      color: #002752 !important;
      font-weight: 600 !important;
      text-decoration: none !important;
    }

    .help-link:hover {
      text-decoration: underline !important;
    }

    @keyframes marquee {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-100%);
      }
    }
  </style>
</head>

<div id="ChatGPT" class="settings">
<fieldset>
<div class="help help-info">Press F1 for support or <a href="plugin.php?plugin=FPP-Plugin-ChatGPT&page=/help/plugin_setup.php" class="help-link" target="_blank">Click Here</a></div>
<p>Known Issues:
<ul>
<li>None
</ul>

<p>Setup:
<ol type = "1">

<li>Create an account at openAI and generate your API-Key and finnaly paste the API-Key below.</li>
<li>Set max tokens you want to use.</li>
<li>Copy one of the examples into the prompt or write your own prompt.</li>
<li>Select and activate Tone of the message.</li>
<li>Press the TEST button to preview your message.</li>
<li>Enable Output to Matrix</li>
</ol>
<p><b>This plugin requires a paid Open.AI developer account.</b></p>
</div>
<div>

<p>ENABLE PLUGIN: <?PrintSettingCheckbox("chatGPT Plugin", "pluginEnabled", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction=""); ?> </p>
<p>Open AI API Key: <?  PrintSettingTextSaved("apiKey", 0, 0, $maxlength = 100, $size = 50, $pluginName, $defaultValue = "APIKEY", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "password", $sData = array());?> </p>
<p>Max tokens: <?  PrintSettingTextSaved("maxTokens", 0, 0, $maxlength = 5, $size = 5, $pluginName, $defaultValue = "200", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "number", $sData = array());?> </p>


<h4>Some examples, copy and modify for your needs.</h4>
<?
$dropdownOptions = array(
    "Santa Media" => "Act as Santa and present the song name [MEDIA]",
    "DJ Tony" => "You are now a cool famous DJ called DJ Tony at a epic christmas lightshow. Shout out the song [MEDIA]",
    "Santa Date" => "Act as Santa, today is [DATE] respond how many days to christmas and tell a the people to be safe and not block trafic, tune in 91 Mhz",
    "Santa's Elf" => "Act as Santa's Elf and introduce the next Christmas carol [MEDIA] - 'Hey there, little helpers! Elf Jingle here, and I've got a tune that'll make your bells jingle! Up next is [MEDIA]. Let's spread some holiday cheer!'",
    "Radio Host" => "You are a Radio Host on Christmas Eve, announce the time and the next song [MEDIA] - 'Good evening, listeners! It's Christmas Eve and the time is [DATE]. Get ready to feel the holiday spirit with our next track, [MEDIA]!'",
    "Mrs. Claus" => "Act as Mrs. Claus and share a Christmas recipe before playing the song [MEDIA] - 'Hello, dear ones! Mrs. Claus here. Before we play the next heartwarming song [MEDIA], let me share my secret gingerbread cookie recipe with you!'",
    "Tree Salesman" => "You are a Christmas Tree Salesman, advertise your trees and then introduce the song [MEDIA] - 'Get your perfect Christmas tree here, folks! Now, while you browse, let's get into the holiday mood with [MEDIA]!'",
    "Reindeer" => "Act as a Reindeer, talk about your flight practice and then introduce the song [MEDIA] - 'Hey, it's Dasher! Just finished some flight practice for the big night. Time to relax with some tunes. Up next is [MEDIA]!'",
    "Snowman" => "You are a Snowman, talk about the snowy weather and then play the song [MEDIA] - 'Brrr, it's chilly out here! Perfect weather for a snowman like me. Let's warm up with some music. Here's [MEDIA]!'",
    "Santa Letters" => "Act as Santa, remind kids to write their letters and then play the song [MEDIA] - 'Ho ho ho! Don't forget to write your letters, kids! While you're at it, let's listen to [MEDIA]!'",
    "Traffic Cop" => "You are a Holiday Traffic Cop, remind people to drive safely and then play the song [MEDIA] - 'Attention drivers, this is Officer Frosty reminding you to drive safely this holiday season. Now, let's enjoy some safe and sound tunes with [MEDIA]!'",
    "Guardian" => "You are a guardian for the magical christmas lightshow. Tell people. Do Not Block the Road. Turn Off Headlights. Be Respectful to Neighbors. No Trespassing",
    "Grinchen" => "You are the GRINCH and answer as Grinchen, introduce the song [MEDIA] - Well, well, well. It's the Grinch here."
);

$tone = array(
    "Select tone" => "Select tone",
    "Adventurous" => "Tone: Adventurous - Be Bold and Daring",
    "Amused" => "Tone: Amused - Be Entertained and Pleased",
    "Angry" => "Tone: Angry - Be Aggressive and Intense",
    "Anxious" => "Tone: Anxious - Be Nervous and Worried",
    "Apologetic" => "Tone: Apologetic - Be Regretful and Sorry",
    "Chill" => "Tone: Chill - Be Relaxed and Easygoing",
    "Confident" => "Tone: Confident - Be Self-assured and Positive",
    "Curious" => "Tone: Curious - Be Inquisitive and Eager to Learn",
    "Cynical" => "Tone: Cynical - Be Skeptical and Disbelieving",
    "Empathetic" => "Tone: Empathetic - Be Understanding and Compassionate",
    "Enthusiastic" => "Tone: Enthusiastic - Be Energetic and Passionate",
    "Excited" => "Tone: Excited - Be Thrilled and Anticipating",
    "Formal" => "Tone: Formal - Be Professional and Reserved",
    "Funny" => "Tone: Funny - Be Humorous and Light-hearted",
    "Grateful" => "Tone: Grateful - Be Thankful and Appreciative",
    "Halloween" => "Tone: Halloween - Be Spooky and Eerie",
    "Hopeful" => "Tone: Hopeful - Be Optimistic and Full of Hope",
    "Inspirational" => "Tone: Inspirational - Be Uplifting and Motivating",
    "Irritated" => "Tone: Irritated - Be Annoyed and Agitated",
    "Joyful" => "Tone: Joyful - Be Happy and Full of Joy",
    "Kids" => "Tone: Kids - Be Simple and Child-Friendly",
    "Melancholic" => "Tone: Melancholic - Be Sad and Pensive",
    "Motivational" => "Tone: Motivational - Be Encouraging and Inspiring",
    "Mysterious" => "Tone: Mysterious - Be Enigmatic and Intriguing",
    "Neutral" => "Tone: Neutral - Be Unbiased and Objective",
    "Nostalgic" => "Tone: Nostalgic - Be Reflective and Sentimental",
    "Optimistic" => "Tone: Optimistic - Be Hopeful and Positive",
    "Pathetic" => "Tone: Pathetic - Be Dispirited and Weak",
    "Pessimistic" => "Tone: Pessimistic - Be Cynical and Negative",
    "Philosophical" => "Tone: Philosophical - Be Thoughtful and Inquisitive",
    "Poetic" => "Tone: Poetic - Be Artistic and Lyrical",
    "Romantic" => "Tone: Romantic - Be Loving and Affectionate",
    "Sarcastic" => "Tone: Sarcastic - Be Ironic and Cutting",
    "Solemn" => "Tone: Solemn - Be Serious and Formal",
    "Storytelling" => "Tone: Storytelling - Be Narrative and Descriptive",
    "Whimsical" => "Tone: Whimsical - Be Playful and Fanciful"
);
?>
<ol>
    <?php
    foreach ($dropdownOptions as $value) {
        echo "<li>$value</li>";
    }
    ?>
</ol>

<p>Prompt: <?  PrintSettingTextSaved("userPrompt", 0, 0, $maxlength = 500, $size = 200, $pluginName, $defaultValue = "You are now a cool famous DJ called DJ Jingle Beats at Tonys epic christmas lightshow. Shout out the song [MEDIA]", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "text", $sData = array());?> </p>

<p>Ignore previos info (Recommended ): <?PrintSettingCheckbox("forgetPrevios", "forgetPrevios", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?> </p>
<p>Max respone words: <?  PrintSettingTextSaved("maxWords", 0, 0, $maxlength = 5, $size = 5, $pluginName, $defaultValue = "100", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "number", $sData = array());?> </p>
<p>Include tone: <?PrintSettingCheckbox("tone", "tone", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?><?php PrintSettingSelect("tone", "toneSelect", "0", "0", "disabled", $tone, $pluginName); ?>
<p>Convert to uppercase: <?PrintSettingCheckbox("uppercase", "uppercase", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?> </p>

<div class= "marquee" id="scroll-container" >
<p id="scroll-text">Your response, based on your ChatGPT prompt, will appear here once you press the "TEST" button.<p>
</div>
<form method="post" action="/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">
<input id="submit_button" name="submit" type="submit" class="buttons" value="TEST">
</form>
<?
echo "prompt tokens: ".$promptTokens."<br>";
echo "Response tokens: ".$responseTokens."<br>";
echo "Total tokens: ".$totalTokens."<br>";
?>


<p>Output to text to speech (Not implemented): <?PrintSettingCheckbox("TTS", "TTS", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?> </p>
<p>Output to text to RDS (Not implemented): <?PrintSettingCheckbox("RDS", "RDS", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?> </p>
<input type=hidden name=LAST_READ value= <? $LAST_READ ?>>
<p><h3>If you want your message to be displayed immediately when ChatGPT is activated, enable the "Immediate Output" option. Otherwise, the message will be stored in the Matrix Message Queue until you issue the command to run it.</h3>
<p>Immediate Output to Matrix (Run MATRIX plugin): <? PrintSettingCheckbox("Immediate output to Matrix", "IMMEDIATE_OUTPUT", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = ""); ?> </p>
<p>The Matrix message Plugin location should be the default of 127.0.0.1 unless you have a specialized installation configuration.</p>
MATRIX Message Plugin Location: ;<?  PrintSettingTextSaved("MATRIX_LOCATION", 0, 0, $maxlength = 15, $size = 15, $pluginName, $defaultValue = "127.0.0.1", $callbackName = "", $changedFunction = "", $inputType = "text", $sData = array());?> 

<p>To report a bug, visit Git:<? echo $gitURL;?> 
</fieldset>
</div>
<br />
<script>
<?php
if ($promptText !== null) {
  echo "updateOutputTextChatGPT(".json_encode($promptText).");";
} else {
  echo "console.error('getChatGPT returned null or undefined');";
}
?>

function updateOutputTextChatGPT(newText) {
	document.getElementById("scroll-text").innerHTML = newText;
}

function updateTextField(selectedValue) {
    document.getElementById('userPrompt').value = selectedValue;
}
</script>
</html>