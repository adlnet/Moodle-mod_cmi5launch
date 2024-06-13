<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/* For global cmi5 settings  */

/**
 * Defines the version of cmi5launch
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package mod_cmi5launch
 * @copyright  2023 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes as well as some code from the scorm module (Source code was uncredited).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */




defined('MOODLE_INTERNAL') || die;

use mod_cmi5launch\local\cmi5_connectors;
?>

<script>
      
      function myFunction() {
  alert("I am an alert box!");
}

function openprompt(){

    console.log("I am in openprompt");

    var site = prompt("Please enter the tenant name:", "The cmi5 tenant name. Please enter a name you would like to use");
    if (site != null) {
      // Set the form paramters.
      // I wonder if I can just submit my own form again
      $('#variableName').val(site);

        // Post it.
        $('#settingform').submit();
    }

}
function totokenpage(){

    console.log("To the make a token page");

    
        // Post it.
        $('#settingformtoken').submit();
    

}
//TRy this new func
// Function for popup window
function openprompt3(){
    //open a prompt box to get new tenant name for cmi5launch. Then we can call createtenant function
    // Hold tenant name answer
    var x;
    var site = prompt("Please enter the tenant name:", "The cmi5 tenant name. Please enter a name you would like to use");
    if (site != null) {
        
       // document.getElementById("name").innerHTML = site;
//var p1 =encodeURIComponent(site);
        var dataToSend = "variableName=" + (site);


        ////?????
        var PageToSendTo = "settings.php";
 var MyVariable = "variableData";
 var VariablePlaceholder = "?variableName=";
 var UrlToSend = PageToSendTo + VariablePlaceholder + encodeURIComponent(site);

// Prepare the data to send
var xhr = new XMLHttpRequest();

xhr.open("POST", "", true);
xhr.send(dataToSend);

// Create a new XMLHttpRequest object
//xhr.open("POST", "", true);

// Specify the request method, PHP script URL, and asynchronous
//xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

// Set the content type
xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {

        console.log("where its going  " + xhr.responseURL);
        console.log("staus tect    " + xhr.statusText);
        // Check if the request is complete
        if (xhr.status === 200) {

            // Check if the request was successful
            console.log(xhr.responseText);
            // Output the response from the PHP script
        } else {
            console.error("Error:", xhr.status);
            // Log an error if the request was unsuccessful
        }

    }

}
    ;
//xhr.send(dataToSend);
// Send the data to the PHP script
      // // 
         // test it works and echo it
      //  console.log(site);
 }
}


// Function for popup window
 function openprompt2(){
    //open a prompt box to get new tenant name for cmi5launch. Then we can call createtenant function
    // Hold tenant name answer
    var x;
        var site = prompt("Please enter the tenant name:", "The cmi5 tenant name. Please enter a name you would like to use");
        if (site != null) {
         
         document.getElementById("name").innerHTML = site;
      // // 
         // test it works and echo it
      //  console.log(site);
 }
}


</script>
<?php


// maybe add if ($hassiteconfig?) Can regulare users access this? TODO -MB
if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/cmi5launch/locallib.php');
    require_once($CFG->dirroot . '/mod/cmi5launch/settingslib.php');

    // Varibale to hold answer?
$nameanswer = "";


