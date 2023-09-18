<?php

$pages = Array(
	'name' => "ChatGPT AI Matrix",
	'type' => "output",
	'page' => "plugin_setup.php");

?>

<li><a href="<?php echo preg_replace('/.*\/plugins\/(.*)/', 'plugin.php?plugin=$1&page=plugin_setup.php', dirname(__FILE__)); ?>">ChatGPT - Setup</a></li
>
