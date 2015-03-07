<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'include/utils/utils.php';
/**
 * Vtiger JoomlaBridge Joomla User Model Class
 */
class Joomlabridge_JUser_Model extends Vtiger_Base_Model {

	/**
	 * Unique id
	 *
	 * @var    integer
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $id = 0;
	
	/**
	 * Unique Joomla SQL HostId to identify the Joomla Instance
	 *
	 * @var    integer
	 * @since  vtiger 6.0 - for the multi-site support
	 */
	public $jhostid = 0;
	
	/**
	 * The Joomla user's real name (or nickname)
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $name = '';
	
	/**
	 * The login name /may better to use email for this purpose? /
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $username = '';	
	
	/**
	 * The email
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $email = '';	
	
	/**
	 * MD5 encrypted password
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $password = '';
	
	/**
	 * Clear password, only available when a new password is set for a user
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $password_clear = '';	
	
	/**
	 * Block status
	 *
	 * @var    integer
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $block = 0;
	
	/**
	 * Should this user receive system email
	 *
	 * @var    integer
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $sendemail = 0;

	/**
	 * Date the user was registered
	 *
	 * @var    datetime
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $registerdate = '0000-00-00 00:00:00';

	/**
	 * Date of last visit
	 *
	 * @var    datetime
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $lastvisitdate = '0000-00-00 00:00:00';
	
	/**
	 * Activation hash
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $activation = '';
	
	/**
	 * User parameters
	 *
	 * @var    JRegistry
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $params = '{}';
	
	/**
	 * Associative array of user names => group ids ( user id => group ids may better ?? )
	 *
	 * @var    array
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	public $groups = array();
	
	/**
	 * Last Reset Time
	 *
	 * @var    string
	 * @since  12.2 in Joomla platform - here is using for the convenience of object structure
	 */
	public $lastresettime = '0000-00-00 00:00:00';
	
	/**
	 * Count since last Reset Time
	 *
	 * @var    int
	 * @since  12.2 in Joomla platform - here is using for the convenience of object structure
	 */
	public $resetcount = 0;
	
	/**
	 * Flag to require the user's password be reset
	 *
	 * @var    int
	 * @since  3.2 in Joomla CMS - here is using for the convenience of object structure
	 */
	public $requirereset = 0;
	
	/**
	 * Error message
	 *
	 * @var    string
	 * @since  11.1 in Joomla platform - here is using for the convenience of object structure
	 */
	protected $_errormsg = '';

	/**
	 * @var    array  JUser instances container.
	 * @since  11.3 in Joomla platform - here is using for the convenience of object structure
	 */
	protected static $instances = array();

	/**
	 * @var    array  Module instances container.
	 * @since  vtiger 6.0
	 */
	protected $module = false;

