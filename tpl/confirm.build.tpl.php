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

/**	    \file       htdocs/confrdv/tpl/confrdv_build.php
 *		\ingroup    confrdv
 *		\brief      ConfRDV module build confirmation view
 */

llxHeader('', $langs->trans("ConfRDV"));


echo ($err ? dol_htmloutput_mesg($errMsg, '', 'error', 0) : '');

print_fiche_titre($langs->trans("ConfRDV"));


?>
<br />

<?php print_titre($langs->trans("ConfRDVDetails")); ?>

<form name="formaction" action="<?php echo DOL_URL_ROOT.'/comm/action/card.php'; ?>"  method="POST">
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="id" value="<?php echo $actioncomm->id; ?>" />
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
        
    </table>

    
<center>
<br />
<input type="submit" class="button" value="<?php echo $langs->trans("Modify");?>" />&nbsp; &nbsp;
</center>    
</form>

<br />

<form name="formconf" action="<?php echo dol_buildpath('/confirm/confirm.php',1); ?>" method="post">
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
<input type="hidden" name="action" value="confirm" />
<input type="hidden" name="id" value="<?php echo $actioncomm->id; ?>" />
<input type="hidden" id="mobile" name="mobile" value="0" />
<input type="hidden" name="sortfield" value="<?php echo $sortfield; ?>" />
<input type="hidden" name="sortorder" value="<?php echo $sortorder; ?>" />
 
<?php if (sizeof($address) > 0 || sizeof($addressc) > 0 || sizeof($phones) > 0 || sizeof($phonesc) > 0){ ?>
<?php (!$isPhoneCall ? print_titre($langs->trans("SelectAddress")) : print_titre($langs->trans("SelectPhone"))); ?> 
<table class="noborder" width="100%">

    <?php if (!$isPhoneCall){ ?>
    
    <?php if (sizeof($address) > 0){ ?>
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td><?php echo $langs->trans('Society'); ?></td>
    </tr>
    

    <?php for ($i=0; $i<sizeof($address); $i++){ ?>
    
	<tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>" >
        <td class="requiredfield"  width="40">
            <input type="radio" name="contactid" value="<?php echo $address[$i]->cid; ?>" />
        </td>
        <td>     
            <?php echo str_replace("\n", "<br />", $address[$i]->address); ?>
        </td>
    </tr>
    <?php } ?>
    <?php } ?>              
    <?php if (sizeof($addressc) > 0){ ?>
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td><?php echo $langs->trans('Contact'); ?> (<?php echo $soc->name; ?>)</td>
    </tr>
     <?php for ($i=0; $i<sizeof($addressc); $i++){ ?>
    
	<tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>" >
        <td class="requiredfield"  width="40">
            <input type="radio" name="contactid" value="<?php echo $addressc[$i]->cid; ?>" />
        </td>
        <td>     
            <?php echo str_replace("\n", "<br />", $addressc[$i]->address); ?>
        </td>
    </tr>
    <?php } ?>       
    <?php } ?>  
          
    <?php }else{ ?>
    
    <?php if (sizeof($phones) > 0){ ?>
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td><?php echo $langs->trans('Society'); ?></td>
    </tr>
        
    <?php for ($i=0; $i<sizeof($phones); $i++){ ?>
    
	<tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>">
        <td class="requiredfield"  width="40">
            <input type="radio" name="contactid" value="<?php echo $phones[$i]->cid; ?>" onchange="document.getElementById('mobile').value = '<?php echo $phones[$i]->mobile; ?>';"/>
        </td>
        <td>     
            <?php echo $phones[$i]->phone; ?>
        </td>
    </tr>     
    
    <?php } ?>
    <?php } ?>  
        
    <?php if (sizeof($phonesc) > 0){ ?>
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td><?php echo $langs->trans('Contacts'); ?> (<?php echo $soc->name; ?>)</td>
    </tr>
     <?php for ($i=0; $i<sizeof($phonesc); $i++){ ?>
    
	<tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>" >
        <td class="requiredfield"  width="40">
            <input type="radio" name="contactid" value="<?php echo $phonesc[$i]->cid; ?>" onchange="document.getElementById('mobile').value = '<?php echo $phonesc[$i]->mobile; ?>';"/>
        </td>
        <td>     
             <?php echo $phonesc[$i]->phone; ?>
        </td>
    </tr>
    <?php } ?>
    <?php } ?>              
          
    <?php } ?> 
        
</table>
<br />    
<?php } ?>  

<?php if (sizeof($address) > 0 || sizeof($addressc) > 0 || sizeof($phones) > 0 || sizeof($phonesc) > 0){ 
    
 (!$isPhoneCall ? print_titre($langs->trans("OrFillInAddress")) : print_titre($langs->trans("OrFillInPhone")));
  
 }else{ 
    
 (!$isPhoneCall ? print_titre($langs->trans("FillInAddress")) : print_titre($langs->trans("FillInPhone")));
 
 } ?> 
<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    
    <?php if (!$isPhoneCall){ ?>
    
	<tr class="impair">
        <td class="requiredfield" rowspan="3" width="40">
            <input type="radio" value="-1" name="contactid" checked="checked"  />
        </td>
        <td width="70">     
            <?php echo $langs->trans("Address"); ?>: 
        </td>
        <td>
            <textarea name="address" wrap="soft" cols="70" rows="3" ></textarea>
        </td>
    </tr>     

        
	<tr class="impair">
        <td width="70">        
            <?php echo $langs->trans("CP"); ?>: 
        </td>
        <td>
            <input type="text" name="zip" size="10" />
        </td>
    </tr>
    
	<tr class="impair">         
        <td width="70">      
            <?php echo $langs->trans("Town"); ?>: 
        </td>
        <td>
            <input type="text" name="town" size="32"  />
        </td>
    </tr> 
        
    <?php }else{ ?>
	<tr class="impair">
        <td class="requiredfield"  width="40">
            <input type="radio" value="-1" name="contactid" checked="checked" />
        </td>
        <td width="70">        
            <?php echo $langs->trans("Phone"); ?>: 
        </td>
        <td>
            <input type="text" name="phone" size="16" />
        </td>
    </tr>     
       
    <?php } ?>    
    
</table>
<br />

<?php print_titre($langs->trans("PDFModel")); ?>
<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    
   <tr class="impair">
        <td width="40"><?php echo $langs->trans('Name'); ?></td>
        <td><?php echo $form->selectarray('model', $liste, $conf->global->CONFIRM_ADDON_PDF); ?></td>
    </tr>    
</table>
<br />

    
<center>
<br />
<input type="submit" class="button" name="submit" value="<?php echo $langs->trans("Confirm");?>" />&nbsp; &nbsp;
<input type="submit" class="button" name="cancel" value="<?php echo $langs->trans("Cancel");?>" />
</center>

</form>

<br />
<br />


<?php llxFooter(''); ?>
