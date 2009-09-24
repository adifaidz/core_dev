<?php
/**
 * $Id$
 *
 * Object is a list of comments, usually attached to another object
 *
 * @author Martin Lindhe, 2009 <martin@startwars.org>
 */

require_once('class.Comment.php');
require_once('xhtml_form.php');
require_once('client_captcha.php');

//FIXME: the pager is half broken, limit is ignored in render()

class Comments
{
	private $tbl_layout;
	private $tbl_name = 'tblComments';
	private $sql_limit = ''; ///< used by the pager

	private $ownerId;
	private $type;                ///< Comment::type
	private $showDeleted = false; ///< shall deleted comments be included?
	private $showPrivate = false; ///< shall private comments be included?
	private $allowAnon   = false; ///< do we allow anonymous comments?
	private $use_captcha = true;  ///< shall we use captchas for anonymous comments?
	private $limit       = 0;     ///< number of items per page

	private $comment;             ///< Comment object
	private $captcha;             ///< Captcha object

	function __construct($type)
	{
		if (!is_numeric($type)) return false;
		$this->type = $type;

		$this->comment = new Comment();
		$this->captcha = new Captcha();

		$this->captcha->setPrivKey('6LfqDQQAAAAAAKOMPfoJYcpqfZBlWQZf1BYiq7qt');
		$this->captcha->setPubKey( '6LfqDQQAAAAAAMF-GaCBYHRJFetLd_BrjO8-2HBW');
	}

	function setOwner($id)
	{
		if (!is_numeric($id)) return false;
		$this->ownerId = $id;
	}

	function setLimit($l)
	{
		if (!is_numeric($l)) return false;
		$this->limit = $l;
	}

	function setAnonAccess($bool) { $this->allowAnon = $bool; }
	function disableCaptcha() { $this->use_captcha = false; }

	function showDeleted() { $this->showDeleted = true; }
	function showPrivate() { $this->showPrivate = true; }

	function getList()
	{
		global $db;

		$q  = 'SELECT * FROM '.$this->tbl_name.' WHERE ';

		//XXX this sql will break some times
		if ($this->ownerId) $q .= 'ownerId='.$this->ownerId.' AND';
		if ($this->type) $q .= ' commentType='.$this->type.' AND';
		if (!$this->showPrivate) $q .= ' commentPrivate=0 AND';
		if (!$this->showDeleted) $q .= ' deletedBy=0';

		$q .= ' ORDER BY timeCreated DESC'.$this->sql_limit;
		return $db->getArray($q);
	}

	function getCount()
	{
		global $db;

		$q  = 'SELECT COUNT(*) FROM '.$this->tbl_name.' WHERE ';

		//XXX this sql will break some times
		if ($this->ownerId) $q .= 'ownerId='.$this->ownerId.' AND';
		if ($this->type) $q .= ' commentType='.$this->type.' AND';
		if (!$this->showPrivate) $q .= ' commentPrivate=0 AND';
		if (!$this->showDeleted) $q .= ' deletedBy=0';
		return $db->getOneItem($q);
	}

	/**
	 * Handles new form post
	 */
	function handleSubmit($p, $caller)
	{
		global $h;
		if (empty($p['cmt_'.$this->type])) {
			$caller->setError('No text entered.');
			return false;
		}

		if ($h->session->id || //logged in
			(!$h->session->id && $this->allowAnon && !$this->use_captcha) || //anon + captcha disabled
			(!$h->session->id && $this->allowAnon && $this->captcha->verify()) //anon + captcha accepted
			) {
			$id = $this->comment->add($p['cmt_'.$this->type]);
			if (!$id) $caller->setError( $this->comment->getError() );
			unset($p['cmt_'.$this->type]);
			return $id;
		}

		if (!$h->session->id && $this->allowAnon && !$this->captcha->verify()) {
			$caller->setError('Incorrect captcha');
			return false;
		}

		$caller->setError('Unauthorized submit');
		return false;
	}

	function render()
	{
		global $h;

		$col_w = 30;
		$col_h = 6;

		$this->comment->setType($this->type);
		$this->comment->setOwner($this->ownerId);

		$form = new xhtml_form('addcomment');
		$form->addTextarea('cmt_'.$this->type, 'Write a comment', '', $col_w, $col_h);
		if ($this->use_captcha && !$h->session->id) $form->addCaptcha($this->captcha);
		$form->addSubmit('Add comment');
		$form->setHandler('handleSubmit', $this);

		$cnt = $this->getCount();

		$res = '<div class="comment_header" onclick="toggle_element(\'comments_holder\')">'.$cnt.' '.($cnt == 1 ? t('comment'):t('comments')).'</div>';

		$res .= '<div id="comments_holder">';
		$res .= '<div id="comments_only">';
		$pager = makePager($cnt, $this->limit);

		$this->sql_limit = $pager['limit'];

		$res .= $pager['head'];
		foreach ($this->getList() as $row) {
			$res .= $this->comment->render($row);
		}
		if ($cnt >= 5) $res .= $pager['head'];
		$res .= '</div>'; //id="comments_only"

		if ($h->session->id || $this->allowAnon) {
			//add comment form
			$res .= $form->render();
		}

		$res .= '</div>';	//id="comments_holder"

		return $res;

	}

}


/*
		//XXX make use of this info
		$this->tbl_layout = array(
		'commentId'      => array('bigint:20:unsigned','null:NO','key:PRI','extra:auto_increment'),
		'commentType'    => array('tinyint:3:unsigned','null:NO'),
		'commentText'    => array('text'),
		'commentPrivate' => array('tinyint:3:unsigned','null:NO'),
		'timeCreated'    => array('datetime','null:YES'),
		'timeDeleted'    => array('datetime','null:YES'),
		'deletedBy'      => array('bigint:20:unsigned','null:NO'),
		'ownerId'        => array('bigint:20:unsigned','null:NO'),
		'userId'         => array('bigint:20:unsigned','null:NO'),
		'userIP'         => array('bigint:20:unsigned','null:NO')
		);
*/
?>