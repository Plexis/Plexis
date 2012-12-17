<?php
// Bring some classes into scope so we dont have to specify namespaces throughout
use Core\Request;
use Library\Auth;
use Library\InvalidUsernameException;
use Library\InvalidPasswordException;
use Library\InvalidCredentialsException;
use Library\AccountBannedException;

class AccountModel
{
    /**
     *  Method used to proccess the login request.
     *
     * @return array Returns an array with 2 keys (bool success, string message)
     *   If success is false, a message will be provided detailing the error.
     */
    public function doLogin()
    {
        // Default return values
        $result = false;
        $message = '';
        
        // Attempt the login D:
        try {
            $result = Auth::Login( Request::Post('username'), Request::Post('password') );
        }
        catch(InvalidUsernameException $e) 
        {
            switch($e->getCode())
            {
                case 1:
                    $message = "Invalid username. Username must not be at least 3 characters in length";
                    break;
                case 2:
                    $message = "Invalid username. Username must not be over 12 characters in length";
                    break;
            }
        }
        catch(InvalidPasswordException $e) 
        {
            switch($e->getCode())
            {
                case 1:
                    $message = "Invalid password. Password must not be at least 3 characters in length";
                    break;
                case 2:
                    $message = "Invalid password. Password must not be over 12 characters in length";
                    break;
            }
        }
        catch(InvalidCredentialsException $e) {
            $message = "Incorrect username or password.";
        }
        catch(AccountBannedException $e) {
            $message = "Login failed because the account is banned.";
        }
        
        return array('success' => $result, 'message' => $message);
    }
}