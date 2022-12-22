<?php
namespace myWork;
require_once("./cmi5Connector.php");



buttonPushed();

//Does the whole thing need to be a func to hold global ariableS?

function buttonPushed() {

//to hold variables gather from forms
$newTenantName = htmlspecialchars($_POST["textboxForNewName"] ?? "", ENT_QUOTES);
$firstUserName = htmlspecialchars($_POST["textboxForUsername1"] ?? "", ENT_QUOTES);
$firstPassword = htmlspecialchars($_POST["textboxForPassword1"] ?? "", ENT_QUOTES);
$firstUrl = htmlspecialchars($_POST["textboxForURL1"] ?? "", ENT_QUOTES);

$userName = htmlspecialchars($_POST["textboxForUser"] ?? "", ENT_QUOTES);
$audience = htmlspecialchars($_POST["textboxAudience"] ?? "", ENT_QUOTES);
$tenantId = htmlspecialchars($_POST["textboxForId"] ?? "", ENT_QUOTES);
$password = htmlspecialchars($_POST["textboxForPassword"] ?? "", ENT_QUOTES);
$url = htmlspecialchars($_POST["textboxForURL"] ?? "", ENT_QUOTES);

$homepage = htmlspecialchars($_POST["textboxHomepage"] ?? "", ENT_QUOTES);
$actorName = htmlspecialchars($_POST["textboxNameforUrl"] ?? "", ENT_QUOTES);
$returnUrl = htmlspecialchars($_POST["textboxForReturnUrl"] ?? "", ENT_QUOTES);

//to bring in functions
$connectors = new cmi5Connectors;

  //To hold variables overall, aka after manipulation from functions
$returnedTenName = "";
$returnedTenId = "";
//$//returnedToken = "";
global $bearerToken;

//Check which button was pushed
if (isset($_POST['Register'])) {
    echo"Register button pushed";
    echo"<div class=\"feedback\">newTenantName: $newTenantName<br>Username: $firstUserName<br>Password: $firstPassword</div><br>URL: $firstUrl</div>";

  $createTenant = $connectors->getCreateTenant();
   
  //will create a new tenant
    $tenantInfo = $createTenant($firstUrl, $firstUserName, $firstPassword, $newTenantName);
    echo"<br>";
    echo"<br>";
  echo"wtf";
    //var_dump($tenantInfo);

    $returnedTenName = $tenantInfo['code'];
    $returnedTenId = $tenantInfo['id'];
        echo"<br>";
        echo"returned name is $returnedTenName";
        echo"<br>";
        echo"returned id is $returnedTenId";

}elseif (isset($_POST['GetToken'])) {

  
    echo"Get Token button pushed";
    echo"<div class=\"feedback\">Username: $userName<br>Audience: $audience</div><br>Tenent ID: $tenantId</div><br>Password: $password</div><br>URL: $url</div>";

    $retrieveToken = $connectors->getRetrieveToken();

    //will retreive tenants bearer token
  $returnedToken = $retrieveToken($url, $userName, $password, $audience, $tenantId);

  $bearerToken = $returnedToken;


} elseif (isset($_POST['GetURL'])) {

  echo "Get Token button pushed";
  echo "<div class=\"feedback\">Actor name: $actorName<br>Homepage URL: $homepage</div><br>Return URL: $returnUrl</div>";
  
  //$actorName = "your";
  $courseId = "9";
  $auIndex = "0";

  //URL to send request to
  $url = "http://localhost:63398/api/v1/course/{$courseId}/launch-url/{$auIndex}";


  echo "<br>";
  echo "Can it see returned TOken here?" .   $_POST['bearerToken'];
  echo "<br>";
  //No it CANN>OT because we keep resubmitting form so lea=ts hardcode for know to practice

  $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJ1cm46Y2F0YXB1bHQ6cGxheWVyIiwiYXVkIjoidXJuOmNhdGFwdWx0Ok1vb2RsZSIsInN1YiI6MywianRpIjoiM2FlZDliNDEtYjk4Yy00NmJiLTkzYmUtMmM5ZTRiMzhlZWUxIiwiaWF0IjoxNjcwMjYwNzE2fQ.8w0QiKNE4RMOyLMcktfX_fruhS1i-kfqlfaWO2WYJP4";


  $retrieveUrl = $connectors->getRetrieveUrl();
  $result = $retrieveUrl($actorName, $homepage, $returnUrl, $url, $token);

  if ($result === FALSE) {
    echo "Something went wrong!";
    echo "<br>";
    var_dump($_SERVER);
  } else {

    $launchResponse = json_decode($result, true);

    $sessionId = $launchResponse['id'];
    $launchMethod = $launchResponse['launchMethod'];
    $launchUrl = $launchResponse['url'];

  }

  echo "<br>";
  echo "<br>";

  echo "<br>";
  echo "Let's make sure the decode worked. The returned session id is {$sessionId}, and the launch method is {$launchMethod}, and the long await URL is {$launchUrl}";

  echo "<br>";
  echo "<br>";
}
//to upload course
  elseif (isset($_POST['uploadCourse'])) {
    echo "Post course button pushed";
    
    //The url to upload course to
    $targetUrl = "http://127.0.0.1:63398/api/v1/course";
    //The file brought in from form
   $courseFile =  basename($_FILES["fileToUpload"]["full_path"]);

    echo "<br>";
   echo "What is this showing as??? " . $courseFile;

    $test = implode("", $_FILES);
    echo "<br>";
    echo"Ok, can I see the 'array'" . $test;
    var_dump($_FILES);
    ///Ok it's an empty array, why? 

    //  

};

}
?>