<?php
/* Copyright (C) 2009-2018	Regis Houssin	<regis.houssin@inodbox.com>
 * Copyright (C) 2011		Herve Prot		<herve.prot@symeos.com>
 * Copyright (C) 2014		Philippe Grand	<philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       htdocs/multicompany/actions_multicompany.class.php
 *	\ingroup    multicompany
 *	\brief      File Class multicompany
 */

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/multicompany/class/dao_multicompany.class.php', 'DaoMulticompany');
dol_include_once('/multicompany/lib/multicompany.lib.php');

/**
 *	Class Actions of the module multicompany
 */
class ActionsMulticompany
{
	/** @var DoliDB */
	var $db;
	/** @var DaoMulticompany */
	var $dao;

	var $mesg;
	var $error;
	var $errors=array();
	//! Numero de l'erreur
	var $errno = 0;

	var $template_dir;
	var $template;

	var $label;
	var $description;

	var $referent;

	var $sharings=array();
	var $options=array();
	var $entities=array();
	var $dict=array();
	var $tpl=array();

	var $addzero=array();
	var $sharingelements=array();
	var $sharingobjects=array();
	var $sharingdicts=array();

	private $config=array();

	// For Hookmanager return
	var $resprints;
	var $results=array();


	/**
	 *	Constructor
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->addzero = array(
			'user',
			'usergroup',
			'c_email_templates',
			'email_template',
			'default_values'
		);

		$this->sharingelements = array(
			'thirdparty' => array(
				'icon' => 'building'
			),
			'product' => array(
				'icon' => 'cube'
			),
			'productprice' => array(
				'icon' => 'money'
			),
			'productsupplierprice' => array(
				'icon' => 'money'
			),
			'stock' => array(
				'icon' => 'cubes'
			),
			'invoicenumber' => array(
				'icon' => 'cogs'
			),
			'category' => array(
				'icon' => 'paperclip'
			),
			'agenda' => array(
				'icon' => 'calendar'
			),
			'bankaccount' => array(
				'icon' => 'bank'
			),
			'expensereport' => array(
				'icon' => 'edit'
			),
			'holiday' => array(
				'icon' => 'paper-plane-o'
			),
			'project' => array(
				'icon' => 'code-fork'
			),
			'member' => array(
				'icon' => 'address-card'
			),
			'member_type' => array(
				'icon' => 'address-card'
			),
		);

		$this->sharingobjects = array(
			'proposal' => array(
				'element' => 'propal',
				'icon' => 'file-pdf-o',
				'active' => true
			),
			'order' => array(
				'element' => 'commande',
				'icon' => 'file-pdf-o'
			),
			'invoice' => array(
				'element' => 'facture',
				'icon' => 'file-pdf-o'
			),
			'supplier_proposal' => array(
				'icon' => 'file-pdf-o'
			),
			'supplier_order' => array(
				'icon' => 'file-pdf-o'
			),
			'supplier_invoice' => array(
				'icon' => 'file-pdf-o'
			),
			'intervention' => array(
				'element' => 'ficheinter',
				'icon' => 'wrench'
			),
		);

		$this->sharingdicts = array(
			'c_paiement' => array(
				'societe' => array(
					'mode_reglement',
					'mode_reglement_supplier'
				),
				'propal'				=> 'fk_mode_reglement',
				'commande'				=> 'fk_mode_reglement',
				'facture'				=> 'fk_mode_reglement',
				'facture_rec'			=> 'fk_mode_reglement',
				'commande_fournisseur'	=> 'fk_mode_reglement',
				'facture_fourn'			=> 'fk_mode_reglement',
				'supplier_proposal'		=> 'fk_mode_reglement',
				'chargesociales'		=> 'fk_mode_reglement',
				'don'					=> 'fk_payment',
				'paiement'				=> 'fk_paiement',
				'paiementfourn'			=> 'fk_paiement',
				'paiement_facture'		=> 'fk_paiement',
				'expensereport'			=> 'fk_c_paiement',
				'paiementcharge'		=> 'fk_typepaiement',
				'tva'					=> 'fk_typepayment',
				'payment_various'		=> 'fk_typepayment',
				'payment_salary'		=> 'fk_typepayment',
				'payment_expensereport'	=> 'fk_typepayment',
				'payment_donation'		=> 'fk_typepayment',
				'loan_schedule'			=> 'fk_typepayment',
				'payment_loan'			=> 'fk_typepayment'
			),
			'c_payment_term' => array(
				'societe' => array(
					'cond_reglement',
					'cond_reglement_supplier'
				),
				'propal'				=> 'fk_cond_reglement',
				'commande'				=> 'fk_cond_reglement',
				'facture'				=> 'fk_cond_reglement',
				'facture_rec'			=> 'fk_cond_reglement',
				'commande_fournisseur'	=> 'fk_cond_reglement',
				'facture_fourn'			=> 'fk_cond_reglement',
				'supplier_proposal'		=> 'fk_cond_reglement'
			)
		);
	}

	/**
	 * Instantiation of DAO class
	 *
	 * @return	void
	 */
	// private function getInstanceDao()
	// {
	// 	if (! is_object($this->dao))
	// 	{
	// 		$this->dao = new DaoMulticompany($this->db);
	// 	}
	// }


	/**
	 * setHtmlTitle
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 */
	public function setHtmlTitle($parameters=false)
	{
		$this->resprints = ' + multicompany';
		return 0;
	}


	/**
	 * 	Enter description here ...
	 *
	 * 	@param	string	$action		Action type
	 */
	public function doAdminActions(&$action='')
	{
		global $conf, $user, $langs;

		$this->getInstanceDao();

		$id=GETPOST('id','int');
		$label=GETPOST('label','alpha');
		$name=GETPOST('name','alpha');
		$description=GETPOST('description','alpha');
		$value=GETPOST('value','int');
		$cancel=GETPOST('cancel', 'alpha');
		$visible=GETPOST('visible', 'int');
		$active=GETPOST('active', 'int');

		if ($action == 'add' && empty($cancel) && $user->admin && ! $user->entity)
		{
			$error=0;

			if (empty($label))
			{
				$error++;
				setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")), 'errors');
				$action = 'create';
			}
			else if (empty($name))
			{
				$error++;
				setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("CompanyName")), 'errors');
				$action = 'create';
			}

			// Verify if label already exist in database
			if (! $error)
			{
				$this->dao->getEntities();
				if (! empty($this->dao->entities))
				{
					foreach($this->dao->entities as $entity)
					{
						if (strtolower($entity->label) == strtolower($label)) $error++;
					}
					if ($error)
					{
						setEventMessage($langs->trans("ErrorEntityLabelAlreadyExist"), 'errors');
						$action = 'create';
					}
				}
			}

