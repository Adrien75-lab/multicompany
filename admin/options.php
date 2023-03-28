<?php
/* Copyright (C) 2011-2018	Regis Houssin	<regis.houssin@inodbox.com>
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
 *  \file       multicompany/admin/parameters.php
 *  \ingroup    multicompany
 *  \brief      Page d'administration/configuration du module Multi-Company
 */

$res=@include("../../main.inc.php");						// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");			// For "custom" directory

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
dol_include_once('/multicompany/class/actions_multicompany.class.php', 'ActionsMulticompany');

$langs->loadLangs(array('admin', 'multicompany@multicompany'));

// Security check
if (empty($user->admin) || ! empty($user->entity)) {
	accessforbidden();
}

$action=GETPOST('action','alpha');

$object = New ActionsMulticompany($db);


/*
 * Action
 */


/*
 * View
 */

$extrajs = array(
	'/multicompany/core/js/lib_head.js'
);

$help_url='EN:Module_MultiCompany|FR:Module_MultiSoci&eacute;t&eacute;';
llxHeader('', $langs->trans("MultiCompanySetup"), $help_url,'','','',$extrajs);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("MultiCompanySetup"),$linkback,'multicompany@multicompany',0,'multicompany_title');

$head = multicompany_prepare_head();
dol_fiche_head($head, 'options', $langs->trans("ModuleSetup"), -1);

$form=new Form($db);

$hidden=true;
$checkconfig = checkMulticompanyAutentication();
if ($checkconfig !== true) {
	if (! empty($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX)) {
		$hidden=false;
	}
	print '<div id="mc_hide_login_combobox_error"'.($hidden ? ' style="display:none;"' : '').'>'.get_htmloutput_mesg($langs->trans("ErrorMulticompanyConfAuthentication"),'','error',1).'</div>';
} else {
	if (empty($conf->global->MULTICOMPANY_HIDE_LOGIN_COMBOBOX)) {
		$hidden=false;
	}
	print '<div id="dol_hide_login_combobox_error"'.($hidden ? ' style="display:none;"' : '').'>'.get_htmloutput_mesg($langs->trans("ErrorDolibarrConfAuthentication"),'','error',1).'</div>';
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

/*
 * Formulaire parametres divers
 */

// Login page combobox activation
print '<tr class="oddeven">';
print '<td><span class="fa fa-cogs"></span><span class="multiselect-title">'.$langs->trans("HideLoginCombobox").'</span></td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($checkconfig !== true) {
	$input = array(
		'showhide' => array(
			'#mc_hide_login_combobox_error'
		)
	);
} else {
	$input = array(
		'hideshow' => array(
			'#dol_hide_login_combobox_error'
		)
	);
}
print ajax_mcconstantonoff('MULTICOMPANY_HIDE_LOGIN_COMBOBOX', $input, 0);
print '</td></tr>';

// Active by default during create
print '<tr class="oddeven">';
print '<td><span class="fa fa-cogs"></span><span class="multiselect-title">'.$langs->trans("EntityActiveByDefault").'</span></td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
$input = array(
	'showhide' => array(
		'#visiblebydefault'
	),
	'del' => array(
		'MULTICOMPANY_VISIBLE_BY_DEFAULT'
	)
);
print ajax_mcconstantonoff('MULTICOMPANY_ACTIVE_BY_DEFAULT', $input, 0);
print '</td></tr>';

// Visible by default during create
print '<tr id="visiblebydefault" class="oddeven"'.(empty($conf->global->MULTICOMPANY_ACTIVE_BY_DEFAULT) ? ' style="display:none;"' : '').'>';
print '<td><span class="fa fa-cogs"></span><span class="multiselect-title">'.$langs->trans("EntityVisibleByDefault").'</span></td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_mcconstantonoff('MULTICOMPANY_VISIBLE_BY_DEFAULT', '', 0);
print '</td></tr>';

/* Mode de gestion des droits :
 * Mode Off : mode Off : pyramidale. Les droits et les groupes sont gérés dans chaque entité : les utilisateurs appartiennent au groupe de l'entity pour obtenir leurs droits
 * Mode On : mode On : transversale : Les groupes ne peuvent appartenir qu'a l'entity = 0 et c'est l'utilisateur qui appartient à tel ou tel entity
 */

