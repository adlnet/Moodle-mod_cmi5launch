<!DOCTYPE html>

<html>
<body>

<h2>Lets try to make a post and paste tenant with echo</h2>

<?php 
//but thhere is also his?public HttpRequest::__construct ([ string $url [, int $request_method = HTTP_METH_GET [, array $options ]]] )
echo "<br>";
echo $_SERVER['PHP_SELF'];
echo "<br>";
echo $_SERVER['SERVER_NAME'];
echo "<br>";
echo $_SERVER['HTTP_HOST'];
echo "<br>";
echo $_SERVER['HTTP_REFERER'];
echo "<br>";
echo $_SERVER['HTTP_USER_AGENT'];
echo "<br>";
echo $_SERVER['SCRIPT_NAME'];

//require_once($CFG->dirroot.'/curlLib.php');
use curl;

//global $CFG;
//require_once($CFG->libdir . '/curlLib.php');
require 'curlLib.php';
echo "<br>";
echo "Curl";
$curl = new curl();

echo "<br>";
echo "After Curl";
$html = $curl->post('http://127.0.0.1:63398/api/v1/tenant', array('code'=>'chocolate', 'name'=>'BasicKey', 'passwd'=>'BasicSecret'));

echo "<br>";
echo "res?";
echo $html;

//request($url, $options = array()) {
//public HttpRequest::__construct ([ string $url [, int $request_method = HTTP_METH_GET [, array $options ]]] )
/////////////////////straight from remoteLRS.php

///this seems to be how they pass config to sendRequest
/*$requestCfg = array(
    'headers' => array(
        'Content-Type' => $contentType,
    ),
    'params' => array(
        'agent'     => json_encode($agent->asVersion($this->version)),
        'profileId' => $id,
    ),
    'content' => $content,
);
///lets implement the code from plugin here with like a doodad or something
*/

?>

</body>
</html>