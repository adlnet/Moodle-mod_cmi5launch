<!DOCTYPE html>
<html>
    <head>
        <title>Trial Tenant</title>
    </head>

    <body>

    <h1>Tenant retrieval form</h1>
<?php

        ?>

        <form method="post" action="tenant.php">
            <div class="feedback">
            <label for="name">New Tenant Name</label>
            <input type="text" name="textboxForNewName">
            </div>
        
            <div class="feedback">
            <label for="name">Username</label>
            <input type="text" name="textboxForUsername1">
            </div>

            <div class="feedback">
            <label for="name">Password</label>
            <input type="text" name="textboxForPassword1">
            </div>

            <div class="feedback">
            <label for="name">URL</label>
            <input type="text" name="textboxForURL1">
            </div>

            <input type="submit" name="Register" value="Confirm" class="btn btn-primary">
        </form>

        <br>
        <h1>Token retrieval form</h1>
        <br>
        
        <form method="post" action="tenant.php">
            <div class="feedback">
            <label for="name">Audience</label>
            <input type="text" name="textboxAudience">
            </div>
        
            <div class="feedback">
            <label for="name">Tenant Id</label>
            <input type="text" name="textboxForId">
            </div>

            <div class="feedback">
            <label for="name">Username</label>
            <input type="text" name="textboxForUser">
            </div>

            <div class="feedback">
            <label for="name">Password</label>
            <input type="text" name="textboxForPassword">
            </div>

            <div class="feedback">
            <label for="name">URL</label>
            <input type="text" name="textboxForURL">
            </div>

            <input type="submit" name="GetToken" value="Confirm" class="btn btn-primary">
        </form>

        <br>
        <h1>Retrieve URL</h1>
        <br>
        
        <form method="post" action="tenant.php">
            <div class="feedback">
            <label for="name">Homepage</label>
            <input type="text" name="textboxHomepage">
            </div>
        
            <div class="feedback">
            <label for="name">Tenant name for URL</label>
            <input type="text" name="textboxNameforUrl">
            </div>

            <div class="feedback">
            <label for="name">Return URL</label>
            <input type="text" name="textboxForReturnUrl">
            </div>

            <input type="submit" name="GetURL" value="Confirm" class="btn btn-primary">
        </form>

        <br>
        <h1>Upload course</h1>
        <br>

        <form method="post" action="tenant.php" enctype="multipart/form-data">
            <div class="feedback">
            <p>Enter course file, needs to be zip</p>
            <input type="file" name="fileToUpload" id="fileToUpload" accept="zip/*">
            
            </div>
        

            <input type="submit" name="uploadCourse" value="Confirm" class="btn btn-primary">
        </form>


    </body>


</html>