// Ok let's try to get the answer from the ajax method
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["variableName"])) {
    $receivedVariable = $_POST["variableName"];
        echo "<br>";
    echo" We got it ! it is ! : ". $receivedVariable;   
    echo " IGOT IT!";
    // Process the received variable here
// Check for answer : 
/////////// maybe come back to htis if (isset($_POST['tenantbutton'])) {
     
    // Get the answer
  //  $.get('myFile.php', js_answer: answer);
  //  $nameanswer = $_POST['cmi5launchtenantname'];
    // echo it
 //   echo $_POST["name"];
 $sessionhelper = new cmi5_connectors;
 $maketenant = $sessionhelper->cmi5launch_get_create_tenant();

      //  $pass = $_POST['tenantbutton'];

      echo " What is recieved var : ";
        echo $receivedVariable;  
 $newtenantname = $maketenant($receivedVariable);

    echo $nameanswer;
}
// If it's null dont use it, if its not call func
if ($nameanswer != null) {
    // Call func
    echo" We got it ! it is ! : ". $nameanswer;
    // $maketenant = $sessionhelper->cmi5launch_get_create_tenant();
}
 
    // MB
    // From scorm grading stuff.
    $yesno = array(0 => get_string('no'),
                   1 => get_string('yes'));

    // Default display settings.
    $settings->add(new admin_setting_heading('cmi5launch/cmi5launchlrsfieldset',
        get_string('cmi5launchlrsfieldset', 'cmi5launch'),
        get_string('cmi5launchlrsfieldset_help', 'cmi5launch')));

    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchlrsendpoint',
        get_string('cmi5launchlrsendpoint', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_help', 'cmi5launch'),
        get_string('cmi5launchlrsendpoint_default', 'cmi5launch'), PARAM_URL));

    $options = array(
        1 => get_string('cmi5launchlrsauthentication_option_0', 'cmi5launch'),
        2 => get_string('cmi5launchlrsauthentication_option_1', 'cmi5launch'),
        0 => get_string('cmi5launchlrsauthentication_option_2', 'cmi5launch'),
    );
    // Note the numbers above are deliberately mis-ordered for reasons of backwards compatibility with older settings.

    $setting = new admin_setting_configselect('cmi5launch/cmi5launchlrsauthentication',
        get_string('cmi5launchlrsauthentication', 'cmi5launch'),
        get_string('cmi5launchlrsauthentication_help', 'cmi5launch').'<br/>'
        .get_string('cmi5launchlrsauthentication_watershedhelp', 'cmi5launch')
        , 1, $options);
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchlrslogin',
        get_string('cmi5launchlrslogin', 'cmi5launch'),
        get_string('cmi5launchlrslogin_help', 'cmi5launch'),
        get_string('cmi5launchlrslogin_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchlrspass',
        get_string('cmi5launchlrspass', 'cmi5launch'),
        get_string('cmi5launchlrspass_help', 'cmi5launch'),
        get_string('cmi5launchlrspass_default', 'cmi5launch'));
    $settings->add($setting);

    $settings->add(new admin_setting_configtext('cmi5launch/cmi5launchlrsduration',
        get_string('cmi5launchlrsduration', 'cmi5launch'),
        get_string('cmi5launchlrsduration_help', 'cmi5launch'),
        get_string('cmi5launchlrsduration_default', 'cmi5launch')));

    $settings->add(new admin_setting_configtext('cmi5launch/cmi5launchcustomacchp',
        get_string('cmi5launchcustomacchp', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_help', 'cmi5launch'),
        get_string('cmi5launchcustomacchp_default', 'cmi5launch')));

    $settings->add(new admin_setting_configcheckbox('cmi5launch/cmi5launchuseactoremail',
        get_string('cmi5launchuseactoremail', 'cmi5launch'),
        get_string('cmi5launchuseactoremail_help', 'cmi5launch'),
        1));

        // LEt's add a new header to separate cmi5 from lrs
    $settings->add(new admin_setting_heading('cmi5launch/cmi5launchsettings', get_string('cmi5launchsettingsheader', 'cmi5launch'), ''));


    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchplayerurl',
        get_string('cmi5launchplayerurl', 'cmi5launch'),
        get_string('cmi5launchplayerurl_help', 'cmi5launch'),
        get_string('cmi5launchplayerurl_default', 'cmi5launch'), PARAM_URL));
    /*    
    $settings->add(new admin_setting_configtext_mod_cmi5launch('cmi5launch/cmi5launchcontenturl',
        get_string('cmi5launchcontenturl', 'cmi5launch'),
        get_string('cmi5launchcontenturl_help', 'cmi5launch'),
        get_string('cmi5launchcontenturl_default', 'cmi5launch'), PARAM_URL));
*/


    $setting = new admin_setting_configtext('cmi5launch/cmi5launchbasicname',
        get_string('cmi5launchbasicname', 'cmi5launch'),
        get_string('cmi5launchbasicname_help', 'cmi5launch'),
        get_string('cmi5launchbasicname_default', 'cmi5launch'));
    $settings->add($setting);

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchbasepass',
        get_string('cmi5launchbasepass', 'cmi5launch'),
        get_string('cmi5launchbasepass_help', 'cmi5launch'),
        get_string('cmi5launchbasepass_default', 'cmi5launch'));
    $settings->add($setting);