$text = img_picto('', 'info','class="linkobject"');
$htmltext = $langs->trans("GroupModeTransversalInfoFull");

print '<tr class="oddeven">';
print '<td><span class="fa fa-cogs"></span><span class="multiselect-title">'.$langs->trans("GroupModeTransversal").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</span></td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
$input = array(
	'alert' => array(
		'set' => array(
			'info' => true,
			'height' => 200,
			'yesButton' => $langs->trans('Ok'),
			'title' => $langs->transnoentities('GroupModeTransversalTitle'),
			'content' => img_warning().' '.$langs->trans('GroupModeTransversalInfo')
		)
	)
);
print ajax_mcconstantonoff('MULTICOMPANY_TRANSVERSE_MODE', $input, 0);
print '</td></tr>';

// Enable global sharings
if (! empty($conf->societe->enabled)
	|| ! empty($conf->product->enabled)
	|| ! empty($conf->service->enabled)
	|| ! empty($conf->categorie->enabled)
	|| ! empty($conf->adherent->enabled)
	|| ! empty($conf->agenda->enabled))
{
	print '<tr class="oddeven">';
	print '<td><span class="fa fa-cogs"></span><span class="multiselect-title">'.$langs->trans("EnableGlobalSharings").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	$input = array(
		'alert' => array(
			'set' => array(
				'info' => true,
				'yesButton' => $langs->trans('Ok'),
				'title' => $langs->transnoentities('GlobalSharings'),
				'content' => img_warning().' '.$langs->trans('GlobalSharingsInfo')
			)
		),
		'showhide' => array(
			'#sharetitle',
			'#shareproduct',
			'#sharethirdparty',
			'#sharecategory',
			'#sharebank',
			'#shareexpensereport',
			'#shareholiday',
			'#shareproject',
			'#sharemember'
		),
		'hide' => array(
			'#sharetitle',
			'#shareinvoicenumber',
			'#shareproduct',
			'#shareproductprice',
			'#shareproductsupplierprice',
			'#sharestock',
			'#sharethirdparty',
			'#shareagenda',
			'#sharecategory',
			'#sharebank',
			'#shareexpensereport',
			'#shareholiday',
			'#shareproject',
			'#sharemember',
			'#sharemembertype',
			'#objectsharetitle'
		),
		'del' => array(
			'MULTICOMPANY_INVOICENUMBER_SHARING_ENABLED',
			'MULTICOMPANY_PRODUCT_SHARING_ENABLED',
			'MULTICOMPANY_PRODUCTPRICE_SHARING_ENABLED',
			'MULTICOMPANY_PRODUCTSUPPLIERPRICE_SHARING_ENABLED',
			'MULTICOMPANY_STOCK_SHARING_ENABLED',
			'MULTICOMPANY_THIRDPARTY_SHARING_ENABLED',
			'MULTICOMPANY_AGENDA_SHARING_ENABLED',
			'MULTICOMPANY_CATEGORY_SHARING_ENABLED',
			'MULTICOMPANY_BANKACCOUNT_SHARING_ENABLED',
			'MULTICOMPANY_EXPENSEREPORT_SHARING_ENABLED',
			'MULTICOMPANY_HOLIDAY_SHARING_ENABLED',
			'MULTICOMPANY_PROJECT_SHARING_ENABLED',
			'MULTICOMPANY_MEMBER_SHARING_ENABLED',
			'MULTICOMPANY_MEMBER_TYPE_SHARING_ENABLED'
		)
	);
	if (! empty($object->sharingobjects))
	{
		foreach ($object->sharingobjects as $element => $params)
		{
			if (! empty($params['active'])) {
				array_push($input['hide'], '#share'.$element);
				array_push($input['del'], 'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED');
			}
		}
	}
	print ajax_mcconstantonoff('MULTICOMPANY_SHARINGS_ENABLED', $input, 0);
	print '</td></tr>';
}

// Share invoices number incrementation between entities
if (! empty($conf->facture->enabled) && ! empty($conf->societe->enabled))
{
	$text = img_picto('', 'info','class="linkobject"');
	$htmltext = $langs->trans("InvoiceNumberSharingInfo");

	$display=(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) || empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) ? ' style="display:none;"' : '');
	print '<tr id="shareinvoicenumber" class="oddeven"'.$display.'>';
	print '<td><span class="fa fa-'.$object->sharingelements['invoicenumber']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareInvoicesNumber").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_INVOICENUMBER_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

