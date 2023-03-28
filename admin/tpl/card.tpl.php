<?php
/* Copyright (C) 2009-2018	Regis Houssin	<regis.houssin@inodbox.com>
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
 *
 */
?>

<!-- BEGIN PHP TEMPLATE -->
<?php if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) { ?>
<!-- Isolate Boostrap for avoid conflicts -->
<script type="text/javascript" src="<?php dol_buildpath('/multicompany/inc/multiselect/js/bootstrap.min.js', 1); ?>"></script>
<?php } ?>
<script type="text/javascript">
$(document).ready(function () {
	$("#selectcountry_id").change(function() {
		document.form_entity.action.value="<?php echo $this->tpl['action']; ?>";
		document.form_entity.submit();
	});
<?php
if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) {
	foreach($this->sharingelements as $element => $params) {
		if (! empty($this->tpl['multiselect_from_' . $element])) {
?>
	$('#multiselect_shared_<?php echo $element; ?>').multiselect({
		keepRenderingSort: true,
		right: '#multiselect_to_<?php echo $element; ?>',
	    rightAll: '#multiselect_shared_<?php echo $element; ?>_leftAll',
	    rightSelected: '#multiselect_shared_<?php echo $element; ?>_leftSelected',
	    leftSelected: '#multiselect_shared_<?php echo $element; ?>_rightSelected',
	    leftAll: '#multiselect_shared_<?php echo $element; ?>_rightAll',
	    search: {
            left: '<input type="text" name="q" class="form-control" placeholder="<?php echo $langs->trans("Search").'...'; ?>" />',
            right: '<input type="text" name="q" class="form-control" placeholder="<?php echo $langs->trans("Search").'...'; ?>" />',
        },
        fireSearch: function(value) {
            return value.length > 2;
        },
	    <?php if ($element == 'thirdparty') { ?>
	    afterMoveToLeft: function($left, $right, $options) {
	    	var sharingobjects = <?php echo json_encode($object->sharingobjects); ?>;
	    	$.each(sharingobjects, function( element, param ) {
				if (param.active) {
					var elements = $('#multiselect_shared_' + element + '_to option');
					if (elements && elements.length) {
						$.each(elements, function( key, share) {
							$.each($options, function( index, entity ) {
								if (entity.value == share.value) {
									$('#multiselect_shared_' + element + '_to option[value=' + entity.value + ']').remove();
									$('#multiselect_shared_' + element).append($("<option></option>").attr("value", entity.value).text(entity.text));
									$('#multiselect_shared_' + element).html($('#multiselect_shared_' + element + ' option').sort(function(x, y) {
							            return $(x).val() < $(y).val() ? -1 : 1;
							        }));
								}
							});
						});
					}
				}
			});
		},
		afterMoveToRight: function($left, $right, $options) {
			$('#multiselect_shared_<?php echo $element; ?>_to').html($('#multiselect_shared_<?php echo $element; ?>_to option').sort(function(x, y) {
	            return $(x).val() < $(y).val() ? -1 : 1;
	        }));
		}
		<?php } ?>
	});
<?php } } ?>
<?php
	foreach($this->sharingobjects as $element => $params) {
		if (! empty($this->tpl['multiselect_from_' . $element])) {
?>
	$('#multiselect_shared_<?php echo $element; ?>').multiselect({
		keepRenderingSort: true,
		right: '#multiselect_to_<?php echo $element; ?>',
        rightAll: '#multiselect_shared_<?php echo $element; ?>_leftAll',
        rightSelected: '#multiselect_shared_<?php echo $element; ?>_leftSelected',
        leftSelected: '#multiselect_shared_<?php echo $element; ?>_rightSelected',
        leftAll: '#multiselect_shared_<?php echo $element; ?>_rightAll',
        search: {
            left: '<input type="text" name="q" class="form-control" placeholder="<?php echo $langs->trans("Search").'...'; ?>" />',
            right: '<input type="text" name="q" class="form-control" placeholder="<?php echo $langs->trans("Search").'...'; ?>" />',
        },
        fireSearch: function(value) {
            return value.length > 2;
        },
        afterMoveToRight: function($left, $right, $options) {
        	var elements = $('#multiselect_shared_thirdparty option');
			if (elements && elements.length) {
				$.each(elements, function( key, share) {
					$.each($options, function( index, entity ) {
						if (entity.value == share.value) {
							$('#multiselect_shared_thirdparty option[value=' + entity.value + ']').remove();
							$('#multiselect_shared_thirdparty_to').append($("<option></option>").attr("value", entity.value).text(entity.text));
							$('#multiselect_shared_thirdparty_to').html($('#multiselect_shared_thirdparty_to option').sort(function(x, y) {
					            return $(x).val() < $(y).val() ? -1 : 1;
					        }));
						}
					});
				});
			}
		},
		afterMoveToLeft: function($left, $right, $options) {
			$('#multiselect_shared_<?php echo $element; ?>').html($('#multiselect_shared_<?php echo $element; ?> option').sort(function(x, y) {
	            return $(x).val() < $(y).val() ? -1 : 1;
	        }));
		}
	});
<?php } } } ?>
});
</script>