$warning = "OHNO";
   // $link = "</form><a href=".new moodle_url('/settings.php')." class='btn btn-danger';>Empty all results</a> <strong style='color: red;'>".$warning."</strong>";
   // $clear_url = new moodle_url('/settings.php');
    // For form action we will call new tenant or new token
    // Furthe we can set them as to apperar when made either here or in those funcs being called

    // Info we need to send?
 //$newtenantname;
    $linktotenant = "</br>
    <p id=name >
        <div class='input-group rounded'>
          <button class='btn btn-secondary' type='reset' name='tenantbutton' onclick='openprompt()'>
            <span class='button-label'>Generate tenant</span>
            </button>
        </div>
    </p>
      ";
      $linktotoken = "</br>
      <p id=name >
          <div class='input-group rounded'>
            <button class='btn btn-secondary' type='reset' name='tokenbutton' onclick='totokenpage()'>
              <span class='button-label'>Generate bearer token</span>
              </button>
          </div>
      </p>
        ";
      //$link ="<a href='http://www.google.com' target='_parent'><button>Click me !</button></a>";
    
      $setting = new admin_setting_configtext(
        'cmi5launch/cmi5launchtenantname',
        get_string('cmi5launchtenantname', 'cmi5launch'),
        " " . get_string('cmi5launchtenantname_help', 'cmi5launch') . $linktotenant,
        get_string('cmi5launchtenantname_default', 'cmi5launch')
    );
    $settings->add($setting);
/*
    echo"<br>";
    //echo "<script>document.writeln(p1);</script>";
    //echo"Hey it worked and I cansee receivedVariable " . $receivedVariable; 
    echo "What is settings? Did we change it>???? ";
    $toread = $settings['cmi5launchtenantname'];
    var_dump($toread);
    echo "<br>";
*/

    $setting = new admin_setting_configtext('cmi5launch/cmi5launchtenanttoken',
        get_string('cmi5launchtenanttoken', 'cmi5launch'),
        get_string('cmi5launchtenanttoken_help', 'cmi5launch') . $linktotoken,
        get_string('cmi5launchtenanttoken_default', 'cmi5launch'));
    $settings->add($setting);

/*
    $editstring = "I am a button";

    $url = new moodle_url("$CFG->wwwroot/my/index.php");
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($button);
    $editstring2 = "I am another button";

    $url = new moodle_url("$CFG->wwwroot/my/index.php");
    $button = $OUTPUT->single_button($url, $editstring2);
    $PAGE->set_button($button);
  
  */
    // MB.
    // Grade stuff I'm bringing over.
        // Default grade settings.
    $settings->add(new admin_setting_heading('cmi5launch/gradesettings', get_string('defaultgradesettings', 'cmi5launch'), ''));
    $settings->add(new admin_setting_configselect('cmi5launch/grademethod',
        get_string('grademethod', 'cmi5launch'), get_string('grademethoddesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_GRADE_HIGHEST, cmi5launch_get_grade_method_array()));

    for ($i = 0; $i <= 100; $i++) {
        $grades[$i] = "$i";
    }

    $settings->add(new admin_setting_configselect('cmi5launch/maxgrade',
        get_string('maximumgrade'), get_string('maximumgradedesc', 'cmi5launch'), 100, $grades));

    $settings->add(new admin_setting_heading('cmi5launch/othersettings', get_string('defaultothersettings', 'cmi5launch'), ''));

    // Default attempts settings.
    $settings->add(new admin_setting_configselect('cmi5launch/maxattempt',
        get_string('maximumattempts', 'cmi5launch'), '', '0', cmi5launch_get_attempts_array()),
        get_string('whatmaxdesc', 'cmi5launch'), );

    $settings->add(new admin_setting_configselect('cmi5launch/whatgrade',
        get_string('whatgrade', 'cmi5launch'), get_string('whatgradedesc', 'cmi5launch'),
        MOD_CMI5LAUNCH_HIGHEST_ATTEMPT, cmi5launch_get_what_grade_array()));

    // Not sure if we want to implement mastery override at this time -MB.
    /*
    $settings->add(new admin_setting_configselect('cmi5launch/masteryoverride',
    get_string('masteryoverride', 'cmi5launch'), get_string('masteryoverridedesc', 'cmi5launch'), 1, $yesno));
    */

    $settings->add(new admin_setting_configselect('cmi5launch/MOD_CMI5LAUNCH_LAST_ATTEMPTlock',
        get_string('mod_cmi5launch_last_attempt_lock', 'cmi5launch'), get_string('mod_cmi5launch_last_attempt_lockdesc', 'cmi5launch'), 0, $yesno));


    }

    ?>  
    <form id="settingformtoken" action="../mod/cmi5launch/tokensetup.php" method="get">
 
</form>


 <form id="settingform" action="../mod/cmi5launch/tenantsetup.php" method="get">
        
        <input id="variableName" name="variableName" type="hidden" value="default">

    </form>
