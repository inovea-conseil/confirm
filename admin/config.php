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


/**
 *      \file       htdocs/confirm/admin/confirm_config.php
 *		\ingroup    confirm
 *		\brief      Page to setup appointment confirmation module
 */

$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

dol_include_once("/confirm/class/confirm.class.php");


$langs->load("admin");
$langs->load("companies");
$langs->load("confirm@confirm");
$langs->load("other");
$langs->load("errors");

if (!$user->admin){
   accessforbidden(); 
}


$error = false;
$message = '';
/*
 * Actions
 */

if ($_POST["action"] == 'updateMask')
{
    $maskconst = $_POST['maskconst'];
    $mask = $_POST['mask'];

    if ($maskconst){
        dolibarr_set_const($db, $maskconst, $mask, 'chaine', 0, '', $conf->entity);
    } 
}

if ($_GET["action"] == 'specimen')
{
    $modele = $_GET["module"];

    $confirm = new Confirm($db);
    $confirm->initAsSpecimen();

    // Load template
    $dir = '../core/modules/confirm/doc/';
    $file = "pdf_".$modele.".modules.php";
    
    if (file_exists($dir.$file))
    {
        $classname = "pdf_".$modele;
        require_once($dir.$file);

        $obj = new $classname($db);

        if ($obj->write_file($confirm,$langs) > 0)
        {
            header("Location: ".DOL_URL_ROOT."/document.php?modulepart=confirm&file=SPECIMEN.pdf");
            return;
        }
        else
        {
            $error = true;
            $message = $obj->error;

            dol_syslog($obj->error, LOG_ERR);
        }
    }
    else
    {
        $error = true;
        $message = $langs->trans("ErrorModuleNotFound");        

        dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
    }
}
 
if ($_GET["action"] == 'set')
{
    $type = 'confirm';
    
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
    $sql.= " VALUES ('".$db->escape($_GET["value"])."','".$type."',".$conf->entity.", ";
    $sql.= ($_GET["label"]?"'".$db->escape($_GET["label"])."'":'null').", ";
    $sql.= (! empty($_GET["scandir"])?"'".$db->escape($_GET["scandir"])."'":"null");
    $sql.= ")";
    
    $db->query($sql);
}

if ($_GET["action"] == 'del')
{
    $type = 'confirm';
    
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
    $sql.= " WHERE nom = '".$_GET["value"]."'";
    $sql.= " AND type = '".$type."'";
    $sql.= " AND entity = ".$conf->entity;

    $db->query($sql);
}

 
if ($_GET["action"] == 'setdoc')
{
    $db->begin();

    if (dolibarr_set_const($db, "CONFIRM_ADDON_PDF",$_GET["value"],'chaine',0,'',$conf->entity))
    {
        $conf->global->CONFIRM_ADDON_PDF = $_GET["value"];
    }

    // On active le modele
    $type = 'confirm';

    $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
    $sql_del.= " WHERE nom = '".$db->escape($_GET["value"])."'";
    $sql_del.= " AND type = '".$type."'";
    $sql_del.= " AND entity = ".$conf->entity;
    dol_syslog("confirm_config.php ".$sql_del);
    
    $result1 = $db->query($sql_del);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
    $sql.= " VALUES ('".$_GET["value"]."', '".$type."', ".$conf->entity.", ";
    $sql.= ($_GET["label"]?"'".$db->escape($_GET["label"])."'":'null').", ";
    $sql.= (! empty($_GET["scandir"])?"'".$_GET["scandir"]."'":"null");
    $sql.= ")";
    dol_syslog("confirm_config.php ".$sql);
    
    $result2 = $db->query($sql);
    
    if ($result1 && $result2)
    {
        $db->commit();
    }
    else
    {
        dol_syslog("confirm_config.php ".$db->lasterror(), LOG_ERR);
        $db->rollback();
    }
}

if ($_GET["action"] == 'setmod')
{
    // TODO Verifier si module numerotation choisi peut etre active
    // par appel methode canBeActivated

    dolibarr_set_const($db, "CONFIRM_ADDON",$_GET["value"],'chaine',0,'',$conf->entity);
}


if ($_POST["action"] == 'setemail')
{
    $email = $_POST['emailfrom'];
    
    if (!isValidEMail($email)){
        $error = true;
        $message = $langs->trans("InvalidEmailAddress");        
    }else{
        dolibarr_set_const($db, "CONFIRM_EMAIL_FROM",trim($email),'chaine',0,'',$conf->entity);
    }
    
}


$html = new Form($db);


$modules = array();
$modules2 = array();


$i = 0;
$dir = '../core/modules/confirm/';

