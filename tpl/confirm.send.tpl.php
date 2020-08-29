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

/**	    \file       htdocs/confirm/tpl/confirm_send.php
 *		\ingroup    confirm
 *		\brief      ConfRDV module send confirmation view
 */

llxHeader('', $langs->trans("ConfRDV"));


echo ($err ? dol_htmloutput_mesg($errMsg, '', 'error', 0) : '');

print_fiche_titre($langs->trans("ConfRDV"));


?>
<br />

<?php print_titre($langs->trans("ConfRDVDetails")); ?>


<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
	<tr class="impair">
        <td><?php echo $langs->trans("Title"); ?></td>
        <td><?php echo $actioncomm->label; ?></td>
    </tr>
	<tr class="pair">
        <td><?php echo $langs->trans("Type"); ?></td>
        <td><?php echo $actioncomm->type; ?></td>
    </tr>


	<tr class="impair">
        <td>
        <?php echo $langs->trans("DateActionStart"); 
             if ($actioncomm->datef){ 
              echo ' / '.$langs->trans("DateActionEnd"); 
            }
        ?>
        </td>
        <td>
        <?php echo dol_print_date($actioncomm->datep, "dayhourtext"); 
             if ($actioncomm->datef){ 
              echo ' / '.dol_print_date($actioncomm->datef, "dayhourtext"); 
            } 
        ?>       
        </td>
    </tr>

    <?php if ($actioncomm->societe->id){ ?>
	<tr class="pair">
        <td><?php echo $langs->trans("SocietyConcerned"); ?></td>
        <td><?php echo $actioncomm->societe->getNomUrl(1); ?>       </td>
    </tr>        
    <?php } ?>
        
    <tr class="impair">    
    <?php if ($actioncomm->phonecall){ ?>	
        <td><?php echo $langs->trans("Phone"); ?></td>
        <td><?php echo $rdv->phone; ?>       </td>                  
    <?php }else{ ?>
         <td><?php echo $langs->trans("Address"); ?></td>
        <td><?php echo str_replace("\n", "<br />", $rdv->address); ?>       </td>                
    <?php } ?>
    </tr> 
    
</table>

<br />


<?php $formmail->show_form(); ?>


<?php llxFooter(''); ?>