			if (! $error)
			{
				$this->db->begin();

				$this->dao->label		= $label;
				$this->dao->description	= $description;
				$this->dao->visible		= ((! empty($visible) || ! empty($conf->global->MULTICOMPANY_VISIBLE_BY_DEFAULT))?1:0);
				$this->dao->active		= ((! empty($active) || ! empty($conf->global->MULTICOMPANY_ACTIVE_BY_DEFAULT))?1:0);

				if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) {
					$this->dao->options['referent']	= (GETPOST('referring_entity', 'int') ? GETPOST('referring_entity', 'int') : null);

					foreach ($this->sharingelements as $element => $params) {
						$uppername = strtoupper($element);
						$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
						if (! empty($conf->global->$constname)) {
							$shareentities = GETPOST($element.'_to', 'array');
							$shareentities = array_unique($shareentities); sort($shareentities);
							$this->dao->options['sharings'][$element]	= (! empty($shareentities) ? $shareentities : null);
						}
					}

					if (! empty($conf->societe->enabled) && ! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED)) {
						foreach ($this->sharingobjects as $element => $params) {
							if (! empty($params['active'])) {
								$uppername = strtoupper($element);
								$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
								if (! empty($conf->global->$constname)) {
									$shareentities = GETPOST($element.'_to', 'array');
									$shareentities = array_unique($shareentities); sort($shareentities);
									$this->dao->options['sharings'][$element]	= (! empty($shareentities) ? $shareentities : null);
								}
							}
						}
					}
				}

				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($this->dao->table_element, true);
				$extrafields->setOptionalsFromPost($extralabels, $this->dao);

				$id = $this->dao->create($user);
				if ($id <= 0)
				{
					$error++;
					$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
					setEventMessage($errors, 'errors');
					$action = 'create';
				}

				if (! $error && $id > 0)
				{
					$country_id		= GETPOST('country_id', 'int');
					$country		= getCountry($country_id, 'all');
					$country_code	= $country['code'];
					$country_label	= $country['label'];

					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_COUNTRY", $country_id.':'.$country_code.':'.$country_label,'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_NOM",$name,'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_ADDRESS",GETPOST('address', 'alpha'),'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_TOWN",GETPOST('town', 'alpha'),'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_ZIP",GETPOST('zipcode', 'alpha'),'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_STATE",GETPOST('departement_id', 'int'),'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_MONNAIE",GETPOST('currency_code', 'alpha'),'chaine',0,'',$id);
					dolibarr_set_const($this->db, "MAIN_LANG_DEFAULT",GETPOST('main_lang_default', 'alpha'),'chaine',0,'',$id);

					$dir	= "/multicompany/sql/";

					// Load sql init_new_entity.sql file
					$file 	= 'init_new_entity_nocrypt.sql';
					if (! empty($conf->db->dolibarr_main_db_encryption) && ! empty($conf->db->dolibarr_main_db_cryptkey))
					{
						$file = 'init_new_entity.sql';
					}
					$fullpath = dol_buildpath($dir.$file);

					if (file_exists($fullpath))
					{
						$result=run_sql($fullpath,1,$id);
					}

					$dir	= "/multicompany/sql/dict/";

					foreach($this->sharingdicts as $dict => $data)
					{
						// Load sql init_new_entity_dict.sql file
						$file 	= 'init_new_entity_'.$dict.'.sql';
						$fullpath = dol_buildpath($dir.$file);

						if (file_exists($fullpath))
						{
							$result=run_sql($fullpath,1,$id);
						}
					}

					$this->db->commit();
				}
				else
				{
					$this->db->rollback();
				}
			}
		}
		else if ($action == 'edit' && $user->admin && ! $user->entity)
		{
			$error=0;

			if ($this->dao->fetch($id) < 0)
			{
				$error++;
				setEventMessage($langs->trans("ErrorEntityIsNotValid"), 'errors');
				$action = '';
			}
		}
		else if ($action == 'update' && empty($cancel) && $id > 0 && $user->admin && ! $user->entity)
		{
			$error=0;

			$ret = $this->dao->fetch($id);
			if ($ret < 0)
			{
				$error++;
				setEventMessage($langs->trans("ErrorEntityIsNotValid"), 'errors');
				$action = '';
			}
			else if (empty($label))
			{
				$error++;
				setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("Label")), 'errors');
				$action = 'edit';
			}
			else if (empty($name))
			{
				$error++;
				setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("CompanyName")), 'errors');
				$action = 'edit';
			}

			// Verify if label already exist in database
			if (! $error)
			{
				$this->dao->getEntities();
				if (! empty($this->dao->entities))
				{
					foreach($this->dao->entities as $entity)
					{
						if ($entity->id == $this->dao->id) continue;
						if (strtolower($entity->label) == strtolower($label)) $error++;
					}
					if ($error)
					{
						setEventMessage($langs->trans("ErrorEntityLabelAlreadyExist"), 'errors');
						$action = 'edit';
					}
				}
			}

			if (! $error)
			{
				$this->db->begin();

				$this->dao->label = $label;
				$this->dao->description	= $description;

				if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) {
					$this->dao->options['referent']	= (GETPOST('referring_entity', 'int') ? GETPOST('referring_entity', 'int') : null);
					foreach ($this->sharingelements as $element => $params) {
						$uppername = strtoupper($element);
						$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
						if (! empty($conf->global->$constname)) {
							$shareentities = GETPOST($element.'_to', 'array');
							$shareentities = array_unique($shareentities); sort($shareentities);
							$this->dao->options['sharings'][$element]	= (! empty($shareentities) ? $shareentities : null);
						}
					}
					if (! empty($conf->societe->enabled) && ! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED)) {
						foreach ($this->sharingobjects as $element => $params) {
							if (! empty($params['active'])) {
								$uppername = strtoupper($element);
								$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
								if (! empty($conf->global->$constname)) {
									$shareentities = GETPOST($element.'_to', 'array');
									$shareentities = array_unique($shareentities); sort($shareentities);
									$this->dao->options['sharings'][$element]	= (! empty($shareentities) ? $shareentities : null);
								}
							}
						}
					}
				}

				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($this->dao->table_element, true);
				$extrafields->setOptionalsFromPost($extralabels, $this->dao);

				$ret = $this->dao->update($this->dao->id, $user);
				if ($ret <= 0)
				{
					$error++;
					$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
					setEventMessage($errors, 'errors');
					$action = 'edit';
				}

				if (! $error && $ret > 0)
				{
					$country_id		= GETPOST('country_id', 'int');
					$country		= getCountry($country_id, 'all');
					$country_code	= $country['code'];
					$country_label	= $country['label'];

					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_COUNTRY", $country_id.':'.$country_code.':'.$country_label,'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_NOM",$name,'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_ADDRESS",GETPOST('address', 'alpha'),'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_TOWN",GETPOST('town', 'alpha'),'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_ZIP",GETPOST('zipcode', 'alpha'),'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_INFO_SOCIETE_STATE",GETPOST('departement_id', 'int'),'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_MONNAIE",GETPOST('currency_code', 'alpha'),'chaine',0,'',$this->dao->id);
					dolibarr_set_const($this->db, "MAIN_LANG_DEFAULT",GETPOST('main_lang_default', 'alpha'),'chaine',0,'',$this->dao->id);

					$this->db->commit();
				}
				else
				{
					$this->db->rollback();
				}
			}
		}
	}

	/**
	 * 	Return action of hook
	 * 	@param		object			Linked object
	 */
	public function doActions($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $mc, $contextpage;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		// Clear constants cache after company infos update
		if (is_array($currentcontext))
		{
			if ((in_array('admincompany', $currentcontext) || in_array('adminihm', $currentcontext)) && ($action == 'update' || $action == 'updateedit'))
			{
				clearCache($conf->entity);
				clearCache('constants_' . $conf->entity);
			}
			else if ((in_array('groupcard', $currentcontext) || in_array('groupperms', $currentcontext)) && $object->element == 'usergroup')
			{
				global $entity;

				// Users/Groups management only in master entity if transverse mode
				if ($conf->entity > 1 && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
					accessforbidden();
				}

				if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
					$entity=(GETPOST('entity','int') ? GETPOST('entity','int') : $conf->entity);
				} else {
					$entity=(! empty($object->entity) ? $object->entity : $conf->entity);
				}

				// Add/Remove user into group
				if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && in_array('groupcard', $currentcontext) && ($action == 'adduser' || $action =='removeuser') && (! empty($userid) && $userid > 0) && $caneditperms)
				{
					if ($action == 'adduser')
					{
						$entities = GETPOST("entities", "array", 3);

						if (is_array($entities) && ! empty($entities))
						{
							$error=0;

							foreach ($entities as $entity_id)
							{
								$object->fetch($id);
								$object->oldcopy = clone $object;

								$edituser = new User($this->db);
								$edituser->fetch($userid);
								$result=$edituser->SetInGroup($object->id, $entity_id);
								if ($result < 0)
								{
									$error++;
									break;
								}
							}
							if (!$error)
							{
								header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
								exit;
							}
							else
							{
								$this->error = $edituser->error;
								$this->errors = $edituser->errors;
								return -1;
							}
						}
					}
					else if ($action == 'removeuser')
					{
						$object->fetch($id);
						$object->oldcopy = clone $object;

						$edituser = new User($this->db);
						$edituser->fetch($userid);
						$result=$edituser->RemoveFromGroup($object->id, $entity);

						if ($result > 0)
						{
							header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
							exit;
						}
						else
						{
							$this->error = $object->error;
							$this->errors = $object->errors;
							return -1;
						}
					}
					return 1;
				}
			}
			else if ((in_array('usercard', $currentcontext) || in_array('userperms', $currentcontext)) && $object->element == 'user')
			{
				global $entity, $caneditperms;

				// Users/Groups management only in master entity if transverse mode
				if ($conf->entity > 1 && $conf->global->MULTICOMPANY_TRANSVERSE_MODE)
				{
					if (in_array('usercard', $currentcontext) && ($action == 'create' || $action == 'adduserldap')) {
						accessforbidden();
					} else if (in_array('userperms', $currentcontext)) {
						$caneditperms = false;
					}
				}

				if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
					$entity = (GETPOST('entity','int') ? GETPOST('entity','int') : $conf->entity);

					// Check usergroup if user not in master entity
					if (in_array('userperms', $currentcontext) && ! empty($user->admin) && empty($user->entity) && $conf->entity == 1)
					{
						require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
						$group = new UserGroup($this->db);
						$ret = $group->listGroupsForUser($object->id, false);
						if (! empty($ret[$object->id]->usergroup_entity)) {
							sort($ret[$object->id]->usergroup_entity);
							if ($ret[$object->id]->usergroup_entity[0] > 1) {
								$entity = $ret[$object->id]->usergroup_entity[0];
							}
						}
					}
				} else {
					$entity=(! empty($object->entity) ? $object->entity : $conf->entity);
				}

				// Action add usergroup
				if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && in_array('usercard', $currentcontext) && ($action == 'addgroup' || $action == 'removegroup') && (! empty($group) && $group > 0) && $caneditgroup)
				{
					if ($action == 'addgroup')
					{
						$entities = GETPOST("entities", "array", 3);

						if (is_array($entities) && ! empty($entities))
						{
							$error=0;

							foreach ($entities as $entity_id)
							{
								$object->fetch($id);
								$result = $object->SetInGroup($group, $entity_id);
								if ($result < 0)
								{
									$error++;
									break;
								}
							}
							if ($error)
							{
								$this->error = $object->error;
								$this->errors = $object->errors;
								return -1;
							}
							else
							{
								header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
								exit;
							}
						}
					}
					else if ($action == 'removegroup')
					{
						$object->fetch($id);
						$result = $object->RemoveFromGroup($group, $entity);
						if ($result > 0) {
							header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $object->id);
							exit;
						}
						else
						{
							$this->error = $object->error;
							$this->errors = $object->errors;
							return -1;
						}
					}
					return 1;
				}
			}
			else if (in_array('productcard', $currentcontext) && ($object->element == 'product' || $object->element == 'service'))
			{
				if ($action != 'create' && $action != 'add')
				{
					if ($object->entity != $conf->entity)
					{
						global $usercanread, $usercancreate, $usercandelete;

						/*if (empty($user->rights->multicompany->product->read)) {
							$usercanread = false;
						}*/
						if (empty($user->rights->multicompany->product->write)) {
							$usercancreate = false;
						}
						if (empty($user->rights->multicompany->product->delete)) {
							$usercandelete = false;
						}
					}
				}
			}
			else if (in_array('propalcard', $currentcontext) && $object->element == 'propal')
			{
				if ($action != 'create' && $action != 'add')
				{
					if ($object->entity != $conf->entity)
					{
						global $usercanread, $usercancreate, $usercandelete, $usercanvalidate, $usercansend, $usercanclose;
						global $permissionnote, $permissiondellink, $permissiontoedit;
						global $disableedit, $disablemove, $disableremove;

						$this->getInstanceDao();

						$constants = array('PROPALE_ADDON','PROPALE_SAPHIR_MASK');
						foreach ($constants as $constname)
						{
							$res = $this->dao->getEntityConfig($object->entity, $constname);
							if (! empty($res[$constname])) {
								$newconstant = $constname.'_'.$object->entity;
								$conf->global->$newconstant = $res[$constname];
							}
						}

						if (empty($user->rights->multicompany->propal->read)) {
							$usercanread = false;
						}
						if (empty($user->rights->multicompany->propal->write)) {
							$usercancreate = false;

							$permissionnote = $usercancreate; // Used by the include of actions_setnotes.inc.php
							$permissiondellink = $usercancreate;	// Used by the include of actions_dellink.inc.php
							$permissiontoedit = $usercancreate; // Used by the include of actions_lineupdown.inc.php

							// for object lines
							$disableedit = true;
							$disablemove = true;
							$disableremove = true;
						}
						if (empty($user->rights->multicompany->propal->delete)) {
							$usercandelete = false;
						}
						if ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($usercancreate)) || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->multicompany->propal_advance->validate))) {
							$usercanvalidate = false;
						}
						if ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($usercanread)) || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->multicompany->propal_advance->send))) {
							$usercansend = false;
						}
						if (empty($user->rights->multicompany->propal->close)) {
							$usercanclose = false;
						}
					}
				}
			}
		}

		//var_dump($_POST);