$text = img_picto('', 'info','class="linkobject"');
$htmltext = $langs->trans("GlobalSharingsInfo");

print '<tr class="liste_titre" id="sharetitle"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
print '<td>'.$langs->trans("ActivatingShares").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Share thirparties and contacts
if (! empty($conf->societe->enabled))
{
	print '<tr id="sharethirdparty" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['thirdparty']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareThirdpartiesAndContacts").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	$input = array(
		'showhide' => array(
			'#shareinvoicenumber',
			'#objectsharetitle'
		),
		'del' => array(
			'MULTICOMPANY_INVOICENUMBER_SHARING_ENABLED'
		)
	);
	if (! empty($object->sharingobjects))
	{
		foreach ($object->sharingobjects as $element => $params)
		{
			if (! empty($params['active'])) {
				array_push($input['showhide'], '#share'.$element);
				array_push($input['del'], 'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED');
			}
		}
	}
	print ajax_mcconstantonoff('MULTICOMPANY_THIRDPARTY_SHARING_ENABLED', $input, 0);
	print '</td></tr>';
}

// Share agendas
if (! empty($conf->agenda->enabled))
{
	$display=(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '');
	print '<tr id="shareagenda" class="oddeven"'.$display.'>';
	print '<td><span class="fa fa-'.$object->sharingelements['agenda']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareAgenda").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_AGENDA_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share products/services
if (! empty($conf->product->enabled) || ! empty($conf->service->enabled))
{
	$text = img_picto('', 'info','class="linkobject"');
	$htmltext = $langs->trans("ProductSharingInfo");

	print '<tr id="shareproduct" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['product']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareProductsAndServices").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	$input = array(
		'showhide' => array(
			'#shareproductprice',
			'#shareproductsupplierprice',
			'#sharestock'
		),
		'del' => array(
			'MULTICOMPANY_PRODUCTPRICE_SHARING_ENABLED',
			'MULTICOMPANY_PRODUCTSUPPLIERPRICE_SHARING_ENABLED',
			'MULTICOMPANY_STOCK_SHARING_ENABLED'
		)
	);
	print ajax_mcconstantonoff('MULTICOMPANY_PRODUCT_SHARING_ENABLED', $input, 0);
	print '</td></tr>';

	$text = img_picto('', 'info','class="linkobject"');
	$htmltext = $langs->trans("ProductPriceSharingInfo");

	print '<tr id="shareproductprice" class="oddeven"'.(empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['productprice']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareProductsAndServicesPrices").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_PRODUCTPRICE_SHARING_ENABLED', '', 0);
	print '</td></tr>';

	if (! empty($conf->fournisseur->enabled))
	{
		print '<tr id="shareproductsupplierprice" class="oddeven"'.(empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) ? ' style="display:none;"' : '').'>';
		print '<td><span class="fa fa-'.$object->sharingelements['productsupplierprice']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareProductsServicesSupplierPrices").'</span></td>';
		print '<td align="center" width="20">&nbsp;</td>';

		print '<td align="center" width="100">';
		print ajax_mcconstantonoff('MULTICOMPANY_PRODUCTSUPPLIERPRICE_SHARING_ENABLED', '', 0);
		print '</td></tr>';
	}
}

// Share stocks
if (! empty($conf->stock->enabled) && (! empty($conf->product->enabled) || ! empty($conf->service->enabled)))
{
	$display=(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) || empty($conf->global->MULTICOMPANY_PRODUCT_SHARING_ENABLED) ? ' style="display:none;"' : '');
	print '<tr id="sharestock" class="oddeven"'.$display.'>';
	print '<td><span class="fa fa-'.$object->sharingelements['stock']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareStock").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_STOCK_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share categories
