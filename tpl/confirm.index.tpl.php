<?php

/* Copyright (C) 2012      Mikael Carlavan        <mcarlavan@qis-network.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
/**	    \file       htdocs/confirm/tpl/confirm.index.tpl.php
 *		\ingroup    ndfp
 *		\brief      Confirm module default view
 */

llxHeader('', $langs->trans("ConfRDV"));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

    
dol_fiche_head($head, $current_head, $langs->trans('AppList'), 0, 'list');
?>

<form name="listactionsfilter" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
<input type="hidden" name="status" value="<?php echo $status; ?>" />


<table class="nobordernopadding" width="100%">
<tr>
<td nowrap="nowrap">

<table class="nobordernopadding">
    <tr>
        <td nowrap="nowrap"><?php echo $langs->trans("ActionsAskedBy"); ?>&nbsp;</td>
        <td nowrap="nowrap"><?php echo $form->select_users($filtera,'userasked'); ?></td>
    </tr>

    <tr>
        <td nowrap="nowrap"><?php echo $langs->trans("or").' '.$langs->trans("ActionsToDoBy"); ?>&nbsp;</td>
        <td nowrap="nowrap"><?php echo $form->select_users($filtert,'usertodo');?></td>
    </tr>

    <tr>
        <td nowrap="nowrap"><?php echo $langs->trans("or").' '.$langs->trans("ActionsDoneBy");?>&nbsp;</td>
        <td nowrap="nowrap"><?php echo $form->select_users($filterd,'userdone');?></td>
    </tr>
			
    <tr>
        <td nowrap="nowrap"><?php echo $langs->trans("or").' '.$langs->trans("ActionsConfBy");?>&nbsp;</td>
        <td nowrap="nowrap"><?php echo $form->select_users($filterc,'userconf');?></td>
    </tr>

    <tr>
        <td nowrap="nowrap"><?php echo $langs->trans("or").' '.$langs->trans("ActionsSendBy");?>&nbsp;</td>
        <td nowrap="nowrap"><?php echo $form->select_users($filters,'usersend');?></td>
    </tr>    
<?php if ($conf->projet->enabled){ ?>
<tr>
    <td nowrap="nowrap"><?php echo $langs->trans("Project");?> &nbsp;</td>
    <td nowrap="nowrap"><?php $formproject->select_projects($socid?$socid:-1, $pid, 'projectid'); ?></td>
</tr>
<?php } ?>
</table>
</td>

</table>
</form>
<?php 

echo dol_fiche_end(); 

print_barre_liste($newtitle, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder,'', $num, 0, '');

?>
        
<table class="liste" width="100%">
    <tr class="liste_titre">
	   <?php print_liste_field_titre($langs->trans("Action"),$_SERVER["PHP_SELF"],"acode",$param,"","",$sortfield,$sortorder); ?>
       <?php print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"a.fk_action",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("DateStart"),$_SERVER["PHP_SELF"],"a.datep",$param,'','align="center"',$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("DateEnd"),$_SERVER["PHP_SELF"],"a.datep2",$param,'','align="center"',$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"],"s.nom",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("ActionUserAsk"),$_SERVER["PHP_SELF"],"ua.login",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("AffectedTo"),$_SERVER["PHP_SELF"],"ut.login",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("DateConf"),$_SERVER["PHP_SELF"],"co.datec",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("DateSend"),$_SERVER["PHP_SELF"],"co.dates",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("ConfBy"),$_SERVER["PHP_SELF"],"uc.login",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("SentBy"),$_SERVER["PHP_SELF"],"uc.login",$param,"","",$sortfield,$sortorder); ?>
	   <?php print_liste_field_titre($langs->trans("Actions"),$_SERVER["PHP_SELF"],"a.conf_sent",$param,"",'align="right"',$sortfield,$sortorder); ?>
    </tr>
    <?php if (sizeof($actions)) { 
            for ($i=0; $i < sizeof($actions); $i++){ ?>
    	<tr class="<?php echo ($i%2== 0 ? 'impair' : 'pair'); ?>">
    		<td>
                <?php echo $actions[$i]->title; ?>
            </td>
    		<td>
                <?php echo $langs->trans('Action'.$actions[$i]->type); ?>
            </td>        
            <td align="center" nowrap="nowrap">
                <?php echo $actions[$i]->dates; ?> &nbsp;
                <?php if ($actions[$i]->late){ 
                    echo img_warning($langs->trans("Late"));
                } ?>
            </td>
            <td align="center" nowrap="nowrap">
                <?php echo $actions[$i]->datee; ?>
            </td>
            <td>
                <?php echo $actions[$i]->soc; ?>
            </td>       
            <td>
                <?php echo $actions[$i]->userauthor; ?>
            </td> 
            <td>
                <?php echo $actions[$i]->usertodo; ?>
            </td>         
            <td>
                 <?php echo $actions[$i]->dateconf; ?>
            </td>
            <td>
                <?php echo $actions[$i]->datesend; ?>
            </td> 
            <td>
                <?php echo $actions[$i]->userconf; ?>
            </td>
            <td>
                <?php echo $actions[$i]->usersend; ?>
            </td>                              
            <td align="right" nowrap="nowrap">
                <?php if ($actions[$i]->built || $actions[$i]->sent){ ?>
                
                    <?php echo $actions[$i]->tosend; ?>&nbsp;<?php echo $actions[$i]->tosee; ?>&nbsp;<?php echo $actions[$i]->toreload; ?>
                    
                <?php }else{ ?>
                    
                    <?php echo $actions[$i]->tobuild; ?>
                    
                <?php } ?>
            </td>         
       
        </tr>        
    <?php }
    }else{ ?>
    	<tr class="impair">
    		<td colspan="12">
                <?php echo $langs->trans("NoActions"); ?>
            </td>
        </tr>              
<?php } ?>
</table>


<?php llxFooter(''); ?>