<form id="form_entity" name="form_entity" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />

<?php if ($this->tpl['action'] == 'create') { ?>
<input type="hidden" name="action" value="add" />
<?php } else { ?>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="id" value="<?php echo $this->tpl['id']; ?>" />
<?php } ?>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><span class="fa fa-edit"></span><span class="multiselect-title"><?php echo $langs->trans("CompanyInfo"); ?></span></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>
<tr class="oddeven">
	<td><span class="fieldrequired"><?php echo $langs->trans("Label"); ?></span></td>
	<td><input name="label" size="40" value="<?php echo $this->tpl['label']; ?>" /></td>
</tr>
<tr class="oddeven">
	<td><span class="fieldrequired"><?php echo $langs->trans("CompanyName"); ?></span></td>
	<td><input name="name" size="40" value="<?php echo $this->tpl['name']; ?>" /></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("CompanyAddress"); ?></td>
	<td><textarea name="address" cols="80" rows="<?php echo ROWS_3; ?>"><?php echo $this->tpl['address']; ?></textarea></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("CompanyZip"); ?></td>
	<td><?php echo $this->tpl['select_zip']; ?></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("CompanyTown"); ?></td>
	<td><?php echo $this->tpl['select_town']; ?></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("Country"); ?></td>
	<td><?php echo $this->tpl['select_country'].$this->tpl['info_admin']; ?></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("State"); ?></td>
	<td><?php echo $this->tpl['select_state']; ?></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("CompanyCurrency"); ?></td>
	<td><?php echo $this->tpl['select_currency']; ?></td>
</tr>
<tr class="oddeven">
	<td><?php echo $langs->trans("DefaultLanguage"); ?></td>
	<td><?php echo $this->tpl['select_language']; ?></td>
</tr>
<tr class="oddeven">
	<td class="tdtop"><?php echo $langs->trans("Description"); ?></td>
	<td><textarea class="flat" name="description" cols="80" rows="<?php echo ROWS_3; ?>"><?php echo $this->tpl['description']; ?></textarea></td>
</tr>