if (is_dir($dir))
{
	$handle = opendir($dir);
	if (is_resource($handle))
	{
		while (($file = readdir($handle))!==false)
		{
			if (! is_dir($dir.$file) || (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS'))
			{
				$filebis = $file;
				$classname = preg_replace('/\.php$/','',$file);
				// For compatibility
				if (! is_file($dir.$filebis))
				{
					$filebis = $file."/".$file.".modules.php";
					$classname = "mod_confirm_".$file;
				}
				//print "x".$dir."-".$filebis."-".$classname;
				if (! class_exists($classname) && is_readable($dir.$filebis) && (preg_match('/mod_/',$filebis) || preg_match('/mod_/',$classname)) && substr($filebis, dol_strlen($filebis)-3, 3) == 'php')
				{
					// Chargement de la classe de numerotation
					require_once($dir.$filebis);

					$module = new $classname($db);

					// Show modules according to features level
					if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

					if ($module->isEnabled())
					{
						$modules[$i]->name = preg_replace('/mod_confirm_/','',preg_replace('/\.php$/','',$file));
						$modules[$i]->info = $module->info();

						$tmp = $module->getExample();
						
						if (preg_match('/^Error/',$tmp)){
							$modules[$i]->example = $langs->trans($tmp);
						}else{
							$modules[$i]->example = $tmp;
						}
						
						if ($conf->global->CONFIRM_ADDON == $file || $conf->global->CONFIRM_ADDON.'.php' == $file){
							$modules[$i]->state = img_picto($langs->trans("Activated"),'on');
						}else{
							$modules[$i]->state = '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.preg_replace('/\.php$/','',$file).'&amp;scandir='.$module->scandir.'&amp;label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
						} 
						

						$confirm = new Confirm($db);
						$confirm->initAsSpecimen();
					
						$htmltooltip = '';
						$htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';

						$nextval = $module->getNextValue($mysoc, $confirm);
						if ("$nextval" != $langs->trans("NotAvailable"))	// Keep " on nextval
						{
							$htmltooltip .= $langs->trans("NextValueForConfirm").': ';
							if ($nextval)
							{
								$htmltooltip.= $nextval.'<br />';
							}
							else
							{
								$htmltooltip.=$langs->trans($module->error).'<br />';
							}
						}
						
						$modules[$i]->tooltip = $html->textwithpicto('', $htmltooltip, 1, 0);           
						$modules[$i]->error = '';
						
						if ($conf->global->CONFIRM_ADDON.'.php' == $file)  // If module is the one used, we show existing errors
						{
							$modules[$i]->error = $module->error;
						}
													
					}
					
					$i++;
				}                 
				
			}
			
		}
		
		closedir($handle);
	}
}



// Load array def with activated templates
$def = array();

$sql = "SELECT nom";
$sql.= " FROM ".MAIN_DB_PREFIX."document_model";
$sql.= " WHERE type = 'confirm'";
$sql.= " AND entity = ".$conf->entity;

$resql = $db->query($sql);

if ($resql)
{
    $i = 0;
    $num_rows = $db->num_rows($resql);
    while ($i < $num_rows)
    {
        $array = $db->fetch_array($resql);
        array_push($def, $array[0]);
        $i++;
    }
}
else
{
    $error = true;
    $message = $db->error;
}

$i = 0;
$dir = '../core/modules/confirm/doc';

if (is_dir($dir))
{
	$handle = opendir($dir);
	if (is_resource($handle))
	{
		while (($file = readdir($handle))!==false)
		{
			$filelist[]=$file;
		}
		closedir($handle);


		foreach($filelist as $file)
		{
			if (preg_match('/\.modules\.php$/i',$file) && preg_match('/^(pdf_|doc_)/',$file))
			{
				if (file_exists($dir.'/'.$file))
				{
					$name = substr($file, 4, dol_strlen($file) -16);
					$classname = substr($file, 0, dol_strlen($file) -12);

					require_once($dir.'/'.$file);
					$module = new $classname($db);

					$modulequalified=1;
					if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified=0;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified=0;

					if ($modulequalified)
					{
						
						$modules2[$i]->name = (empty($module->name)?$name:$module->name);

						if (method_exists($module,'info')){
							$modules2[$i]->desc = $module->info($langs);
						}else{
							$modules2[$i]->desc = $module->description;
						} 
						
						$url = '';
						
						if (in_array($name, $def))
						{
							if ($conf->global->CONFIRM_ADDON_PDF != "$name")
							{
								$url .= '<a href="'.$_SERVER["PHP_SELF"].'?action=del&amp;value='.$name.'">';
								$url .= img_picto($langs->trans("Enabled"),'on');
								$url .= '</a>';
							}
							else
							{
								$url = img_picto($langs->trans("Enabled"),'on');
							}

						}
						else
						{
							
							$url .= '<a href="'.$_SERVER["PHP_SELF"].'?action=set&amp;value='.$name.'&amp;scandir='.$module->scandir.'&amp;label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"),'off').'</a>';

						}
						
						$modules2[$i]->active = $url;

						$url = '';
						if ($conf->global->CONFIRM_ADDON_PDF == "$name")
						{
							$url .=  img_picto($langs->trans("Default"),'on');
						}
						else
						{
							$url .=  '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&amp;value='.$name.'&amp;scandir='.$module->scandir.'&amp;label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
						}
						
						$modules2[$i]->default = $url;
						
						// Info
						$htmltooltip =    ''.$langs->trans("Name").': '.$module->name;
						$htmltooltip.='<br />'.$langs->trans("Type").': '.($module->type?$module->type:$langs->trans("Unknown"));
						if ($module->type == 'pdf')
						{
							$htmltooltip.='<br />'.$langs->trans("Height").'/'.$langs->trans("Width").': '.$module->page_hauteur.'/'.$module->page_largeur;
						}
						$htmltooltip.='<br /><br /><u>'.$langs->trans("FeaturesSupported").':</u>';
						$htmltooltip.='<br />'.$langs->trans("Logo").': '.yn($module->option_logo,1,1);
						$htmltooltip.='<br />'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang,1,1);

						$modules2[$i]->info = $html->textwithpicto('', $htmltooltip, 1, 0);

						$url = '';
						if ($module->type == 'pdf')
						{
							$url .= '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"),'generic').'</a>';
						}
						else
						{
							$url .= img_object($langs->trans("PreviewNotAvailable"),'generic');
						} 
						
						$modules2[$i]->preview = $url;
						
						$i++;                                                                                               
					}
				}
			}                 
		}
	}
}

/*
 * View
 */

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

require_once("./../tpl/confirm.admin.tpl.php");

$db->close();

?>
