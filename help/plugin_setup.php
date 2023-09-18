<style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        ul {
            list-style-type: disc;
            margin-left: 40px;
        }
        code {
            background-color: #f4f4f4;
            padding: 4px;
            font-size: 1em;
        }
        img {
            max-width: 400px;
            margin-top: 10px;
        }
 </style>
 <h1>Step-by-Step Guide: Creating an OpenAI Account and API Keys for ChatGPT</h1>

<h2>Creating an OpenAI Account</h2>
<ul>
    <li>Navigate to <a href="https://www.openai.com/" target="_blank">OpenAI's official website</a>.</li>
    <li>Select the "Sign Up" or "Register" button, commonly found in the top-right corner of the homepage.</li>
    <li><img src="https://raw.githubusercontent.com/tonylofgren/FPP-Plugin-ChatGPT/master/help/images/createaccount2.png"></li>
    <li>Provide the necessary details such as your email address, name, and desired password.</li>
    <li>Confirm your email by clicking the verification link sent to your inbox.</li>
    <li>Access your newly established OpenAI account by logging in.</li>
</ul>

<h2>About the Trial Period</h2>
<ul>
    <li>Upon account creation, you might qualify for a trial period.</li>
    <li>This trial typically grants limited API access for a specified duration or usage limit.</li>
    <li>Utilize this period to explore and evaluate the API’s capabilities before opting for a paid subscription.</li>
    <li>Thoroughly review the trial period's terms and conditions.</li>
</ul>

<h2>Generating API Keys for ChatGPT</h2>
<ul>
    <li>Go to the API section, generally accessible via your account settings or dashboard.</li>
    <li><img src="https://raw.githubusercontent.com/tonylofgren/FPP-Plugin-ChatGPT/master/help/images/apikey.png"></li>
    <li>Select "New API Key" or an equivalent option to initiate the key generation process.</li>
    <li><img src="https://raw.githubusercontent.com/tonylofgren/FPP-Plugin-ChatGPT/master/help/images/key.png"></li>
    <li>Complete the required fields or settings to configure your new API key.</li>
    <li>Click "Create secret key" to finalize the key.</li>
    <li>Secure your API key as it will be displayed only once; refrain from sharing it.</li>
    <li>Paste this API key into the 'APIKEY' field within the FPP plugin.</li>
</ul>

<h2>Plugin Setup</h2>
<ul>
    <li>Paste the API key into the "OpenAI API Key" textbox.</li>
    <li>If necessary, tweak the "Max Tokens" setting. This parameter influences the total token count, affecting both the cost and the quality of the responses.</li>
    <li>Sample prompts are available for quick testing; you can also customize these or create your own.</li>
    <li>Specify your desired output length in terms of maximum words. Adjust the token count if the message gets truncated.</li>
    <li>Enable the "Ignore Previous" option to prevent ChatGPT from referencing prior requests.</li>
    <li>Toggle "Include Tone" to modify the response tone as desired.</li>
    <li>Use the "TEST" button for a webpage-based preview. Note that song names will only appear if music is currently playing. (This action will not output to the real matrix)</li>
    <li>Check the token usage displayed at the bottom to fine-tune the "Max Tokens" setting.</li>
    <li>Enable "Immediate Output" for real-time message delivery to the matrix.</li>
</ul>

<h2>Integration in Shows</h2>
<p>ChatGPT can be triggered in multiple ways within your playlist or when a new sequence commences.</p>
<h4>Playlist Integration</h4>
<p>
To add ChatGPT to a playlist, create a new Script Entry and select RUN-CHATGPT-SCRIPT.sh. Leave the 'Args:' field blank for the default prompt or input a custom one. Note that song names won't be included if [MEDIA] is activated before the sequence starts.
</p>
<h4>Command Presets</h4>
<p>
Click the "+Add" button and select "SEQUENCE_STARTED" under the "PRESET NAME" field. From the "FPP COMMAND" dropdown, choose "Run Script" and then select RUN-CHATGPT-SCRIPT.sh. For custom prompts, enter them in the 'Script Arguments:' field.
</p>
<p><img src="https://raw.githubusercontent.com/tonylofgren/FPP-Plugin-ChatGPT/master/help/images/command_presets.png"></p>

<h2>Important Note</h2>
<p>Maintain the confidentiality of your API keys to avoid unauthorized access. Should you need to invalidate an API key, deletion is usually the way to go—but exercise this option cautiously.</p>