<?php
if (! empty($this->tpl['extrafields']->attribute_label)) {
	print $this->dao->showOptionals($this->tpl['extrafields'], 'edit');
}
if (! empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED)) {

foreach($this->sharingelements as $element => $params) {
	if (! empty($this->tpl['multiselect_from_' . $element])) {
		$uppername = strtoupper($element);
		$icon = (! empty($params['icon'])?$params['icon']:'edit');
?>
<tr class="liste_titre">
	<td colspan="2"><span class="fa fa-<?php echo $icon; ?>"></span><span class="multiselect-title"><?php echo $langs->trans($uppername . "Sharing"); ?></span></td>
</tr>
<tr class="oddeven">
	<td class="tdtop"><?php echo $langs->trans($uppername . "SharingDescription"); ?></td>
	<td>
		<div class="row">
           	<div class="col-sm-5">
           		<div class="multiselect-selected-title"><span class="fa fa-globe"></span><span class="multiselect-selected-title-text"><?php echo $langs->trans("EntitiesSelected"); ?></span></div>
           		<?php echo $this->tpl['multiselect_to_' . $element]; ?>
           	</div>
			<div class="col-xs-2 multiselect-menu">
				<!-- <button type="button" id="multiselect_shared_<?php //echo $element; ?>_undo" class="btn btn-primary btn-block"><?php //echo $langs->trans("Undo"); ?></button> -->
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_leftAll" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-backward"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_leftSelected" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-chevron-left"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_rightSelected" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-chevron-right"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_rightAll" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-forward"></i></button>
				<!-- <button type="button" id="multiselect_shared_<?php //echo $element; ?>_redo" class="btn btn-warning btn-block"><?php //echo $langs->trans("Redo"); ?></button> -->
			</div>
			<div class="col-xs-5">
				<div class="multiselect-available-title"><span class="fa fa-globe"></span><span class="multiselect-available-title-text"><?php echo $langs->trans("EntitiesAvailable"); ?></span></div>
				<?php echo $this->tpl['multiselect_from_' . $element]; ?>
			</div>
		</div>
	</td>
</tr>
<?php if ($element == 'invoicenumber' && ! empty($conf->global->MULTICOMPANY_INVOICENUMBER_SHARING_ENABLED)) { ?>
<tr class="oddeven">
	<td class="tdtop"><?php echo $langs->trans("ReferringEntity"); ?></td>
	<td><?php echo $this->tpl['select_entity']; ?></td>
</tr>
<tr class="multiselect-separator"><td colspan="2">&nbsp;</td></tr>

<?php } } } ?>

<?php
foreach($this->sharingobjects as $element => $params) {
	if (! empty($this->tpl['multiselect_from_' . $element])) {
		$uppername = strtoupper($element);
		$icon = (! empty($params['icon'])?$params['icon']:'edit');
?>
<tr class="liste_titre">
	<td colspan="2"><span class="fa fa-<?php echo $icon; ?>"></span><span class="multiselect-title"><?php echo $langs->trans($uppername . "Sharing"); ?></span></td>
</tr>
<tr class="oddeven">
	<td class="tdtop"><?php echo $langs->trans($uppername . "SharingDescription"); ?></td>
	<td>
		<div class="row">
           	<div class="col-sm-5">
           		<div class="multiselect-selected-title"><span class="fa fa-globe"></span><span class="multiselect-selected-title-text"><?php echo $langs->trans("EntitiesSelected"); ?></span></div>
           		<?php echo $this->tpl['multiselect_to_' . $element]; ?>
           	</div>
			<div class="col-xs-2 multiselect-menu">
				<!-- <button type="button" id="multiselect_shared_<?php //echo $element; ?>_undo" class="btn btn-primary btn-block"><?php //echo $langs->trans("Undo"); ?></button> -->
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_leftAll" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-backward"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_leftSelected" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-chevron-left"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_rightSelected" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-chevron-right"></i></button>
				<button type="button" id="multiselect_shared_<?php echo $element; ?>_rightAll" class="btn btn-block multiselect-menu-btn-color"><i class="glyphicon glyphicon-forward"></i></button>
				<!-- <button type="button" id="multiselect_shared_<?php //echo $element; ?>_redo" class="btn btn-warning btn-block"><?php //echo $langs->trans("Redo"); ?></button> -->
			</div>
			<div class="col-xs-5">
				<div class="multiselect-available-title"><span class="fa fa-globe"></span><span class="multiselect-available-title-text"><?php echo $langs->trans("EntitiesAvailable"); ?></span></div>
				<?php echo $this->tpl['multiselect_from_' . $element]; ?>
			</div>
		</div>
	</td>
</tr>

<?php } } } ?>

</table>
</div>

<div class="tabsAction">
<?php if ($this->tpl['action'] == 'create') { ?>
<input type="submit" class="butAction linkobject" name="add" value="<?php echo $langs->trans('Add'); ?>" />
<?php } else { ?>
<input type="submit" class="butAction linkobject" name="update" value="<?php echo $langs->trans('Update'); ?>" />
<?php } ?>
<input type="submit" class="butAction linkobject" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>" />
</div>

</form>

<!-- END PHP TEMPLATE -->
