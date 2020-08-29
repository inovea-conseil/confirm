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


llxHeader("", $langs->trans("ConfRDVSetup"));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');


print_fiche_titre($langs->trans("ConfRDVSetup"), $linkback, 'setup');

?>
<br />
<?php print_titre($langs->trans("ConfRDVNumberingModule")); ?>

<?php clearstatcache(); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Description"); ?></td>
        <td nowrap><?php echo $langs->trans("Example"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Status"); ?></td>
        <td align="center" width="16"><?php echo $langs->trans("Infos"); ?></td>
    </tr>

    <?php for($i=0; $i<sizeof($modules); $i++){ ?>
    <tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>">
        <td width="100">
            <?php echo $modules[$i]->name; ?>
        </td>
        
        <td>
            <?php echo $modules[$i]->info; ?>
        </td>

        <td nowrap="nowrap">
            <?php echo $modules[$i]->example; ?>
        </td>

        <td align="center">
            <?php echo $modules[$i]->state; ?>
        </td>

        <td align="center">
            <?php        
            echo $modules[$i]->tooltip;
            echo $modules[$i]->error;   
            ?>
        </td>
    </tr>        
    <?php } ?>
</table>
<br />

<?php print_titre($langs->trans("ConfRDVPDFModules")); ?>

<?php clearstatcache(); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Description"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Status"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Default"); ?></td>
        <td align="center" width="32" colspan="2"><?php echo $langs->trans("Infos"); ?></td>
    </tr>

    <?php for($i=0; $i<sizeof($modules2); $i++){ ?>
    <tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>">
	    <td width="100">
	       <?php echo $modules2[$i]->name; ?>
	    </td>
        
        <td>
	       <?php echo $modules2[$i]->desc; ?>                     
	    </td>
        
        <td align="center">
            <?php echo $modules2[$i]->active; ?>
        </td>
	                  
        <td align="center">
            <?php echo $modules2[$i]->default; ?>
        </td>

        <td align="center">
	        <?php echo $modules2[$i]->info; ?>
	    </td>
	                          
	    <td align="center">
            <?php echo $modules2[$i]->preview; ?>
	   </td>

    </tr>        
    <?php } ?>
</table>
<br />

<?php print_titre($langs->trans("OtherOptions")); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Parameter"); ?></td>
        <td align="center"><?php echo $langs->trans("Value"); ?></td>
        <td width="80">&nbsp;</td>
    </tr>

    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
    <input type="hidden" name="action" value="setemail" />
    
    <tr class="imapir">
        <td><?php echo $langs->trans("ConfRDVEmailParameter"); ?></td>
        <td align="center">
            <input type="text" name="emailfrom" class="flat" size="36" value="<?php echo $conf->global->CONFIRM_EMAIL_FROM; ?>" />
        </td>
        <td align="right">
            <input type="submit" class="button" value="<?php echo $langs->trans("Modify"); ?>" />
        </td>
    </tr>
    </form>

</table>


<br />
<?php print_titre($langs->trans("PathToDocuments")); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Value"); ?></td>
    </tr>
    <tr class="imapir">
        <td width="140"><?php echo $langs->trans("PathDirectory"); ?></td>
        <td><?php echo $conf->global->CONFIRM_DIR_OUTPUT; ?></td>
    </tr>
</table>

<?php llxFooter(''); ?>
