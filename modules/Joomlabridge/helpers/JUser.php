<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Joomlabridge_JUser_Helper {
	/**
	 * Returns userid if a Joomla User exists in the Joomla SQL
	 *
	 * @param   string  $username  The username to search on.
	 *
	 * @return  integer  The user id or 0 if not found.
	 *
	 * @since   6.0 (vtiger Joomlabridge)
	 */
	public static function getUserId($username, $JHostId) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_JUser_Helper::getUserId( $JHostId, $username )");
		
		//get the Joomla database prefix
		$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
		$jtname = implode("", array($prefix, 'users'));
		//Open the Joomla SQL by JHostId
		$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);		
		$qusername = $joomlaDB->quote($username);
		$records = array();
		
		$jresult = $joomlaDB->pquery('SELECT id FROM '.$jtname.' WHERE username = ?;', array($qusername));

		if ($joomlaDB->num_rows($jresult)) {
			while ($data = $joomlaDB->fetch_array($jresult)) {
				$records[] = new self($data);
			}
			foreach ( $records as $record) {
				$JUserId = $record->get('id');
			}
			$joomlaDB->disconnect();
			return $JUserId;
			
		} else {
			$joomlaDB->disconnect();		
			return false;
		}
	}
	
	/**
	 * Returns a Joomla user object if a Joomla User exists in the Joomla SQL
	 *
	 * @param   integer  $JUserId  The UserId to search on.
	 * @param   integer  $JHostId  The Joomla instance Host ID
	 * @return  integer  The user id or 0 if not found.
	 *
	 * @since   6.0 (vtiger Joomlabridge)
	 */
	public static function getJUser($JUserId, $JHostId) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_JUser_Helper::getUser( $JUserId, $JHostId )");
		
		if ( !$JUserId ) { return false; }
		
		$JUser = Joomlabridge_JUser_Model::getInstance( $JUserId, $JHostId);
		
		//get the Joomla database prefix
		$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
		$jtname = implode("", array($prefix, 'users'));
		$jusergrouptablename = implode("", array($prefix, 'user_usergroup_map'));
		//Open the Joomla SQL by JHostId
		$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);		
		$JURecord = array();
		
		$jresult = $joomlaDB->pquery('SELECT * FROM '.$jtname.' WHERE id = ?', array($JUserId));

		if ($joomlaDB->num_rows($jresult)) {
			$JURecord = $joomlaDB->fetch_row($jresult, 0);

			foreach ( $JURecord as $property => $value )  {
				if (!is_numeric($property)) {
					$JUser->$property = $value;
				}
			}

			$JUsergroups = array();
			$jgroups = $joomlaDB->pquery('SELECT * FROM '.$jusergrouptablename.' WHERE user_id = ?', array($JUserId) );

			if ($joomlaDB->num_rows($jgroups)) {
				while ($group_row = $joomlaDB->fetch_array($jgroups)) {
					$JUsergroups[] = $group_row['group_id'];
				}
			}
			$JUser->groups = $JUsergroups;	
			
			$joomlaDB->disconnect();
			return $JUser;
			
		} else {
			$joomlaDB->disconnect();		
			return false;
		}
	}

	/**
	 * Generate a random password
	 *
	 * @param   integer  $length  Length of the password to generate
	 *
	 * @return  string  Random Password
	 *
	 * @since   11.1 (too same as in the Joomla 3.3 )
	 */
	public static function genRandomPassword($length = 8)
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$base = strlen($salt);
		$makepass = '';

		/*
		 * Start with a cryptographic strength random string, then convert it to
		 * a string with the numeric base of the salt.
		 * Shift the base conversion on each character so the character
		 * distribution is even, and randomize the start shift so it's not
		 * predictable.
		 */
		$random = Joomlabridge_JCrypt_Helper::genRandomBytes($length + 1);
		$shift = ord($random[0]);

		for ($i = 1; $i <= $length; ++$i)
		{
			$makepass .= $salt[($shift + ord($random[$i])) % $base];
			$shift += ord($random[$i]);
		}

		return $makepass;
	}
	
	/**
	 * Hashes a password using the current encryption.
	 *
	 * @param   string  $password  The plaintext password to encrypt.
	 *
	 * @return  string  The encrypted password.
	 *
	 * @since   3.2.1
	 */
	public static function hashPassword($password)
	{
		// JCrypt::hasStrongPasswordSupport() includes a fallback for us in the worst case
		Joomlabridge_JCrypt_Helper::hasStrongPasswordSupport();

		return password_hash($password, PASSWORD_DEFAULT);
	}
	
	/**
	 * Formats a password using the current encryption. If the user ID is given
	 * and the hash does not fit the current hashing algorithm, it automatically
	 * updates the hash.
	 *
	 * @param   string   $password  The plaintext password to check.
	 * @param   string   $hash      The hash to verify against.
	 * @param   integer  $user_id   ID of the user if the password hash should be updated
	 *
	 * @return  boolean  True if the password and hash match, false otherwise
	 *
	 * @since   3.2.1
	 */
	public static function verifyPassword($password, $hash, $user_id = 0) {
		global $log;	
		spl_autoload_register(function ($PasswordHash) {
			include_once 'modules/Joomlabridge/helpers/PasswordHash.php';
		});
		
		$rehash = false;
		$match = false;

		// If we are using phpass
		if (strpos($hash, '$P$') === 0)
		{
			try {
				// Use PHPass's portable hashes with a cost of 10.
				$phpass = new PasswordHash(10, true);
				$match = $phpass->CheckPassword($password, $hash);
				$rehash = true;
				
			} catch (Exception $e) {
				$log->fatal('@@@ Creating new PasswordHash object -- failed: '.print_r($e->getMessage(), true) );
			}

		}
		elseif ($hash[0] == '$')
		{
			// Joomlabridge_JCrypt_Helper::hasStrongPasswordSupport() includes a fallback for us in the worst case
			Joomlabridge_JCrypt_Helper::hasStrongPasswordSupport();
			$match = password_verify($password, $hash);

			// Uncomment this line if we actually move to bcrypt.
			$rehash = password_needs_rehash($hash, PASSWORD_DEFAULT);
		}
		elseif (substr($hash, 0, 8) == '{SHA256}')
		{
			// Check the password
			$parts     = explode(':', $hash);
			$crypt     = $parts[0];
			$salt      = @$parts[1];
			$testcrypt = static::getCryptedPassword($password, $salt, 'sha256', true);

			$match = Joomlabridge_JCrypt_Helper::timingSafeCompare($hash, $testcrypt);

			$rehash = true;
		}
		else
		{
			// Check the password
			$parts = explode(':', $hash);
			$crypt = $parts[0];
			$salt  = @$parts[1];

			$rehash = true;

			$testcrypt = md5($password . $salt) . ($salt ? ':' . $salt : '');

			$match = Joomlabridge_JCrypt_Helper::timingSafeCompare($hash, $testcrypt);
		}

		// If we have a match and rehash = true, rehash the password with the current algorithm.
		if ((int) $user_id > 0 && $match && $rehash)
		{
			$user = new Joomlabridge_JUser_Model($user_id);
			$user->password = static::hashPassword($password);
			$user->save();
		}

		return $match;
	}
	
	/**
	 * Formats a password using the current encryption.
	 *
	 * @param   string   $plaintext     The plaintext password to encrypt.
	 * @param   string   $salt          The salt to use to encrypt the password. []
	 *                                  If not present, a new salt will be
	 *                                  generated.
	 * @param   string   $encryption    The kind of password encryption to use.
	 *                                  Defaults to md5-hex.
	 * @param   boolean  $show_encrypt  Some password systems prepend the kind of
	 *                                  encryption to the crypted password ({SHA},
	 *                                  etc). Defaults to false.
	 *
	 * @return  string  The encrypted password.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public static function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		// Get the salt to use.
		$salt = static::getSalt($encryption, $salt, $plaintext);

		// Encrypt the password.
		switch ($encryption)
		{
			case 'plain':
				return $plaintext;

			case 'sha':
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));

				return ($show_encrypt) ? '{SHA}' . $encrypted : $encrypted;

			case 'crypt':
			case 'crypt-des':
			case 'crypt-md5':
			case 'crypt-blowfish':
				return ($show_encrypt ? '{crypt}' : '') . crypt($plaintext, $salt);

			case 'md5-base64':
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));

				return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;

			case 'ssha':
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext . $salt) . $salt);

				return ($show_encrypt) ? '{SSHA}' . $encrypted : $encrypted;

			case 'smd5':
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext . $salt) . $salt);

				return ($show_encrypt) ? '{SMD5}' . $encrypted : $encrypted;

			case 'aprmd5':
				$length = strlen($plaintext);
				$context = $plaintext . '$apr1$' . $salt;
				$binary = static::_bin(md5($plaintext . $salt . $plaintext));

				for ($i = $length; $i > 0; $i -= 16)
				{
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}
				for ($i = $length; $i > 0; $i >>= 1)
				{
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = static::_bin(md5($context));

				for ($i = 0; $i < 1000; $i++)
				{
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);

					if ($i % 3)
					{
						$new .= $salt;
					}
					if ($i % 7)
					{
						$new .= $plaintext;
					}
					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = static::_bin(md5($new));
				}

				$p = array();

				for ($i = 0; $i < 5; $i++)
				{
					$k = $i + 6;
					$j = $i + 12;

					if ($j == 16)
					{
						$j = 5;
					}
					$p[] = static::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$' . $salt . '$' . implode('', $p) . static::_toAPRMD5(ord($binary[11]), 3);

			case 'sha256':
				$encrypted = ($salt) ? hash('sha256', $plaintext . $salt) . ':' . $salt : hash('sha256', $plaintext);

				return ($show_encrypt) ? '{SHA256}' . $encrypted : '{SHA256}' . $encrypted;

			case 'md5-hex':
			default:
				$encrypted = ($salt) ? md5($plaintext . $salt) : md5($plaintext);

				return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
		}
	}

}
