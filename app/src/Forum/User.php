<?php
namespace Joah\Forum;
 
/**
 * Model for Users of the forum.
 *
 */
class User extends \Anax\Users\User
{
    const SESSION_VARIABLE = 'user';
    
    /**
     *  Fetch a user Gravatar
     *  
     *  @return string
     */
    public function fetchGravatar($id, $size = 40) 
    {
        $user = $this->find($id);    
        return '<img src="http://www.gravatar.com/avatar/' . md5( strtolower( trim( $user->email ) ) ) . '?d=mm&amp;s=' . $size . '" alt="user picture from gravatar">';
    }
 
    /**
     *  Fetch username
     *  
     *  @return string
     */
    public function fetchName($id)
    {
        $user = $this->find($id);
        return $user->name;
    }

    
    /**
     *  Login
     */
    public function login($acronym, $password)
    {
        //verify password
        $res = $this->verifyPassword($acronym, $password);

        // put user data in session
        if($res) {
        
            
            $this->session->set(self::SESSION_VARIABLE, [
                                'acronym' => $this->acronym, 
                                'name' => $this->name,
                                'id' => $this->id
                                ]);
        return true;
        }
        
        else {
            return false;
        }
        
    }
    
    
    /**
     *  logout
     */
    
    public function logout() 
    {
        $_SESSION = [];
        return true;
    }        

    
    /**
     *  Fetch user by acronym
     */
    public function fetchByAcronym($acronym)
    {
        $this->db->select()
                 ->from($this->getSource())
                 ->where("acronym = ?");
        
        $this->db->execute([$acronym]);
        return $this->db->fetchInto($this);
    }
    
    
    /**
    *  Verify password.
    */
    public function verifyPassword($acronym, $password)
    {
        //fetch user
        $user = $this->fetchByAcronym($acronym);

        // no such user.
        if (empty($user)) {
            return false;
        }
        
        // verify password
        if(password_verify($password, $this->password)){
            return true;
        }
        else {
            return false;
        }        
    }    
    
    
    /**
     *  Verify that user is logged in.
     *
     * @int or false
     */
    public function isLoggedIn()
    {
        // check session for user id
        if(isset($_SESSION['user']['id'])) {
            // return true;
            return $_SESSION['user']['id'];
        }
        else {
            return false;
        }
    }
    
    
    /**
     *  Verify that user is logged in as an specific id
     */
    public function verifyLogin($user_id = null)
    {
        
        // compare user-id with user-id in session
        if (isset($_SESSION['user']['id']) AND ($_SESSION['user']['id'] === $user_id)) {
            return true;
        }
        // if admin account
        else if (isset($_SESSION['user']['id']) AND ($_SESSION['user']['id'] === '1')) {
            return true;
        }
        else {
            return false;
        }
    }    
}   