	/**
	 * Constructor
	 * @param Integer $id
	 * @param Mixed $jhostid
	 */	
	function __construct($id, $jhostid){
		if ( empty ($id) ) {
			$this->id = 0;
		} else {
			$this->id = $id;
		}
		
		if ( empty ($jhostid) ) {
			$this->jhostid = 0;
		} else {
			$this->jhostid	= $jhostid;
		}
	}
	
	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   11.1
	 */
	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;
		return $previous;
	}
	
	/**
	 * Set the object properties based on a named array/hash.
	 *
	 * @param   mixed  $properties  Either an associative array or another object.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 *
	 * @see     JObject::set()
	 */
	public function setProperties($properties)
	{
		if (is_array($properties) || is_object($properties))
		{
			foreach ((array) $properties as $k => $v)
			{
				// Use the set function which might be overridden.
				$this->set($k, $v);
			}
			return true;
		}

		return false;
	}
	
	/**
	 * Returns the global Joomla User object, only creating it if it doesn't already exist.
	 * 
	 * @param   integer  $identifier  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return Joomlabridge_JUser_Model - The Joomla User object for vtiger CRM.
	 *
	 * @since   11.1 in Joomla platform - here is using for the convenience of the approach
	 */
	public static function getInstance($identifier = 0, $JHostId) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_JUser_Model::getInstance( $identifier, $JHostId )");
		
		if ( empty( $JHostId ) ) { 
			$JHostId = $this->get('jhostid'); 
		}
		
		if ( empty( $JHostId ) ) { 
			$log->error("Joomlabridge_JUser_Model::getInstance( $identifier, $JHostId ) --- JHOSTID DOES NOT EXIST");
			return false;
		}
		
		// Find the user id
		if (!is_numeric($identifier)) {
		
			if ( !$id = Joomlabridge_JUser_Helper::getUserId($identifier, $JHostId) ) 	{
			
				$log->error("Joomlabridge_JUser_Model::getInstance( $identifier, $JHostId ) --- ID DOES NOT EXIST");
				return false;
			}
		}
		else
		{
			$id = $identifier;
		}

		// If the $id is zero, just return an empty Joomlabridge_JUser_Model.
		// Note: don't cache this user because it'll have a new ID on save!
		if ($id === 0)
		{
			return new Joomlabridge_JUser_Model(0, $JHostId);
		} else {
		
			// Check if the user ID is already cached.
			if (empty(self::$instances[$id])) {
				$user = new Joomlabridge_JUser_Model($id, $JHostId);
				self::$instances[$id] = $user;
			}

			return self::$instances[$id];		
		
		}
	}	

	/**
	 * Function to get the Joomla User Id
	 * @return <Number> - JUserId
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to set the Joomla User Id
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value) {
		return $this->set('id', $value);
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param   array  &$array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1 in Joomla platform - here is using for the convenience of the approach
	 */
	public function bind(&$array) {
		global $log;
		global $current_user;
		$log->debug( 'ENTERING --> Joomlabridge_JUser_Model::bind(&$array) $array: '.print_r($array, true) );
		// Let's check to see if the user is new or not
		if ( empty( $this->id ) ) {
			// Check the password and create the crypted password
			if ( empty($array['password']) ) {
				$array['password'] = Joomlabridge_JUser_Helper::genRandomPassword();
				$array['password2'] = $array['password'];
			}

			// Not all controllers check the password, although they should.
			// Hence this code is required:
			if ( isset($array['password2']) && $array['password'] != $array['password2'] ) {
				//TO-DO: somehow give an error message
				$log->error( '!#!#!#! ERROR Joomlabridge_JUser_Model::bind(&$array) -- USER_ERROR_PASSWORD_NOT_MATCH -- $array: '.print_r($array, true) );
				return false;
			}
			$this->password_clear = Joomlabridge_Arrays_Helper::getValue($array, 'password', '', 'string');

			$array['password'] = Joomlabridge_JUser_Helper::hashPassword($array['password']);

			//get registration real date-time
			$vt_now = new DateTimeField(null);
			$regdate = $vt_now->getDisplayDate($current_user);  
			$regtime = $vt_now->getDisplayTime($current_user);
			$regdatetime = $regdate." ".$regtime;
		
			// Set the registration timestamp
			$this->registerdate = $regdatetime;

			// Check that username is not greater than 150 characters
			$username = $this->username;

			if (strlen($username) > 150) {
				$username = substr($username, 0, 150);
				$this->username = $username;
			}
		} else {
			// Updating an existing user
			if ( !empty($array['password']) ) {
				if ($array['password'] != $array['password2']) {
					//TO-DO: somehow give an error message
					$log->error('!#!#!#! ERROR Joomlabridge_JUser_Model::bind(&$array) -- USER_ERROR_PASSWORD_NOT_MATCH -- $array: '.print_r($array, true) );
					return false;
				}

				$this->password_clear = Joomlabridge_Arrays_Helper::getValue($array, 'password', '', 'string');

				// Check if the user is reusing the current password if required to reset their password
				if ($this->requirereset == 1 && Joomlabridge_JUser_Helper::verifyPassword($this->password_clear, $this->password)) {
					//TO-DO: somehow give an error message
					$log->error('!#!#!#! ERROR Joomlabridge_JUser_Model::bind(&$array) -- USER_ERROR_CANNOT_REUSE_PASSWORD -- $array: '.print_r($array, true) );
					return false;
				}

				$array['password'] = Joomlabridge_JUser_Helper::hashPassword($array['password']);

				// Reset the change password flag
				$array['requirereset'] = 0;
			} else {
				$array['password'] = $this->password;
			}
		}


		// Bind the array
		if (!$this->setProperties($array))
		{
			//TO-DO: somehow give an error message
			$log->error('!#!#!#! ERROR Joomlabridge_JUser_Model::bind(&$array) -- USER_ERROR_BIND_ARRAY -- $array: '.print_r($array, true) );
			return false;
		}

		// Make sure its an integer
		$this->id = (int) $this->id;

		return true;
	}

	/**
	 * Method to save the Joomla User object to the Joomla SQL database
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new user
	 *                                Currently only used in the user reset password method.
	 *
	 * @return  boolean  False on error and JUserId if save was successfully
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	 

	public function save($updateOnly = false) {
		global $log;

		$params = array();
		$result = false;
		
		$JHostId = $this->jhostid;

		if ( empty( $JHostId ) ) {
			$log->error('!#!#!#! ERROR Joomlabridge_JUser_Model::save() -- Joomla Host ID is NOT DEFINED -- $JHostId: '.print_r($JHostId, true) );
			return false;	
		}
		
		// Are we creating a new user
		$isNew = empty($this->id);

		// If we aren't allowed to create new users return
		if ($isNew && $updateOnly) {
			//exit because no need action
			return true;
		}
		
		try {
			//get the Joomla database prefix
			$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
			$jusertablename = implode("", array($prefix, 'users'));
			$jusergrouptablename = implode("", array($prefix, 'user_usergroup_map'));
			//Open the Joomla SQL by JHostId		
			$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);
			
			// Joomla User record:
			// id, name, username, email, password, block, sendEmail, registerDate, lastvisitDate, activation, 
			// params, lastResetTime, resetCount, otpKey, otep, requireReset
		
			if ( $isNew ) {
			
				try {
					$params['name']				= $this->name;				//1
					$params['username']			= $this->username;			//2
					$params['email']			= $this->email;				//3
					$params['password']			= $this->password;			//4
					$params['block']			= $this->block;				//5
					$params['sendemail']		= 0;						//6
					$params['registerdate']		= $this->registerdate;		//7
					$params['lastvisitdate']	= '0000-00-00 00:00:00';	//8
					$params['activation']		= '';						//9
					$params['params']			= '';						//10
					$params['lastresettime']	= '0000-00-00 00:00:00';	//11
					$params['resetcount']		= 0;						//12
					$params['otpkey']			= '';						//13
					$params['otep']				= '';						//14
					$params['requirereset']		= $this->requirereset;		//15
					
					$log->debug("##### Joomlabridge_JUser_Model::save() : ".print_r($params, true) );
					
					// Be sure username is Unique, email is Unique
					$jd = $joomlaDB->pquery( "SELECT COUNT(*) AS 'duplicates' FROM ".$jusertablename.
							" WHERE email = ? OR username = ? OR username = ?", array($params['email'], $params['email'], $params['username'] ) );
					$jduplicates = $joomlaDB->query_result($jd, 0, 'duplicates');							
					$log->debug("##### Joomlabridge_JUser_Model::save() - Check duplicates : ".print_r($jduplicates, true) );
					
					if ( $jduplicates == 0 ) {
							
						$columnNames = array_keys($params);
						$columnValues = array_values($params);						
						// Create a New JUser in Joomla SQL
						$joomlaDB->pquery('INSERT INTO '.$jusertablename.' ('. implode(',',$columnNames).') VALUES ('. generateQuestionMarks($columnValues).')', array($columnValues));
						// get the last insterd ID
						$JUserID = $joomlaDB->GetOne('SELECT LAST_INSERT_ID()');
						$log->debug("##### Joomlabridge_JUser_Model::save() - Get LAST INSERT ID : ".print_r($JUserID, true) );
						
						// Define the usergroups for this user
						$UserGroups = $this->groups;
						if ( empty ($UserGroups) ) {
							//hardcoded Registered = 2 for the new User, if the Usergroup is not set
							$UserGroups = array(2);
						}
						//INSERT INTO #__user_usergroup_map (user_id , group_id) VALUES (?, ?)
						foreach ( $UserGroups as $key => $group_id ) {
							$joomlaDB->pquery('INSERT INTO '.$jusergrouptablename.' (user_id, group_id) VALUES (?, ?)', array($JUserID, $group_id));
						}
						
						$result = $JUserID;
					} else {
						$this->_errormsg = "Duplicated JUser";
						$result = false;
					}
					
				} catch (Exception $e) {
					$this->_errormsg = print_r($e->getMessage(), true);
					$log->fatal('@@@ Joomlabridge_JUser_Model::save() -- failed at INSERT new JUser: '.print_r($e->getMessage(), true) );
					return false;
				} 
				
			
			} else {
			
	
			
				// Update existing JUser ( User level, Block, Password - subjects of update)
				try {
					$JUserID	= $this->id;
					$Block		= $this->block;
					$PW			= $this->password;
					
					$joomlaDB->pquery("UPDATE ".$jusertablename." SET block = ?, password = ? WHERE id = ?", array( $Block, $PW, $JUserID ) );
									
					// Define the usergroups for this user
					$UserGroups = $this->groups;					
					
					if ( empty($UserGroups) ) {
						//Nothing to update
						$this->_errormsg = "No User groups defined!";
						$result = false;
					} else {					
					
						//Delete the existing User Group memberships
						$joomlaDB->pquery("DELETE FROM ".$jusergrouptablename." WHERE user_id = ?", array( $JUserID ) );				

						//INSERT INTO #__user_usergroup_map (user_id , group_id) VALUES (?, ?)
						foreach ( $UserGroups as $key => $group_id ) {
							$joomlaDB->pquery('INSERT INTO '.$jusergrouptablename.' (user_id, group_id) VALUES (?, ?)', array($JUserID, $group_id) );
						}				
						
						$joomlaDB->disconnect();
						$log->fatal("@@@ Joomla Instance Updated (block, password, user_group_map): InstanceID = ".$JHostId.", JUserID = ".$JUserID );						
						$result = true;
					}
	
				} catch (Exception $e) {
					$this->_errormsg = print_r($e->getMessage(), true);
					$log->fatal('@@@ Joomlabridge_JUser_Model::save() -- failed at UPDATE existing JUser: '.print_r($e->getMessage(), true) );
					return false;
				}
	
				
			}

		} catch (Exception $e) {
			$log->fatal('@@@ Joomlabridge_JUser_Model::save() -- failed: '.print_r($e->getMessage(), true) );
			return false;
		}

		return $result;
	}
	
}
