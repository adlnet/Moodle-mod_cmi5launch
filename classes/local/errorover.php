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

/**
 * Error class with overridden functions for error and warning handling.
 *
 * @copyright  2024 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\local;

/**
 * An exception handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function exception_au(\Throwable $exception)
{
    throw new nullException('Error OVER: ' . $exception->getMessage(), 0);
}

/**
 * An exception handler to use in grade cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function exception_grade(\Throwable $exception)
{
    throw new nullException('Error in checking user grades: ' . $exception->getMessage(), 0);
}

/**
 * An error handler to use in progress cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function progresslrsreq_warning($errno, $errstr, $errfile, $errline)
{

    throw new nullException('Unable to communicate with LRS. Caught exception: ' . $errstr. " Check LRS is up, username and password are correct, and LRS endpoint is correct.", 0);

}

/**
 * An warning handler to use to post better warnings to users for troubleshooting. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function custom_warningAU($errno, $errstr, $errfile, $errline)
{
    echo "Error loading session table on AUview page. Report this to system administrator: <br> $errstr at $errfile on $errline: Check that session information is present in DB and session id is correct.";
    
    exit;

}


/**
 * An exception handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function exception_progresslrsreq(\Throwable $exception)
{
   
    throw new nullException('Unable to communicate with LRS. Caught exception: ' . $exception->getMessage() . " Check LRS is up, username and password are correct, and LRS endpoint is correct.", 0);
}
/**
 * A warning handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function progresslrs_warning($errno, $errstr, $errfile, $errline)
{
    throw new nullException('Error in retrieving statements from LRS ' . $errstr, 0);
}

/**
 * An exception handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function exception_progresslrs(\Throwable $exception)
{
   
    throw new nullException('Error in retrieving statements from LRS ' . $exception->getMessage(), 0);
}

/**
 * A warning handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function sifting_data_warning($errno, $errstr, $errfile, $errline)
{
    throw new nullException('Error: ' . $errstr, 0);
    //  exit;
}

/**
 * An exception handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
    function array_chunk_warning($errno, $errstr, $errfile, $errline)
    {
        throw new nullException('Cannot parse array. Error: ' . $errstr, 0);
    }

/**
 * An grade handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
    function grade_warning($errno, $errstr, $errfile, $errline)
    {

        throw new nullException('Error in checking user grades: ' . $errstr, 0);
    }


/**
 * Define a custom exception class, this will make pour tests meaningful
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class nullException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "A custom function for this type of exception\n";
    }
}

/**
 * Define a custom exception class, this will make pour tests meaningful
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class playerException extends \Exception
{
    // Redefine the exception so message isn't optional
    // I want an exception that takkkes what is missing and adds it to messsssage? 
    // Is this possivlbe? 
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // Ah maybe here is where I can differentiate them
        $playermessage = "Player communication error. Something went wrong " . $message;
        // make sure everything is assigned properly
        parent::__construct($playermessage, $code, $previous);
    }

   
    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        // maybe here?
    }

    public function customFunction() {
        echo "  This error to string            :";
      //  $this->getTraceAsString();
    }
}

/**
 * Define a custom exception class, this will make pour tests meaningful
 * This is a catchall custom
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class customException extends \Exception
{
    // Redefine the exception so message isn't optional
    // I want an exception that takkkes what is missing and adds it to messsssage? 
    // Is this possivlbe? 
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // Ah maybe here is where I can differentiate them
        $playermessage = "Caught error. Something went wrong " . $message;
        // make sure everything is assigned properly
        parent::__construct($playermessage, $code, $previous);

        echo"$playermessage";
    }

   
    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        // maybe here?
    }

    public function customFunction() {
    //    echo "  This error to string            :";
      //  $this->getTraceAsString();
    }
}