/*
		if (empty($_SESSION['dol_tables_list_fk_soc']))
		{
			$_SESSION['dol_tables_list_fk_soc'] = getTablesWithField('fk_soc', array());
		}
		var_dump($_SESSION['dol_tables_list_fk_soc']);
*/
		//$include=false;
		//$exclude=false;
/*
		$exclude = array(
			MAIN_DB_PREFIX . 'user',
			MAIN_DB_PREFIX . 'user_employment',
			MAIN_DB_PREFIX . 'user_param',
			MAIN_DB_PREFIX . 'user_rib',
			MAIN_DB_PREFIX . 'user_rights',
			MAIN_DB_PREFIX . 'usergroup',
			MAIN_DB_PREFIX . 'usergroup_rights',
			MAIN_DB_PREFIX . 'usergroup_user',
			MAIN_DB_PREFIX . 'rights_def',
		);
*/
		//$exclude = '/(const|user|rights\_def)+/';
		//$include = '/(const|user|rights\_def)+/';

		//if (empty($_SESSION['dol_tables_list_entity']))
/*		{
			$_SESSION['dol_tables_list_entity'] = getTablesWithField('entity', $exclude, $include);
		}

		var_dump($_SESSION['dol_tables_list_entity']);
*/
/*
		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) && ! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
		{
			if (in_array($contextpage, $this->thirdpartycontextlist) || in_array($contextpage, $this->contactcontextlist))
			{
				if (GETPOST('confirmmassaction') && GETPOST('massaction') == 'modify_entity')
				{
					var_dump($_POST['toselect']);
				}
			}
		}
*/
		return 0;
	}

	/**
	 *
	 */
	public function showLinkedObjectBlock($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		//var_dump($object->linkedObjects);

		foreach($object->linkedObjects as $objecttype => $objects)
		{
			foreach($objects as $key => $tmpobj)
			{
				if (empty($tmpobj->entity)) continue; // for debug

				if ($tmpobj->entity != $conf->entity)
				{
					$element = $objecttype;
					if ($objecttype == 'propal') $element = 'proposal';
					if ($objecttype == 'commande') $element = 'order';
					if ($objecttype == 'facture') $element = 'invoice';

					//var_dump($element);var_dump($mc->sharings[$element]);
					//var_dump($object->linkedObjects[$objecttype][$key]);

					if (! empty($mc->sharings[$element]) && in_array($tmpobj->entity, $mc->sharings[$element]))
					{
						//nothing
					}
					else
					{
						unset($object->linkedObjects[$objecttype][$key]);
					}
				}
			}
		}

		return 0;
	}

	/**
	 *
	 */
	public function showLinkToObjectBlock($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		$perms = 1;

		if (in_array('propalcard', $currentcontext) && $object->element == 'propal')
		{
			if ($object->entity != $conf->entity)
			{
				$perms = ! empty($user->rights->multicompany->propal->write);
			}
		}

		$this->results = array('propal' => array('enabled'=>$conf->propal->enabled, 'perms'=>$perms, 'label'=>'LinkToProposal',	'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_client, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('propal').')'),
			'order'=>array('enabled'=>$conf->commande->enabled, 'perms'=>$perms, 'label'=>'LinkToOrder', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_client, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('commande').')'),
			'invoice'=>array('enabled'=>$conf->facture->enabled, 'perms'=>$perms, 'label'=>'LinkToInvoice', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.facnumber as ref, t.ref_client, t.total as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('facture').')'),
			'contrat'=>array('enabled'=>$conf->contrat->enabled , 'perms'=>$perms, 'label'=>'LinkToContract', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, '' as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."contrat as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('contract').')'),
			'fichinter'=>array('enabled'=>$conf->ficheinter->enabled, 'perms'=>$perms, 'label'=>'LinkToIntervention', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."fichinter as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('intervention').')'),
			'supplier_proposal'=>array('enabled'=>$conf->supplier_proposal->enabled , 'perms'=>$perms, 'label'=>'LinkToSupplierProposal', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, '' as ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."supplier_proposal as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('supplier_proposal').')'),
			'order_supplier'=>array('enabled'=>$conf->supplier_order->enabled , 'perms'=>$perms, 'label'=>'LinkToSupplierOrder', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande_fournisseur as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('commande_fournisseur').')'),
			'invoice_supplier'=>array('enabled'=>$conf->supplier_invoice->enabled , 'perms'=>$perms, 'label'=>'LinkToSupplierInvoice', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('facture_fourn').')')
		);

		return 1;
	}

	/**
	 *
	 */
	public function addMoreActionsButtons($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		/*if (in_array('productcard', $currentcontext) && ($object->element == 'product' || $object->element == 'service'))
		{
			if ($object->entity != $conf->entity)
			{
				$user->rights->produit->creer = 0;
				$user->rights->produit->supprimer = 0;
				$user->rights->service->creer = 0;
				$user->rights->service->supprimer = 0;

				//return 1;
			}
		}*/

		return 0;
	}

	public function printUserPasswordField($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;

		if (empty($conf->multicompany->enabled)) return 0;

		$langs->load('multicompany@multicompany');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		$this->resprints = "\n".'<!-- BEGIN multicompany printUserPasswordField -->'."\n";

		if (in_array('usercard', $currentcontext) && $object->element == 'user' && ! empty($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX) && checkMulticompanyAutentication())
		{
			if ($action == 'create')
			{
				$this->resprints.= '<input size="30" maxsize="32" type="text" name="password" value="" autocomplete="new-password">';
			}
			else if ($action == 'edit')
			{
				if ($caneditpassword)
				{
					$this->resprints.= '<input size="30" maxlength="32" type="password" class="flat" name="password" value="'.$object->pass.'" autocomplete="new-password">';
				}
				else
				{
					$this->resprints.= preg_replace('/./i','*',$object->pass);
				}
			}
			else
			{
				if ($object->pass) $this->resprints.= preg_replace('/./i','*',$object->pass);
				else
				{
					if ($user->admin) $this->resprints.= ($valuetoshow?(' '.$langs->trans("or").' '):'').$langs->trans("Crypted").': '.$object->pass_indatabase_crypted;
					else $this->resprints.= $langs->trans("Hidden");
				}
			}
		}

		$this->resprints.= '<!-- END multicompany printUserPasswordField -->'."\n";

		return 0;
	}

	/**
	 *
	 */
	public function formObjectOptions($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $form;

		if (empty($conf->multicompany->enabled)) return 0;

		$langs->load('multicompany@multicompany');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		$this->resprints = "\n".'<!-- BEGIN multicompany formObjectOptions -->'."\n";

		if (in_array('thirdpartycard', $currentcontext) && $object->element == 'societe' && ! empty($user->admin) && empty($user->entity))
		{
			if ($action == 'create')
			{
				$this->resprints.= '<tr><td>'.fieldLabel('Entity','entity').'</td><td colspan="3" class="maxwidthonsmartphone">';
				$s = $this->select_entities($conf->entity);
				$this->resprints.= $form->textwithpicto($s,$langs->trans("ThirdpartyEntityDesc"),1);
				$this->resprints.= '</td></tr>'."\n";
			}
		}
		else if (in_array('contactcard', $currentcontext) && $object->element == 'contact' && ! empty($user->admin) && empty($user->entity))
		{
			if ($action == 'create' && empty($objsoc))
			{
				$this->resprints.= '<tr><td>'.fieldLabel('Entity','entity').'</td><td colspan="3" class="maxwidthonsmartphone">';
				$s = $this->select_entities($conf->entity);
				$this->resprints.= $form->textwithpicto($s,$langs->trans("ContactEntityDesc"),1);
				$this->resprints.= '</td></tr>'."\n";
			}
		}
		else if (in_array('usercard', $currentcontext) && $object->element == 'user')
		{
			if ($action == 'edit')
			{
				// TODO check if user not linked with the current entity before change entity (thirdparty, invoice, etc.) !!
				if (empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && ! empty($user->admin) && empty($user->entity))
				{
					$this->resprints.= '<tr><td>'.$langs->trans("Entity").'</td>';
					$this->resprints.= '<td>'.$this->select_entities($object->entity);
					$this->resprints.= "</td></tr>\n";
				}
				else
				{
					$this->resprints.= '<input type="hidden" name="entity" value="'.$conf->entity.'" />';
				}
			}
			else if ($action == 'create')
			{
				if (empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && ! empty($user->admin) && empty($user->entity))
				{
					$this->resprints.= '<tr><td>'.$langs->trans("Entity").'</td>';
					$this->resprints.= '<td>'.$this->select_entities($conf->entity);
					$this->resprints.= "</td></tr>\n";
				}
				else
				{
					$this->resprints.= '<input type="hidden" name="entity" value="'.$conf->entity.'" />';
				}
			}
			else if ($action != 'adduserldap')
			{
				if (empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && ! empty($user->admin) && empty($user->entity))
				{
					$this->resprints.= '<tr><td>'.$langs->trans("Entity").'</td><td>';
					if (empty($object->entity))
					{
						$this->resprints.= $langs->trans("AllEntities");
					}
					else
					{
						$this->getInfo($object->entity);
						$this->resprints.= $this->label;
					}
					$this->resprints.= "</td></tr>\n";
				}
			}
		}

		$this->resprints.= '<!-- END multicompany formObjectOptions -->'."\n";

		return 0;
	}

	/**
	 *
	 */
	public function formCreateThirdpartyOptions($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $form;

		if (empty($conf->multicompany->enabled)) return 0;

		$langs->load('multicompany@multicompany');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		//echo 'OK';

		//$this->resprints = 'OK';

		return 0;
	}

	/**
	 *
	 */
	public function formAddUserToGroup($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $form, $mc, $exclude;

		if (empty($conf->multicompany->enabled)) return 0;

		$langs->load('multicompany@multicompany');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		$this->resprints = '';

		if (is_array($currentcontext))
		{
			if (in_array('usercard', $currentcontext) && $object->element == 'user')
			{
				if ($action != 'edit' && $action != 'presend' && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
				{
					$this->resprints = "\n".'<!-- BEGIN multicompany formAddUserToGroup -->'."\n";

					if (! empty($groupslist))
					{
						$exclude=array();
					}

					$this->resprints.= '<tr class="liste_titre"><th class="liste_titre">'.$langs->trans("Groups").'</th>'."\n";
					if (! empty($user->admin))
					{
						$this->resprints.= '<th class="liste_titre">'.$langs->trans("Entity").'</th>';
					}
					$this->resprints.= '<th class="liste_titre" align="right">';
					if ($caneditgroup && empty($user->entity))
					{
						// Users/Groups management only in master entity if transverse mode
						if ($conf->entity == 1)
						{
							$this->resprints.= $form->select_dolgroups('', 'group', 1, $exclude, 0, '', '', $object->entity);
							$this->resprints.= ' &nbsp; ';
							if ($conf->entity == 1)
							{
								$entities = $this->getEntitiesList();
								$this->resprints.= $form->multiselectarray('entities', $entities, GETPOST('entities', 'array'), '', 0, '', 0, '20%');
							}
							else
							{
								$this->resprints.= '<input type="hidden" name="entity" value="'.$conf->entity.'" />';
							}
							$this->resprints.= '<input type="submit" class="button" value="'.$langs->trans("Add").'" />';
						}
					}
					$this->resprints.= '</th></tr>'."\n";

					/*
					 * Groups assigned to user
					 */
					if (! empty($groupslist))
					{
						foreach($groupslist as $group)
						{
							$this->resprints.= '<tr class="oddeven">';
							$this->resprints.= '<td>';
							if ($caneditgroup)
							{
								$this->resprints.= '<a href="'.DOL_URL_ROOT.'/user/group/card.php?id='.$group->id.'">'.img_object($langs->trans("ShowGroup"),"group").' '.$group->name.'</a>';
							}
							else
							{
								$this->resprints.= img_object($langs->trans("ShowGroup"),"group").' '.$group->name;
							}
							$this->resprints.= '</td>';
							if (! empty($user->admin))
							{
								$this->resprints.= '<td class="valeur">';
								if (! empty($group->usergroup_entity))
								{
									$nb=0;
									foreach($group->usergroup_entity as $group_entity)
									{
										$mc->getInfo($group_entity);
										$this->resprints.= ($nb > 0 ? ', ' : '') . $mc->label . (empty($mc->active) ? ' ('.$langs->trans('Disabled').')' : (empty($mc->visible) ? ' ('.$langs->trans('Hidden').')' : '') );
										if ($conf->entity == 1 && empty($user->entity)) {
											$this->resprints.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=removegroup&amp;group='.$group->id.'&amp;entity='.$group_entity.'">';
											$this->resprints.= img_picto($langs->trans("RemoveFromGroup"), 'unlink');
											$this->resprints.= '</a>';
										}
										$nb++;
									}
								}
							}
							$this->resprints.= '<td align="right">&nbsp;</td></tr>'."\n";
						}
					}
					else
					{
						$this->resprints.= '<tr '.$bc[false].'><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
					}

					$this->resprints.= '<!-- END multicompany formAddUserToGroup -->'."\n";

					return 1;
				}
			}
			else if (in_array('groupcard', $currentcontext) && $object->element == 'usergroup')
			{
				if ($action != 'edit' && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
				{
					$this->resprints = "\n".'<!-- BEGIN multicompany formAddUserToGroup -->'."\n";

					if (! empty($object->members))
					{
						$exclude=array();
					}

					if ($caneditperms && empty($user->entity))
					{
						$this->resprints.= '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" method="POST">'."\n";
						$this->resprints.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
						$this->resprints.= '<input type="hidden" name="action" value="adduser">';
						$this->resprints.= '<table class="noborder" width="100%">'."\n";
						$this->resprints.= '<tr class="liste_titre"><td class="titlefield liste_titre">'.$langs->trans("NonAffectedUsers").'</td>'."\n";
						$this->resprints.= '<th class="liste_titre">';
						$this->resprints.= $form->select_dolusers('', 'user', 1, $exclude, 0, '', '', $object->entity);
						$this->resprints.= ' &nbsp; ';
						if ($conf->entity == 1)
						{
							$entities = $this->getEntitiesList();
							$this->resprints.= $form->multiselectarray('entities', $entities, GETPOST('entities', 'array'), '', 0, '', 0, '20%');
						}
						else
						{
							$this->resprints.= '<input type="hidden" name="entity" value="'.$conf->entity.'" />';
						}
						$this->resprints.= '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
						$this->resprints.= '</th></tr>'."\n";
						$this->resprints.= '</table></form>'."\n";
						$this->resprints.= '<br>';
					}

					/*
					 * Group members
					 */
					$this->resprints.= '<table class="noborder" width="100%">';
					$this->resprints.= '<tr class="liste_titre">';
					$this->resprints.= '<td class="liste_titre">'.$langs->trans("Login").'</td>';
					$this->resprints.= '<td class="liste_titre">'.$langs->trans("Lastname").'</td>';
					$this->resprints.= '<td class="liste_titre">'.$langs->trans("Firstname").'</td>';
					if ($conf->entity == 1)
					{
						$this->resprints.= '<td class="liste_titre">'.$langs->trans("Entity").'</td>';
					}
					$this->resprints.= '<td class="liste_titre" width="5" align="center">'.$langs->trans("Status").'</td>';
					$this->resprints.= '<td class="liste_titre" width="5" align="right">&nbsp;</td>';
					$this->resprints.= "</tr>\n";

					if (! empty($object->members))
					{
						foreach($object->members as $useringroup)
						{
							$this->resprints.= '<tr class="oddeven">';
							$this->resprints.= '<td>';
							$this->resprints.= $useringroup->getNomUrl(-1, '', 0, 0, 24, 0, 'login');
							if ($useringroup->admin  && ! $useringroup->entity) $this->resprints.= img_picto($langs->trans("SuperAdministrator"),'redstar');
							else if ($useringroup->admin) $this->resprints.= img_picto($langs->trans("Administrator"),'star');
							$this->resprints.= '</td>';
							$this->resprints.= '<td>'.$useringroup->lastname.'</td>';
							$this->resprints.= '<td>'.$useringroup->firstname.'</td>';
							if ($conf->entity == 1 && ! empty($user->admin))
							{
								$this->resprints.= '<td class="valeur">';
								if (! empty($useringroup->usergroup_entity))
								{
									$nb=0;
									foreach($useringroup->usergroup_entity as $group_entity)
									{
										$mc->getInfo($group_entity);
										$this->resprints.= ($nb > 0 ? ', ' : '') . $mc->label . (empty($mc->active) ? ' ('.$langs->trans('Disabled').')' : (empty($mc->visible) ? ' ('.$langs->trans('Hidden').')' : '') );
										if (empty($user->entity)) {
											$this->resprints.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=removeuser&amp;user='.$useringroup->id.'&amp;entity='.$group_entity.'">';
											$this->resprints.= img_picto($langs->trans("RemoveFromGroup"), 'unlink');
											$this->resprints.= '</a>';
										}
										$nb++;
									}
								}
								$this->resprints.= '</td>';
							}
							$this->resprints.= '<td align="center">'.$useringroup->getLibStatut(3).'</td>';
							$this->resprints.= '<td align="right">';
							$this->resprints.= "-";
							$this->resprints.= "</td></tr>\n";
						}
					}
					else
					{
						$this->resprints.= '<tr><td colspan="6" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
					}
					$this->resprints.= "</table>";

					$this->resprints.= '<!-- END multicompany formAddUserToGroup -->'."\n";

					return 1;
				}
			}
		}

		return 0;
	}

	/**
	 *
	 */
	public function moreHtmlRef($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $mc, $form;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		$this->resprints = "\n".'<!-- BEGIN multicompany moreHtmlRef -->'."\n";

		// if global sharings is enabled
		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			$this->getInfo($object->entity);

			// if third party sharing is enabled (is mandatory for some sharings)
			if (! empty($conf->societe->enabled) && ! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if ($object->element == 'societe')
				{
					if (in_array('thirdpartycard', $currentcontext))
					{
						if ($action != 'create' && $action != 'edit')
						{
							if ($object->isObjectUsed($object->id) === 0 && ((! empty($user->admin) && ! $user->entity) || ! empty($user->rights->multicompany->thirdparty->write)))
							{
								$this->resprints.= '<div id="modify-entity-thirdparty" class="refidno modify-entity" data-tooltip="'.$langs->trans('ThirdpartyModifyEntity').'" data-tooltip-position="bottom">';
								$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
								$this->resprints.= '</div>';
								$this->resprints.= $this->getModifyEntityDialog('thirdparty', 'modifyEntity', $object);
							}
							else
							{
								$this->resprints.= '<div class="refidno modify-entity-disabled" data-tooltip="'.$langs->trans("ModifyEntityNotAllowed").'" data-tooltip-position="bottom">';
								$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
								$this->resprints.= '</div>';
							}
						}
					}
					else
					{
						$this->resprints.= '<div class="refidno">';
						$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
						$this->resprints.= '</div>';
					}
				}
				else if ($object->element == 'contact')
				{
					if (in_array('contactcard', $currentcontext))
					{
						if ($action != 'create' && $action != 'edit')
						{
							if (empty($object->socid) && ((! empty($user->admin) && ! $user->entity) || ! empty($user->rights->multicompany->contact->write)))
							{
								$this->resprints.= '<div id="modify-entity-contact" class="refidno modify-entity" data-tooltip="'.$langs->trans('ContactModifyEntity').'" data-tooltip-position="bottom">';
								$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
								$this->resprints.= '</div>';
								$this->resprints.= $this->getModifyEntityDialog('contact', 'modifyEntity', $object);
							}
							else
							{
								$this->resprints.= '<div class="refidno modify-entity-disabled" data-tooltip="'.$langs->trans("ModifyEntityNotAllowed").'" data-tooltip-position="bottom">';
								$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
								$this->resprints.= '</div>';
							}
						}
					}
					else
					{
						$this->resprints.= '<div class="refidno">';
						$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
						$this->resprints.= '</div>';
					}
				}
			}

			if ((! empty($conf->product->enabled) || ! empty($conf->service->enabled)) && $object->element == 'product' && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				$this->resprints.= '<div class="refidno">';
				$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
				$this->resprints.= '</div>';
			}

			if (! empty($conf->propal->enabled) && $object->element == 'propal' && in_array('propalcard', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
			{
				$this->resprints.= '<div class="refidno">';
				$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
				$this->resprints.= '</div>';
			}
		}

		if ($object->element == 'user' || $object->element == 'usergroup')
		{
			if (empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && ! empty($user->admin) && empty($user->entity))
			{
				$this->getInfo($object->entity);
				$this->resprints.= '<div class="refidno">';
				$this->resprints.= '<span class="fa fa-globe"></span><span class="multiselect-selected-title-text">'.$this->label.'</span>';
				$this->resprints.= '</div>';
			}
		}

		$this->resprints.= '<!-- END multicompany moreHtmlRef -->'."\n";

		return 0;
	}

	/**
	 *
	 */
	public function moreHtmlStatus($parameters=false, &$object, &$action='')
	{
		global $conf, $user;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		//$this->resprints.= 'OK';

		return 0;
	}

	/**
	 *
	 */
	public function printUserListWhere($parameters=false)
	{
		global $conf, $user;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$this->resprints = '';

		if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
			if (! empty($user->admin) && empty($user->entity) && $conf->entity == 1) {
				$this->resprints.= " WHERE u.entity IS NOT NULL"; // Show all users
			} else {
				$this->resprints.= ",".MAIN_DB_PREFIX."usergroup_user as ug";
				$this->resprints.= " WHERE (ug.fk_user = u.rowid";
				$this->resprints.= " AND ug.entity IN (".getEntity('user')."))";
				$this->resprints.= " OR u.entity = 0"; // Show always superadmin
			}
			return 1;
		}

		return 0;
	}

	/**
	 *
	 * @return number
	 */
	public function addMoreMassActions($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			// name="massaction"
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext) || in_array('contactlist', $currentcontext))
				{
					$langs->load('multicompany@multicompany');
					$this->resprints = '<option value="modify_entity" disabled="disabled">'.$langs->trans('ModifyEntity').'</option>';
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @return number
	 */
	public function printFieldListSelect($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			// Thirdparty sharing is mandatory to share document (propal, etc...)
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext))
				{
					//if (! empty($arrayfields['s.entity']['checked']))
					{
						$this->resprints = ", s.entity";
					}
				}
				else if (in_array('contactlist', $currentcontext))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$this->resprints = ", p.entity";
					}
				}
				else if (in_array('propallist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$this->resprints = ", p.entity";
					}
				}
			}

			if (in_array('productservicelist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				//if (! empty($arrayfields['p.entity']['checked']))
				{
					$this->resprints = ", p.entity";
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @param boolean $parameters
	 * @return number
	 */
	public function printFieldListWhere($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext))
				{
					//if (! empty($arrayfields['s.entity']['checked']))
					{
						$search_entity = GETPOST('search_entity','int');

						if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
						{
							$search_entity = '';
						}

						if ($search_entity > 0)
						{
							$this->resprints = " AND s.entity = " . $search_entity;
						}
					}
				}
				else if (in_array('contactlist', $currentcontext))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$search_entity = GETPOST('search_entity','int');

						if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
						{
							$search_entity = '';
						}

						if ($search_entity > 0)
						{
							$this->resprints = " AND p.entity = " . $search_entity;
						}
					}
				}
				else if (in_array('propallist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$search_entity = GETPOST('search_entity','int');

						if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
						{
							$search_entity = '';
						}

						if ($search_entity > 0)
						{
							$this->resprints = " AND p.entity = " . $search_entity;
						}
					}
				}
			}

			if (in_array('productservicelist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				//if (! empty($arrayfields['p.entity']['checked']))
				{
					$search_entity = GETPOST('search_entity','int');

					if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
					{
						$search_entity = '';
					}

					if ($search_entity > 0)
					{
						$this->resprints = " AND p.entity = " . $search_entity;
					}
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @param boolean $parameters
	 * @return number
	 */
	public function printFieldListOption($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext) || in_array('contactlist', $currentcontext))
				{
					//if (! empty($arrayfields['s.entity']['checked']) || ! empty($arrayfields['p.entity']['checked']))
					{
						$search_entity = GETPOST('search_entity','int');

						if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
						{
							$search_entity = '';
						}

						// Entity
						$this->resprints = '<td class="liste_titre maxwidthonsmartphone" align="center">';
						$this->resprints.= $this->select_entities($search_entity,'search_entity','',false,false,true,explode(",", $mc->entities['thirdparty']),'','minwidth100imp');
						$this->resprints.= '</td>';
					}
				}
				else if (in_array('propallist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$search_entity = GETPOST('search_entity','int');

						if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
						{
							$search_entity = '';
						}

						// Entity
						$this->resprints = '<td class="liste_titre maxwidthonsmartphone" align="center">';
						$this->resprints.= $this->select_entities($search_entity,'search_entity','',false,false,true,explode(",", $mc->entities['proposal']),'','minwidth100imp');
						$this->resprints.= '</td>';
					}
				}
			}

			if (in_array('productservicelist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				//if (! empty($arrayfields['p.entity']['checked']))
				{
					$search_entity = GETPOST('search_entity','int');

					if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
					{
						$search_entity = '';
					}

					// Entity
					$this->resprints = '<td class="liste_titre maxwidthonsmartphone" align="center">';
					$this->resprints.= $this->select_entities($search_entity,'search_entity','',false,false,true,explode(",", $mc->entities['product']),'','minwidth100imp');
					$this->resprints.= '</td>';
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @param boolean $parameters
	 * @return number
	 */
	public function printFieldListTitle($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext))
				{
					//if (! empty($arrayfields['s.entity']['checked']))
					{
						$this->resprints = getTitleFieldOfList('Entity',0,$_SERVER["PHP_SELF"],"s.entity","",$param,'align="center"',$sortfield,$sortorder);
					}
				}
				else if (in_array('contactlist', $currentcontext))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$this->resprints = getTitleFieldOfList('Entity',0,$_SERVER["PHP_SELF"],"p.entity","",$param,'align="center"',$sortfield,$sortorder);
					}
				}
				else if (in_array('propallist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$this->resprints = getTitleFieldOfList('Entity',0,$_SERVER["PHP_SELF"],"p.entity","",$param,'align="center"',$sortfield,$sortorder);
					}
				}
			}

			if (in_array('productservicelist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				//if (! empty($arrayfields['p.entity']['checked']))
				{
					$this->resprints = getTitleFieldOfList('Entity',0,$_SERVER["PHP_SELF"],"p.entity","",$param,'align="center"',$sortfield,$sortorder);
				}
			}
		}

		return 0;
	}

	/**
	 *
	 * @param boolean $parameters
	 * @return number
	 */
	public function printFieldListValue($parameters=false)
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			if (! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) && ! empty($mc->sharings['thirdparty']))
			{
				if (in_array('thirdpartylist', $currentcontext) || in_array('contactlist', $currentcontext))
				{
					//if (! empty($arrayfields['s.entity']['checked']) || ! empty($arrayfields['p.entity']['checked']))
					{
						$this->getInfo($obj->entity);
						$this->resprints = '<td align="center">'.$this->label."</td>\n";
					}
				}
				else if (in_array('propallist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PROPOSAL_SHARING_ENABLED) && ! empty($mc->sharings['proposal']))
				{
					//if (! empty($arrayfields['p.entity']['checked']))
					{
						$this->getInfo($obj->entity);
						$this->resprints = '<td align="center">'.$this->label."</td>\n";
					}
				}
			}

			if (in_array('productservicelist', $currentcontext) && ! empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) && ! empty($mc->sharings['product']))
			{
				//if (! empty($arrayfields['p.entity']['checked']))
				{
					$this->getInfo($obj->entity);
					$this->resprints = '<td align="center">'.$this->label."</td>\n";
				}
			}
		}

		return 0;
	}

	/**
	 *
	 */
	public function insertExtraHeader($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;
		global $mc;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($user->admin) && empty($user->entity) && $conf->entity == 1 && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
		{
			if (in_array('userperms', $currentcontext) || in_array('groupperms', $currentcontext))
			{
				$this->getInstanceDao();

				if ($object->element == 'user')
				{
					$aEntities=array_keys($permsgroupbyentity);

					// Check usergroup if user not in master entity
					if (empty($aEntities))
					{
						require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
						$group = new UserGroup($this->db);
						$ret = $group->listGroupsForUser($object->id, false);
						if (! empty($ret[$object->id]->usergroup_entity)) {
							sort($ret[$object->id]->usergroup_entity);
							if ($ret[$object->id]->usergroup_entity[0] > 1) {
								$entity = $ret[$object->id]->usergroup_entity[0];
								$aEntities[$entity] = $entity;
							}
						}
					}

					if (! empty($aEntities))
					{
						sort($aEntities);
						$entity = (GETPOST('entity', 'int')?GETPOST('entity', 'int'):$aEntities[0]);
						$head = entity_prepare_head($object, $aEntities);
						$title = $langs->trans("Entities");
						dol_fiche_head($head, $entity, $title, 1, 'multicompany@multicompany');
					}
					else
					{
						print get_htmloutput_mesg(img_warning('default') . ' ' . $langs->trans("ErrorLinkUserGroupEntity"), '', 'mc-upgrade-alert', 1);
					}
				}
				else if ($object->element == 'usergroup')
				{
					$this->dao->getEntities();
					$aEntities=array();

					foreach ($this->dao->entities as $objEntity)
					{
						$aEntities[] = $objEntity->id;
					}

					$entity = (GETPOST('entity', 'int')?GETPOST('entity', 'int'):$aEntities[0]);
					$head = entity_prepare_head($object, $aEntities);
					$title = $langs->trans("Entities");
					dol_fiche_head($head, $entity, $title, 1, 'multicompany@multicompany');
				}

				// Check if advanced perms is enabled for current object entity
				$res = $this->dao->getEntityConfig($entity, 'MAIN_USE_ADVANCED_PERMS');
				if (empty($res['MAIN_USE_ADVANCED_PERMS'])) {
					unset($conf->global->MAIN_USE_ADVANCED_PERMS);
				}
			}
		}

		return 0;
	}

	/**
	 *
	 */
	public function insertExtraFooter($parameters=false, &$object, &$action='')
	{
		global $conf, $user;

		if (empty($conf->multicompany->enabled)) return 0;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$currentcontext = explode(':', $parameters['context']);

		if (! empty($user->admin) && empty($user->entity) && $conf->entity == 1 && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
		{
			if (in_array('userperms', $currentcontext) || in_array('groupperms', $currentcontext))
			{
				// Restore advanced perms if enabled for current entity
				$this->getInstanceDao();
				$res = $this->dao->getEntityConfig($conf->entity, 'MAIN_USE_ADVANCED_PERMS');
				if (! empty($res['MAIN_USE_ADVANCED_PERMS'])) {
					$conf->global->MAIN_USE_ADVANCED_PERMS = $res['MAIN_USE_ADVANCED_PERMS'];
				}
			}
		}

		return 0;
	}

	/**
	 *	Return combo list of entities.
	 *
	 *	@param	int		$selected	Preselected entity
	 *	@param	int		$htmlname	Name
	 *	@param	string	$option		Option
	 *	@param	boolean	$login		If use in login page or not
	 *  @param	boolean $exclude	Exclude
	 *  @param	boolean	$emptyvalue Emptyvalue
	 *  @param	boolean	$only		Only
	 *  @param	string	$all		Add 'All entities' value in combo list
	 *  @param	string	$cssclass	specific css class. eg 'minwidth200imp mycssclass'
	 *	@return	string
	 */
	public function select_entities($selected='', $htmlname='entity', $option='', $login=false, $exclude=false, $emptyvalue=false, $only=false, $all='', $cssclass='minwidth200imp')
	{
		global $user,$langs;

		$this->getInstanceDao();

		$this->dao->getEntities($login, $exclude);

		$out = '';

		if (is_array($this->dao->entities))
		{
			$out.= '<select class="flat maxwidth200onsmartphone multicompany_select '.$cssclass.'" id="'.$htmlname.'" name="'.$htmlname.'"'.$option.'>';

			if ($emptyvalue)
				$out.= '<option value="-1">&nbsp;</option>';

			if ($all)
				$out.= '<option value="0">'.$langs->trans("AllEntities").'</option>';

			foreach ($this->dao->entities as $entity)
			{
				if ($entity->active == 1 && ($entity->visible == 1 || ($user->admin && ! $user->entity)))
				{
					if (is_array($only) && ! empty($only) && ! in_array($entity->id, $only)) continue;
					if (! empty($user->login) && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && ! empty($user->entity) && $this->checkRight($user->id, $entity->id) < 0) continue;

					$out.= '<option value="'.$entity->id.'"';
					if ($selected == $entity->id) {
						$out.= ' selected="selected"';
					}
					$out.= '>';
					$out.= $entity->label;
					if (empty($entity->visible)) {
						$out.= ' ('.$langs->trans('Hidden').')';
					}
					$out.= '</option>';
				}
			}

			$out.= '</select>';
		}
		else {
			$out.= $langs->trans('NoEntityAvailable');
		}

		// Make select dynamic
		include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
		$out.= ajax_combobox($htmlname);

		return $out;
	}

	/**
	 *	Return multiselect list of entities.
	 *
	 *	@param	string			$htmlname			Name of select
	 *	@param	DaoMulticompany	$current			Current entity to manage
	 *	@param	bool			$onlyselected		true: show only selected, false: hide only selected
	 *	@return	string
	 */
	public function multiselect_entities($htmlname, $current, $onlyselected=false)
	{
		global $conf, $langs;

		$this->getInstanceDao();
		$this->dao->getEntities();

		$selectname = ($onlyselected ? $htmlname.'_to[]' : 'from[]');
		$selectid = ($onlyselected ? 'multiselect_shared_'.$htmlname.'_to' : 'multiselect_shared_'.$htmlname);

		$return = '<select name="'.$selectname.'" id="'.$selectid.'" class="form-control multiselect-select" size="6" multiple="multiple">';
		if (is_array($this->dao->entities))
		{
			foreach ($this->dao->entities as $entity)
			{
				if (is_object($current) && $current->id != $entity->id && $entity->active == 1)
				{
					if ((! $onlyselected && (empty($current->options['sharings'][$htmlname]) || ! in_array($entity->id, $current->options['sharings'][$htmlname])))		// All unselected
						|| ($onlyselected && is_array($current->options['sharings'][$htmlname]) && in_array($entity->id, $current->options['sharings'][$htmlname])))	// All selected
					{
						$return.= '<option class="oddeven multiselect-option" value="'.$entity->id.'">';
						$return.= $entity->label;
						if (empty($entity->visible))
						{
							$return.= ' ('.$langs->trans('Hidden').')';
						}
						$return.= '</option>';
					}
				}
			}
		}
		$return.= '</select>';

		return $return;
	}

	/**
	 *	Return multiselect list of entities.
	 *
	 *	@param	string	$htmlname	Name of select
	 *	@param	array	$selected	Entities already selected
	 *	@param	string	$option		Option
	 *	@return	string
	 */
	public function multiSelectEntities($htmlname, $selected=null, $option=null)
	{
		global $conf, $langs;

		$this->getInstanceDao();
		$this->dao->getEntities();

		$return = '<select id="'.$htmlname.'" class="multiselect" multiple="multiple" name="'.$htmlname.'[]" '.$option.'>';
		if (is_array($this->dao->entities))
		{
			foreach ($this->dao->entities as $entity)
			{
				$return.= '<option value="'.$entity->id.'" ';
				if (is_array($selected) && in_array($entity->id, $selected))
				{
					$return.= 'selected="selected"';
				}
				$return.= '>';
				$return.= $entity->label;
				if (empty($entity->visible))
				{
					$return.= ' ('.$langs->trans('Hidden').')';
				}
				$return.= '</option>';
			}
		}
		$return.= '</select>';

		return $return;
	}

	/**
	 *    Switch to another entity.
	 *
	 *    @param int $id        User id
	 *    @param int $entity    Entity id
	 *    @return int
	 */
	public function checkRight($id, $entity)
	{
		global $conf;

		$this->getInstanceDao();

		if ($this->dao->fetch($entity) > 0)
		{
			// Controle des droits sur le changement
			if ($this->dao->verifyRight($entity, $id) || $user->admin)
			{
				return 1;
			}
			else
			{
				return -2;
			}
		}
		else
		{
			return -1;
		}
	}

	/**
	 *    Switch to another entity.
	 *    @param    int $id Id of the destination entity
	 *    @param    int $userid
	 *    @return int
	 */
	public function switchEntity($id, $userid=null)
	{
		global $conf, $user;

		$this->getInstanceDao();

		if (!empty($userid))
		{
			$user=new User($this->db);
			$user->fetch($userid);
		}

		if ($this->dao->fetch($id) > 0 && ! empty($this->dao->active)) // check if the entity is still active
		{
			// Controle des droits sur le changement
			if (!empty($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX)
			|| (!empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $this->dao->verifyRight($id, $user->id))
			|| $user->admin)
			{
				$_SESSION['dol_entity'] = $id;
				//$conf = new Conf(); FIXME some constants disappear
				$conf->entity = $id;
				$conf->setValues($this->db);
				return 1;
			}
			else
			{
				//var_dump($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX);
				//var_dump($conf->global->MULTICOMPANY_TRANSVERSE_MODE);
				//var_dump($this->dao->verifyRight($id, $user->id));
				return -2;
			}
		}
		else
		{
			return -1;
		}
	}

	/**
	 * 	Get entity info
	 * 	@param	int $id	Object id
	 */
	public function getInfo($id)
	{
		$this->getInstanceDao();
		$this->dao->fetch($id);

		$this->id				= $this->dao->id;
		$this->label			= $this->dao->label;
		$this->country_id		= $this->dao->country_id;
		$this->country_code		= $this->dao->country_code;
		$this->currency_code	= $this->dao->currency_code;
		$this->language_code	= $this->dao->language_code;
		$this->description		= $this->dao->description;
		$this->active			= $this->dao->active;
		$this->visible			= $this->dao->visible;
	}

	/**
	 *    Get action title
	 *    @param string $action Type of action
	 *    @return string
	 */
	public function getTitle($action='')
	{
		global $langs;

		if ($action == 'create') return $langs->trans("AddEntity");
		else if ($action == 'edit') return $langs->trans("EditEntity");
		else return $langs->trans("EntitiesManagement");
	}


	/**
	 *    Assigne les valeurs pour les templates
	 *    @param string $action     Type of action
	 */
	public function assign_values($action='view')
	{
		global $conf, $langs, $user;
		global $form, $formcompany, $formadmin;

		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

		$this->tpl['extrafields'] = new ExtraFields($this->db);
		// fetch optionals attributes and labels
		$this->tpl['extralabels'] = $this->tpl['extrafields']->fetch_name_optionals_label('entity');

		$this->getInstanceDao();

		$this->template_dir = dol_buildpath('/multicompany/admin/tpl/');

		if ($action == 'create' || $action == 'edit')
		{
			$this->template = 'card.tpl.php';

			if ($action == 'edit' && GETPOSTISSET('id')) {
				$ret = $this->dao->fetch(GETPOST('id', 'int'));
			}

			// action
			$this->tpl['action'] = $action;

			// id
			$this->tpl['id'] = (GETPOSTISSET('id')?GETPOST('id', 'int'):null);

			// Label
			$this->tpl['label'] = (GETPOSTISSET('label')?GETPOST('label', 'alpha'):$this->dao->label);

			// Description
			$this->tpl['description'] = (GETPOSTISSET('description')?GETPOST('description', 'alpha'):$this->dao->description);

			// Company name
			$this->tpl['name'] = (GETPOSTISSET('name')?GETPOST('name', 'alpha'):$this->dao->name);

			// Address
			$this->tpl['address'] = (GETPOSTISSET('address')?GETPOST('address', 'alpha'):$this->dao->address);

			// Zip
            $this->tpl['select_zip'] = $formcompany->select_ziptown((GETPOSTISSET('zipcode')?GETPOST('zipcode', 'alpha'):$this->dao->zip),'zipcode',array('town','selectcountry_id','departement_id'),6);

            // Town
            $this->tpl['select_town'] = $formcompany->select_ziptown((GETPOSTISSET('town')?GETPOST('town', 'alpha'):$this->dao->town),'town',array('zipcode','selectcountry_id','departement_id'),40);

            if ($user->admin) $this->tpl['info_admin'] = info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);


			// We define country_id
			if (GETPOSTISSET('country_id'))
			{
				$country_id = GETPOST('country_id', 'int');
			}
			else if (! empty($this->dao->country_id))
			{
				$country_id = $this->dao->country_id;
			}
			else if (! empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY))
			{
				$tmp = explode(':', $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
				$country_id = $tmp[0];
			}
			else
			{
				$country_id = 0;
			}

			$this->tpl['select_country']	= $form->select_country($country_id,'country_id');
			$this->tpl['select_state']		= $formcompany->select_state((GETPOSTISSET('departement_id')?GETPOST('departement_id', 'int'):$this->dao->state_id),$country_id,'departement_id');
			$this->tpl['select_currency']	= $form->selectCurrency((GETPOSTISSET('currency_code')?GETPOST('currency_code', 'alpha'):($this->dao->currency_code?$this->dao->currency_code:$conf->currency)),"currency_code");
			$this->tpl['select_language']	= $formadmin->select_language((GETPOSTISSET('main_lang_default')?GETPOST('main_lang_default', 'alpha'):($this->dao->language_code?$this->dao->language_code:$conf->global->MAIN_LANG_DEFAULT)),'main_lang_default',1);

			if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) {
				$this->tpl['select_entity']		= $this->select_entities($this->dao->options['referent'], 'referring_entity');
				foreach ($this->sharingelements as $element => $params) {
					$uppername = strtoupper($element);
					$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
					if (! empty($conf->global->$constname)) {
						$this->tpl['multiselect_from_' . $element]	= $this->multiselect_entities($element, $this->dao, false);
						$this->tpl['multiselect_to_' . $element]	= $this->multiselect_entities($element, $this->dao, true);
					}
				}
				if (! empty($conf->societe->enabled) && ! empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED)) {
					foreach ($this->sharingobjects as $element => $params) {
						if (! empty($params['active'])) {
							$uppername = strtoupper($element);
							$constname = 'MULTICOMPANY_' . $uppername . '_SHARING_ENABLED';
							if (! empty($conf->global->$constname)) {
								$this->tpl['multiselect_from_' . $element]	= $this->multiselect_entities($element, $this->dao, false);
								$this->tpl['multiselect_to_' . $element]	= $this->multiselect_entities($element, $this->dao, true);
							}
						}
					}
				}
			}
		}
		else
		{
			$this->template = 'list.tpl.php';
		}
	}

	/**
	 *    Display the template
	 */
	public function display()
	{
		global $conf, $langs;
		global $form, $object;

		include $this->template_dir.$this->template;
	}

	/**
	 * 	Set values of global conf for multicompany
	 *
	 * 	@param	Conf		$conf	Object conf
	 * 	@return void
	 */
	public function setValues(&$conf)
	{
		if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
		{
			$this->getInstanceDao();
			$this->dao->fetch($conf->entity);

			$this->sharings = $this->dao->options['sharings'];
			$this->referent = $this->dao->options['referent'];

			// Load shared elements
			$this->loadSharedElements();

			// Define output dir for others entities
			$this->setMultiOutputDir($conf);
		}

		if (! empty($this->sharingdicts))
		{
			foreach($this->sharingdicts as $dict => $data)
			{
				$constname = 'MULTICOMPANY_'.strtoupper($dict).'_SHARING_DISABLED';
				if (! empty($conf->global->$constname)) {
					$this->dict[$dict] = true;
				}
			}
		}
	}

	/**
	 *	Set status of an entity
	 *
	 *	@param	int		$id			Id of entity
	 *	@param	string	$type		Type of status (visible or active)
	 *	@param	string	$value		Value of status (0: disable, 1: enable)
	 *	@return	int
	 */
	public function setStatus($id, $type='active', $value)
	{
		global $user;

		if (! empty($user->admin) && ! $user->entity) {
			$this->getInstanceDao();
			return $this->dao->setEntity($id, $type, $value);
		}
		else {
			return -1;
		}
	}

	/**
	 *	Delete an entity
	 *
	 *	@param	int	$id		Id of entity
	 *	@return	int
	 */
	public function deleteEntity($id)
	{
		global $user;

		if (! empty($user->admin) && ! $user->entity && $id != 1) {
			$this->getInstanceDao();
			return $this->dao->delete($id);
		}
		else {
			return -1;
		}
	}

	/**
	 * 	Get entity to use
	 *
	 * 	@param	string	$element			Current element
	 * 	@param	int		$shared			0=Return id of current entity only,
	 * 									1=Return id of current entity + shared entities (default)
	 *  @param	int		$forceentity		Entity id to force
	 * 	@return	int						Entity id to use
	 */
	public function getEntity($element=false, $shared=1, $forceentity=null)
	{
		global $conf;

		$element = str_replace(MAIN_DB_PREFIX, '', $element);

		if (in_array($element, $this->addzero))
		{
			if (($element == 'user' || $element == 'usergroup') && ! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
				return '0,1';				// In transverse mode all users except superadmin and groups are in entity 1
			} else {
				return '0,'.$conf->entity;
			}
		}

		// Sharing dictionnaries
		if (array_key_exists($element, $this->sharingdicts))
		{
			if (! empty($this->dict[$element])) {
				return $conf->entity;
			}
			else {
				return 1; // Master entity
			}
		}

		$elementkey = $element;
		if ($element == 'societe' || $element == 'socpeople' || $element == 'contact') {
			$elementkey = 'thirdparty';
		}
		if ($element == 'adherent')			$elementkey = 'member';
		if ($element == 'bank_account')		$elementkey = 'bankaccount';
		if ($element == 'adherent_type')	$elementkey = 'member_type';
		if ($element == 'categorie')		$elementkey	= 'category';
		if ($element == 'propal')			$elementkey = 'proposal';
		if ($element == 'facture')			$elementkey = 'invoice';

		if (! empty($element) && ! empty($this->entities[$elementkey]))
		{
			if (! empty($shared))
			{
				return $this->entities[$elementkey];
			}
			else if (! empty($this->sharings['referent']))
			{
				if ($element == 'societe') return $this->sharings['referent'];
			}
		}

		return $conf->entity;
	}

	/**
	 * 	Get entities list
	 *
	 * 	@return	array		Array of entities (id => label)
	 */
	public function getEntitiesList()
	{
		global $langs;

		$this->getInstanceDao();
		$this->dao->getEntities();

		$entities=array();

		foreach ($this->dao->entities as $entity)
		{
			$entities[$entity->id] = $entity->label . (empty($entity->active) ? ' ('.$langs->trans('Disabled').')' : (empty($entity->visible) ? ' ('.$langs->trans('Hidden').')' : '') );
		}

		return $entities;
	}

	/**
	 * 	Set object documents directory to use
	 *
	 *	@param	Conf	$conf		Object Conf
	 * 	@return	void
	 */
	public function setMultiOutputDir(&$conf)
	{
		if (! empty($this->entities))
		{
			foreach($this->entities as $element => $shares)
			{
				if ($element == 'thirdparty')	$element = 'societe';
				if ($element == 'member')		$element = 'adherent';
				if ($element == 'proposal')		$element = 'propal';
				if ($element == 'intervention')	$element = 'ficheinter';

				if (!empty($conf->$element->enabled) && isset($conf->$element->multidir_output) && isset($conf->$element->multidir_temp))
				{
					$elementpath=$element;
					if ($element == 'product')	$elementpath='produit';
					if ($element == 'category')	$elementpath='categorie';
					if ($element == 'propal')	$elementpath='propale';

					$entities = explode(",", $shares);
					$dir_output = array();
					$dir_temp = array();
					foreach($entities as $entity)
					{
						if (!array_key_exists($entity, $conf->$element->multidir_output))
						{
							$path = ($entity > 1 ? "/".$entity : '');

							$dir_output[$entity] 	= DOL_DATA_ROOT.$path."/".$elementpath;
							$dir_temp[$entity] 		= DOL_DATA_ROOT.$path."/".$elementpath."/temp";

							$conf->$element->multidir_output += $dir_output;
							$conf->$element->multidir_temp += $dir_temp;
						}
					}
				}
			}
		}
	}

	/**
	 * @param bool $parameters
	 * @return int
	 */
	public function printTopRightMenu($parameters=false)
	{
		echo $this->getTopRightMenu();

		return 0;
	}

	/**
	 * @param bool $parameters
	 * @return int
	 */
	/*public function afterLogin($parameters=false)
	{
		global $conf;

		return 0;
	}*/

	/**
	 * @param bool $parameters
	 * @return int
	 */
	public function updateSession($parameters=false)
	{
		global $conf;

		// Switch to another entity
		if (! empty($conf->multicompany->enabled) && GETPOST('action','aZ') == 'switchentity')
		{
			if ($this->switchEntity(GETPOST('entity','int')) > 0)
			{
				header("Location: ".DOL_URL_ROOT.'/');
				exit;
			}
		}

		return 0;
	}

	/**
	 *
	 */
	public function getLoginPageOptions($parameters=false)
	{
		global $conf, $langs;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		if (empty($entity)) $entity=1;
		$lastentity=(! empty($conf->global->MULTICOMPANY_FORCE_ENTITY)?$conf->global->MULTICOMPANY_FORCE_ENTITY:$entity);

		// Entity combobox
		if (empty($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX))
		{
			$select_entity = $this->select_entities($lastentity, 'entity', ' tabindex="3"', true, false, false, false, '', 'minwidth100imp');
/*
			$divformat = '<div class="entityBox">';
			$divformat.= $select_entity;
			$divformat.= '</div>';

			$this->results['options']['div'] = $divformat;
*/
			$tableformat = '<tr id="entity_box"><td class="nowrap center valignmiddle">';
			$tableformat.= '<span class="fa fa-globe">'.$select_entity.'</span>';
			//$tableformat.= '<div class="icon-multicompany-black">'.$select_entity.'</div>';
			$tableformat.= '</td></tr>';

			//$this->results['options']['table'] = $tableformat;
			$this->resprints = $tableformat;
		}

		return 0;
	}

	/**
	 *
	 */
	public function getPasswordForgottenPageOptions($parameters=false)
	{
		return $this->getLoginPageOptions($parameters);
	}

	/**
	 * Add all entities default dictionnaries in database
	 */
	public function addAllEntitiesDefaultDicts()
	{
		if (! empty($this->sharingdicts))
		{
			$this->getInstanceDao();
			$this->dao->getEntities();

			$dir	= "/multicompany/sql/dict/";

			foreach($this->sharingdicts as $dict => $data)
			{
				// Load sql init_new_entity_dict.sql file
				$file 	= 'init_new_entity_'.$dict.'.sql';
				$fullpath = dol_buildpath($dir.$file);

				if (file_exists($fullpath))
				{
					foreach ($this->dao->entities as $entity)
					{
						if ($entity->id == 1) continue;

						$result=run_sql($fullpath,1,$entity->id);
					}
				}
			}
		}
	}

	/**
	 *  Load shared elements
	 *
	 *  @return void
	 */
	private function loadSharedElements()
	{
		global $conf;

		if (! empty($this->sharings))
		{
			$this->getInstanceDao();

			foreach($this->sharings as $element => $ids)
			{
				$moduleSharingEnabled = 'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED';
				$module = $element;

				if ($element == 'thirdparty') {
					$module = 'societe';
				} else if ($element == 'productprice' || $element == 'productsupplierprice') {
					$module = 'product';
				} else if ($element == 'product' && empty($conf->product->enabled) && !empty($conf->service->enabled)) {
					$module = 'service';
				} else if ($element == 'invoicenumber') {
					$module = 'facture';
				} else if ($element == 'project') {
					$module = 'projet';
				} else if ($element == 'member' || $element == 'member_type') {
					$module = 'adherent';
				} else if ($element == 'intervention') {
					$module = 'ficheinter';
				} else if ($element == 'category') {
					$module = 'categorie';
				} else if ($element == 'bank_account' || $element == 'bankaccount') {
					$module = 'banque';
				}

				if (array_key_exists($element, $this->sharingobjects))
				{
					$module = ((isset($this->sharingobjects[$element]['element']) && !empty($this->sharingobjects[$element]['element'])) ? $this->sharingobjects[$element]['element'] : $element);
				}

				if (! empty($conf->$module->enabled) && ! empty($conf->global->$moduleSharingEnabled))
				{
					$entities=array();

					if (! empty($this->referent))
					{
						// Load configuration of referent entity
						$this->config = $this->dao->getEntityConfig($this->referent);
						$this->setConstant($conf, $element);
					}

					if (! empty($ids))
					{
						foreach ($ids as $id)
						{
							$ret=$this->dao->fetch($id);
							if ($ret > 0 && $this->dao->active)
							{
								$entities[] = $id;
							}
						}

						$this->entities[$element] = (! empty($entities) ? implode(",", $entities) : 0);
						$this->entities[$element].= ','.$conf->entity;
					}
				}
			}
		}
		//var_dump($this->entities);
	}

	/**
	 * 	Get modify entity dialog
	 */
	private function getModifyEntityDialog($htmlname, $action, $object)
	{
		global $langs;

		$langs->loadLangs(array('errors','multicompany@multicompany'));

		$out = '<!-- BEGIN MULTICOMPANY AJAX TEMPLATE -->';

		$out.= '
			<script type="text/javascript">
			$(document).ready(function() {
				$( "#modify-entity-'.$htmlname.'" ).click(function() {
					$( "#dialog-modify-'.$htmlname.'" ).dialog({
						modal: true,
						resizable: false,
						width: 400,
						height: 200,
						open: function() {
							$(".ui-dialog-buttonset > button:last").focus();
						},
						buttons: {
							\''.$langs->trans('Validate').'\': function() {
								$.get( "'.dol_buildpath('/multicompany/core/ajax/functions.php',1).'", {
									action: \''.$action.'\',
									element: \''.$object->element.'\',
									fk_element: \''.$object->id.'\',
									id: $( "#entity'.$htmlname.'" ).val()
								},
								function(result) {
									if (result.status == "success") {
										$.jnotify("'.$langs->trans(ucfirst($htmlname) . "ModifyEntitySuccess").'", "ok");
										$( "#dialog-modify-'.$htmlname.'" ).dialog( "close" );
										window.location.href = "'.$_SERVER["PHP_SELF"].'?'.($htmlname == 'thirdparty'?'socid':'id').'='.$object->id.'";
									} else {
										$.jnotify("'.$langs->trans("Error" . ucfirst($htmlname) . "ModifyEntity").'", "error", true);
										if (result.error) {
											if (result.error == "ErrorCustomerCodeAlreadyUsed") {
												$.jnotify("'.$langs->trans("ErrorCustomerCodeAlreadyUsed").'", "error", true);
											}
										}
									}
								});
							},
							\''.$langs->trans('Cancel').'\': function() {
								$(this).dialog( "close" );
							}
						}
					});
				});
			});
			</script>';

		$out.= '<div id="dialog-modify-' . $htmlname . '" class="hideobject" title="' . $langs->trans(ucfirst($htmlname) . 'ModifyEntity') . '">'."\n";
		$out.= '<p>' . img_warning() . ' ' . $langs->trans(ucfirst($htmlname) . 'ModifyEntityDescription') . '</p>'."\n";
		$out.= '<div>' . $langs->trans('SelectAnEntity') . ' : ';
		$out.= $this->select_entities('', 'entity' . $htmlname, '', false, array($object->entity)) . '</div>'."\n";
		$out.= '</div>'."\n";

		$out.= '<!-- END MULTICOMPANY AJAX TEMPLATE -->';

		return $out;
	}

	/**
	 * 	Show entity info
	 */
	private function getTopRightMenu()
	{
		global $conf,$user,$langs;

		$langs->loadLangs(array('languages','admin','multicompany@multicompany'));

		$out='';

		if (!empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) || !empty($user->admin))
		{
			$form=new Form($this->db);

			$this->getInfo($conf->entity);

			$text ='<span class="fa fa-globe atoplogin switchentity"></span>';

			if ($cache = getCache('country_' . $this->country_id)) {
				$country = $cache;
			} else {
				$country = getCountry($this->country_id);
				setCache('country_' . $this->country_id, $country);
			}
			$imgCountry=picto_from_langcode($this->country_code, 'class="multicompany-flag-country"');

			$imgLang=picto_from_langcode($this->language_code, 'class="multicompany-flag-language"');

			$htmltext ='<u>'.$langs->trans("Entity").'</u>'."\n";
			$htmltext.='<br><b>'.$langs->trans("Label").'</b>: '.$this->label."\n";
			$htmltext.='<br><b>'.$langs->trans("Country").'</b>: '. ($imgCountry?$imgCountry.' ':'') . $country."\n";
			$htmltext.='<br><b>'.$langs->trans("Currency").'</b>: '. currency_name($this->currency_code) . ' (' . $langs->getCurrencySymbol($this->currency_code) . ')'."\n";
			$htmltext.='<br><b>'.$langs->trans("Language").'</b>: '. ($imgLang?$imgLang.' ':'') . ($this->language_code=='auto'?$langs->trans("AutoDetectLang"):$langs->trans("Language_".$this->language_code));
			if (! empty($this->description)) $htmltext.='<br><b>'.$langs->trans("Description").'</b>: '.$this->description."\n";

			$out.= $form->textwithtooltip('',$htmltext,2,1,$text,'login_block_elem multicompany_block',2);

			$out.= '
			<script type="text/javascript">
			$(document).ready(function() {
				$( ".switchentity" ).click(function() {
					$( "#dialog-switchentity" ).dialog({
						modal: true,
						width: '.($conf->dol_optimize_smallscreen ? 300 : 400).',
						buttons: {
							\''.$langs->trans('Ok').'\': function() {
								$.get( "'.dol_buildpath('/multicompany/core/ajax/functions.php',1).'", {
									action: \'switchEntity\',
									id: $( "#changeentity" ).val()
								},
								function(content) {
									$( "#dialog-switchentity" ).dialog( "close" );
									var url = window.location.pathname;
									var queryString = window.location.href.split("?")[1];
									if (queryString) {
										var params = parseQueryString(queryString);
										delete params.action;
										url = url + "?" + jQuery.param(params);
									}
									location.href=url;
								});
							},
							\''.$langs->trans('Cancel').'\': function() {
								$(this).dialog( "close" );
							}
						}
					});
				});
				var parseQueryString = function( queryString ) {
					var params = {}, queries, temp, i, l;
					// Split into key/value pairs
					queries = queryString.split("&");
					// Convert the array of strings into an object
					for ( i = 0, l = queries.length; i < l; i++ ) {
						temp = queries[i].split("=");
						params[temp[0]] = temp[1];
					}
					return params;
				};
			';
			if (GETPOST('switchentityautoopen','int'))
			{
				$out.='$( "#switchentity" ).click();'."\n";
			}
			$out.= '
			});
			</script>';

			$out.= '<div id="dialog-switchentity" class="hideobject" title="'.$langs->trans('SwitchToAnotherEntity').'">'."\n";
			$out.= '<br>'.$langs->trans('SelectAnEntity').': ';
			$out.= $this->select_entities($conf->entity, 'changeentity')."\n";
			$out.= '</div>'."\n";
		}

		if (!checkMultiCompanyVersion())
		{
			$msg = get_htmloutput_mesg(img_warning('default') . ' ' . $langs->trans("MultiCompanyUpgradeIsNeeded"), '', 'mc-upgrade-alert', 1);
			$out.= '
			<script type="text/javascript">
			$(document).ready(function() {
				$( "#id-right .fiche" ).before( \'' . $msg . '\' );
			});
			</script>';
		}

		$this->resprints = $out;
	}

	/**
	 *	Set parameters with referent entity
	 *
	 * @param Conf $conf
	 * @param string $element
	 */
	public function setConstant(&$conf, $element)
	{
		if (! empty($this->config))
		{
			$constants=array();

			if ($element == 'thirdparty')
			{
				$constants = array(
						'SOCIETE_CODECLIENT_ADDON',
						'COMPANY_ELEPHANT_MASK_CUSTOMER',
						'COMPANY_ELEPHANT_MASK_SUPPLIER',
						'SOCIETE_IDPROF1_UNIQUE',
						'SOCIETE_IDPROF2_UNIQUE',
						'SOCIETE_IDPROF3_UNIQUE',
						'SOCIETE_IDPROF4_UNIQUE'
				);
			}

			if (! empty($constants))
			{
				foreach($constants as $name)
				{
					if (! empty($this->config[$name])) $conf->global->$name = $this->config[$name];
				}
			}
		}
	}

}