if (! empty($conf->categorie->enabled))
{
	print '<tr id="sharecategory" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['category']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareCategories").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_CATEGORY_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share banks
if (! empty($conf->banque->enabled))
{
	print '<tr id="sharebank" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['bankaccount']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareBank").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_BANKACCOUNT_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share expenses reports
if (! empty($conf->expensereport->enabled))
{
	print '<tr id="shareexpensereport" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['expensereport']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareExpenseReport").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_EXPENSEREPORT_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share holidays
if (! empty($conf->holiday->enabled))
{
	$var=!$var;
	print '<tr id="shareholiday" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['holiday']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareHoliday").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_HOLIDAY_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

//share projects
if (! empty($conf->projet->enabled))
{
	print '<tr id="shareproject" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['project']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareProject").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_PROJECT_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Share members
if (! empty($conf->adherent->enabled))
{
	print '<tr id="sharemember" class="oddeven"'.(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) ? ' style="display:none;"' : '').'>';
	print '<td><span class="fa fa-'.$object->sharingelements['member']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareMembers").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	$input = array(
		'showhide' => array(
			'#sharemembertype'
		),
		'del' => array(
			'MULTICOMPANY_MEMBER_TYPE_SHARING_ENABLED'
		)
	);
	print ajax_mcconstantonoff('MULTICOMPANY_MEMBER_SHARING_ENABLED', $input, 0);
	print '</td></tr>';

	// Share member type
	$display=(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) || empty($conf->global->MULTICOMPANY_MEMBER_SHARING_ENABLED) ? ' style="display:none;"' : '');
	print '<tr id="sharemembertype" class="oddeven"'.$display.'>';
	print '<td><span class="fa fa-'.$object->sharingelements['member_type']['icon'].'"></span><span class="multiselect-title">'.$langs->trans("ShareMembersType").'</span></td>';
	print '<td align="center" width="20">&nbsp;</td>';

	print '<td align="center" width="100">';
	print ajax_mcconstantonoff('MULTICOMPANY_MEMBER_TYPE_SHARING_ENABLED', '', 0);
	print '</td></tr>';
}

// Objects sharings
if (! empty($object->sharingobjects))
{
	$text = img_picto('', 'info','class="linkobject"');
	$htmltext = $langs->trans("ObjectSharingsInfo");
	$display=(empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED) || empty($conf->global->MULTICOMPANY_THIRDPARTY_SHARING_ENABLED) ? ' style="display:none;"' : '');
	print '<tr class="liste_titre" id="objectsharetitle"'.$display.'>';
	print '<td>'.$langs->trans("ActivatingObjectShares").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</td>'."\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
	print '</tr>';

	foreach ($object->sharingobjects as $element => $params)
	{
		if (! empty($params['active'])) {
			$icon = (! empty($params['icon'])?$params['icon']:'edit');
			$module = ((isset($params['element']) && !empty($params['element'])) ? $params['element'] : $element);
			if (! empty($conf->$module->enabled))
			{
				print '<tr id="share'.$element.'" class="oddeven"'.$display.'>';
				print '<td><span class="fa fa-'.$icon.'"></span><span class="multiselect-title">'.$langs->trans("Share".ucfirst($element)).'</span></td>';
				print '<td align="center" width="20">&nbsp;</td>';

				print '<td align="center" width="100">';
				print ajax_mcconstantonoff('MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED', '', 0);
				print '</td></tr>';
			}
		}
	}
}

// Dictionnaries
if (1==2 && ! empty($object->sharingdicts))
{
	$text = img_picto('', 'info','class="linkobject"');
	$htmltext = $langs->trans("DictsSharingsInfo");

	print '<tr class="liste_titre" id="dictsharetitle">';
	print '<td>'.$langs->trans("ActivatingDictsShares").' '.$form->textwithtooltip('',$htmltext,2,1,$text).'</td>'."\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
	print '</tr>';

	foreach ($object->sharingdicts as $dict => $data)
	{
		print '<tr id="share'.$dict.'" class="oddeven">';
		print '<td>'.$langs->trans("Share".ucfirst($dict)).'</td>';
		print '<td align="center" width="20">&nbsp;</td>';

		print '<td align="center" width="100">';
		print ajax_mcconstantonoff('MULTICOMPANY_'.strtoupper($dict).'_SHARING_DISABLED', '', 0);
		print '</td></tr>';
	}
}

print '</table>';

// Card end
dol_fiche_end();
// Footer
llxFooter();
// Close database handler
$db->close();
