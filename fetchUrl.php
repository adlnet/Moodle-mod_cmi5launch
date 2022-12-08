<?php
     // namespace cmi5;   
 
        class remoteMoodle
{
    use ArraySetterTrait;
   

        use cmi5\Activity;
use cmi5\Agent;
use cmi5\Attachment;
use cmi5\Person;
use cmi5\RemoteLRS;
use cmi5\Statement;
use cmi5\StatementRef;
use cmi5\Util;
use cmi5\Verb;
use cmi5\Version
        //require_once(dirname(dirname(dirname(__FILE__))).'LRSInterface.php');
        //require_once('RemoteLRS.php');
        //require_once('ArraySetterTrait.php');
        //we had to create an instance of class and instantiaite below
public function createTenant(){ 
        $lrs = new RemoteLRS();
////////////////////////IS EVERYTHING HERE?? CAN WE CONTINUE?
        var_dump($_GET);
            $tenantName = htmlspecialchars($_POST["textboxForName"] ?? "", ENT_QUOTES);
            //$userName = htmlspecialchars($_POST["textboxForUser"] ?? "", ENT_QUOTES);
            //$password = htmlspecialchars($_POST["textboxForPassword"] ?? "", ENT_QUOTES);

            echo"<div class=\"feedback\">TenantName: $tenantName<br>Username: $userName<br>Password: $password</div>";
        $versioned_statements = "code=bob";

            $requestCfg = array(
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'content' => json_encode($versioned_statements, JSON_UNESCAPED_SLASHES),
            );
            //if (! empty($attachments_map)) {
            //    $$lets->_buildAttachmentContent($requestCfg, array_values($attachments_map));
          //  }
           //I instantiated it correctly!!!~!~
           $response = $lets->sendRequest('POST', $requestCfg);

            if ($response->success) {
                $parsed_content = json_decode($response->content, true);
                foreach ($parsed_content as $i => $stId) {
                    $statements[$i]->setId($stId);
                }
    
                $response->content = $statements;
            }
            //echo "$response";
            echo"worked?";

           // return $response;
}
       
}
?>

        <form method="post" action="">
            <div class="feedback">
            <label for="name">Tenant Name</label>
            <input type="text" name="textboxForName">
            </div>
        
            <div class="feedback">
            <label for="name">Username</label>
            <input type="text" name="textboxForUser">
            </div>

            <div class="feedback">
            <label for="name">Password</label>
            <input type="text" name="textboxForPassword">
            </div>

            <input type="submit" name="submit" value="Register" class="btn btn-primary">
        </form>

    </body>


</html>