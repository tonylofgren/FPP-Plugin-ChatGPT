<?php
function logEntry($data,$logLevel=1) {

	global $logFile,$myPid, $LOG_LEVEL;

	
	if($logLevel <= $LOG_LEVEL) 
		return
		
		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
		
		$logWrite= fopen($logFile, "a") or die("Unable to open file!");
		fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
		fclose($logWrite);
}
function debugLog($message)
{
    global $debug;
    if ($debug) {
        echo "<hr>" . $message . "<hr>";
    }
}
function removeSpecialChars($string) {
    return preg_replace('/[\r\n\t]|[^A-Za-z0-9\s-]/', '', $string);
}
function queryOpenAI($model, $temperature, $maxTokens, $top_p, $prompt, $apiKey) {
    $data = [
        "model" => $model,
        'messages' => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        'temperature' => $temperature,
        "max_tokens" => $maxTokens,
        "top_p" => $top_p,
    ];
 
    echo debugLog("queryOpenAI:".json_encode($data));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];
    
    echo debugLog("Headers:".json_encode($headers));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
         return ['error' => curl_error($ch)];

    }

    curl_close($ch);
    $responseArray = json_decode($response, true);
        echo debugLog("Response:".json_encode($response));

    $completion_tokens = $responseArray['usage']['completion_tokens'];
    $prompt_tokens = $responseArray['usage']['prompt_tokens'];
    $total_tokens = $responseArray['usage']['total_tokens'];
    $content = $responseArray['choices'][0]['message']['content'];
    
    return [
        'completion_tokens' => $completion_tokens,
        'prompt_tokens' => $prompt_tokens,
        'total_tokens' => $total_tokens,
        'content' => $content
    ];
}

function getMP3FileName() {
    $url = 'http://127.0.0.1/api/fppd/status';
    $response = file_get_contents($url);

    if ($response !== false) {
        $data = json_decode($response, true);

        if (isset($data['current_song'])) {
            return $data['current_song'];
        } else {
            return "MP3 file name could not be found in the JSON data.";
        }
    } else {
        return "Failed to fetch data from the web address.";
    }
}
?>
