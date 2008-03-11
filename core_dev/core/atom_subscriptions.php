<?
/**
 * $Id$
 *
 * \todo Implement forum "bookmarks", personal favorite list using this module
 *
 * tblSubscriptions
 *  id				= subscription id
 *  type			= subscription type, SUBSCRIPTION_FORUM, SUBSCRIPTION_BLOG
 *  ownerId		= owner of the subscription (userId)
 *  itemId		= id of the item we are subscribing to (tblForums.forumId perhaps)
 *
 * \author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

	define('SUBSCRIPTION_FORUM',			1);
	define('SUBSCRIPTION_BLOG',				2);	//fixme: implement
	define('SUBSCRIPTION_FILES',			3);	//fixme: implement

	/**
	 * Creates a subscription of $type on itemId
	 *
	 * \param $type type of subscription
	 * \param $itemId id of item to subscribe to
	 * \return id of created subscription
	 */
	function addSubscription($type, $itemId)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($type) || !is_numeric($itemId)) return false;
		
		if (isSubscribed($type, $itemId)) return false;
		$q = 'INSERT INTO tblSubscriptions SET ownerId='.$session->id.', itemId='.$itemId.', type='.$type.', timeCreated=NOW()';
		return $db->insert($q);
	}

	/**
	 * Deletes a subscription
	 *
	 * \param $type type of subscription
	 * \param $subscriptionId id to delete
	 * \return >0 on success
	 */
	function removeSubscription($type, $subscriptionId)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($type) || !is_numeric($subscriptionId)) return false;

		$q = 'DELETE FROM tblSubscriptions WHERE itemId='.$subscriptionId.' AND type='.$type.' AND ownerId='.$session->id;
		return $db->delete($q);
	}

	/**
	 * Checks if the user is subscribed to this item
	 *
	 * \param $type type of subscription
	 * \param $itemId id of item to check
	 * \return true if user is subscribed
	 */
	function isSubscribed($type, $itemId)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($type) || !is_numeric($itemId)) return false;

		$q = 'SELECT id FROM tblSubscriptions WHERE ownerId='.$session->id.' AND type='.$type.' AND itemId='.$itemId;
		if ($db->getOneItem($q)) return true;
		return false;
	}

	/**
	 * Helper function: calls class.phpmailer.php functions. $mails is a array of recipients
	 * 
	 * \todo Move this function to another file
	 *
	 * \param $mails array of destination e-mail addresses
	 * \param $subject subject of e-mail
	 * \param $body body of e-mail
	 */
	function smtp_mass_mail($mails, $subject, $body)
	{
		global $config;
		
		$mail = new PHPMailer();
		
		$mail->IsSMTP();                                // send via SMTP
		$mail->Host     = $config['smtp']['host']; 			// SMTP servers
		$mail->SMTPAuth = true;    											// turn on SMTP authentication
		$mail->Username = $config['smtp']['username'];	// SMTP username
		$mail->Password = $config['smtp']['password'];	// SMTP password
		$mail->CharSet  = 'utf-8';

		$mail->From     = $config['smtp']['sender'];
		$mail->FromName = $config['smtp']['sender_name'];

		foreach ($mails as $adr) {
			$mail->AddAddress($adr);
		}

		$mail->IsHTML(true);                   					// send as HTML

		//Embed graphics
		$mail->AddEmbeddedImage($config['smtp']['mail_footer'], 'pic_name', '', 'base64', 'image/png');

		$mail->Subject  = $subject;
		$mail->Body     = $body;

		if (!$mail->Send()) {
			echo 'Failed to send mail, error:'.$mail->ErrorInfo;
			return false;
		}

		return true;
	}

	/**
	 * Returns array of subscriptions
	 *
	 * \param $type type of subscription
	 * \return array of subscriptions
	 */
	function getSubscriptions($type)
	{
		global $db, $session;
		if (!$session->id || !is_numeric($type)) return false;

		switch ($type) {
			case SUBSCRIPTION_FORUM:
				$q = 'SELECT t1.*,t2.itemSubject FROM tblSubscriptions AS t1 ';
				$q .= 'LEFT JOIN tblForums AS t2 ON (t1.itemId=t2.itemId) ';
				$q .= 'WHERE t1.type='.$type.' AND t1.ownerId='.$session->id;
				break;

			default:
				$q = 'SELECT * FROM tblSubscriptions WHERE type='.$type.' AND ownerId='.$session->id;
				break;
		}
		return $db->getArray($q);		
	}

	/**
	 * Returns all subscribers for $itemId, only of type $type if specified
	 *
	 * \param $type type of subscription
	 * \param $ownerId owner of subscribable information
	 * \return array of subscriptions
	 */
	function getSubscribers($type, $itemId)
	{
		global $db, $session;
		if (!is_numeric($type) || !is_numeric($itemId)) return false;

		$q = 'SELECT * FROM tblSubscriptions WHERE itemId='.$itemId.' AND type='.$type;
		return $db->getArray($q);
	}


	/*
	//Raderar alla subscriptions av $type och $ownerId
	function removeAllSubscriptions($type, $ownerId)
	{
		if (!is_numeric($type) || !is_numeric($ownerId)) return false;
		
		$sql = 'DELETE FROM tblSubscriptions WHERE subscriptionType='.$type.' AND ownerId='.$ownerId;
		dbQuery($db, $sql);
	}

	
	//Returns an array with all stored settings belonging to this subscription from tblSettings
	function getSubscriptionSettings($type, $ownerId)
	{
		if (!is_numeric($type) || !is_numeric($ownerId)) return false;

		$sql  = 'SELECT t2.settingId,t2.settingName,t2.settingValue FROM tblSubscriptions AS t1 ';
		$sql .= 'INNER JOIN tblSettings AS t2 ON (t1.subscriptionId=t2.ownerId) ';
		$sql .= 'WHERE t1.subscriptionId='.$ownerId.' AND t1.subscriptionType='.$type;

		return dbArray($db, $sql);
	}

	//Returnerar ett row f�r angiven subscription
	function getSubscription($type, $ownerId, $itemId)
	{
		global $db, $session;
		if (!is_numeric($type) || !is_numeric($ownerId)) return false;
		
		$q = 'SELECT * FROM tblSubscriptions WHERE type='.$type.' AND ownerId='.$ownerId.' AND itemId='.$itemId;
		
		return $db->getOneRow($q);
	}

	//Helper function, returns a comma separated text string with mail addresses
	function getEmailSubscribers($subscriptionId)
	{
		if (!is_numeric($subscriptionId)) return false;
		
		$list = getSubscribers($db, SUBSCRIBE_MAIL, $subscriptionId);
		$mails = array();
		for ($i=0; $i<count($list); $i++) {
			$mails[] = $list[$i]['recipient'];
		}
		
		return $mails;
	}

	function addSubscriptionHistory($subscriptionId, $time_from, $time_to, $mail_to, $text)
	{
		if (!is_numeric($subscriptionId) || !is_numeric($time_from) || !is_numeric($time_to)) return false;

		$mail_to = dbAddSlashes($db, $mail_to);
		$text = dbAddSlashes($db, $text);

		$date_from = date('Y-m-d H:i', $time_from); // YYYY-MM-DD HH:SS format (MySQL DATETIME)
		$date_to   = date('Y-m-d H:i', $time_to);

		$sql = 'INSERT INTO tblSubscriptionsHistory SET subscriptionId='.$subscriptionId.',timeCreated=NOW(),periodStart="'.$date_from.'",periodEnd="'.$date_to.'",recipients="'.$mail_to.'",message="'.$text.'"';
		dbQuery($db, $sql);
	}
	
	//Returns true if this period has already been covered
	function checkSubscriptionHistoryPeriod($subscriptionId, $time_from, $time_to)
	{
		if (!is_numeric($subscriptionId) || !is_numeric($time_from) || !is_numeric($time_to)) return false;
		
		$date_from = date('Y-m-d H:i', $time_from); // YYYY-MM-DD HH:SS format (MySQL DATETIME)
		$date_to   = date('Y-m-d H:i', $time_to);

		$sql = 'SELECT historyId FROM tblSubscriptionsHistory WHERE subscriptionId='.$subscriptionId.' AND periodStart="'.$date_from.'" AND periodEnd="'.$date_to.'"';
		$check = dbQuery($db, $sql);
		
		if (dbNumRows($check)) return true;

		return false;
	}
	
	//Returns array of all history entries for specified subscription
	function getSubscriptionHistory($subscriptionType, $subscriptionId)
	{
		if (!is_numeric($subscriptionType) || !is_numeric($subscriptionId)) return false;
		
		$sql  = 'SELECT t1.* FROM tblSubscriptionsHistory AS t1 ';
		$sql .= 'INNER JOIN tblSubscriptions AS t2 ON (t1.subscriptionId=t2.subscriptionId) ';
		$sql .= 'WHERE t2.subscriptionType='.$subscriptionType.' AND t1.subscriptionId='.$subscriptionId.' ORDER BY t1.timeCreated ASC';
		
		return dbArray($db, $sql);		
	}
	*/
?>
