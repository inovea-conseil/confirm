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
 *	\file       htdocs/confirm/fiche.php
 *	\ingroup    confirm
 *	\brief      Confirmation appointment form
 */

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/comm/action/class/actioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/interfaces.class.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');

if (! empty($conf->projet->enabled)) 
{
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}

dol_include_once("/confirm/class/confirm.class.php");

                
$langs->load("companies");
$langs->load("commercial");
$langs->load("other");
$langs->load("agenda");
$langs->load("confirm@confirm");

$action = GETPOST("action");

$error = false;
$message = '';

// Security check
$id = GETPOST('id', 'int');
$socid = GETPOST("socid",'int');

if ($user->societe_id)
{
   $socid=$user->societe_id; 
}
 
$status = GETPOST("status",'alpha');//tosend or sent

$filter = GETPOST("filter");
$filtera = GETPOST("userasked","int") ? GETPOST("userasked","int") : GETPOST("filtera","int");
$filtert = GETPOST("usertodo","int") ? GETPOST("usertodo","int") : GETPOST("filtert","int");
$filterd = GETPOST("userdone","int") ? GETPOST("userdone","int") : GETPOST("filterd","int");
$filterc = GETPOST("userconf","int") ? GETPOST("userconf","int") : GETPOST("filterc","int");
$filters = GETPOST("usersend","int") ? GETPOST("usersend","int") : GETPOST("filters","int");

$pid = GETPOST("projectid",'int');
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
$type = GETPOST("type",'alpha');

if ($page == -1)
{ 
    $page = 0; 
}

$limit = $conf->liste_limit;
$offset = $limit * $page;

if (! $sortorder)
{
	$sortorder = "DESC";
	if ($status == 'tosend' or $status == 'tobuild') $sortorder = "ASC";
	if ($status == 'sent' or $status == 'built') $sortorder = "DESC";    
}

if (! $sortfield)
{
	$sortfield = "dp";
}

$param = '&sortfield='.urlencode($sortfield).'&sortorder='.urlencode($sortorder);

if ($status)
{
   $param .= "&status=".$status; 
} 
if ($filter)
{
   $param .= "&filter=".$filter; 
} 
if ($filtera)
{
   $param .= "&filtera=".$filtera; 
} 
if ($filtert)
{
   $param .= "&filtert=".$filtert; 
} 
if ($filterd)
{
   $param .= "&filterd=".$filterd; 
}
if ($filterc)
{
   $param .= "&filterc=".$filterc; 
}
if ($filters)
{
   $param .= "&filters=".$filterc; 
}   
if ($socid)
{
    $param .= "&socid=".$socid;
} 
if ($pid)
{
  $param .= "&projectid=".$pid;  
} 
if ($type)
{
   $param .= "&type=".$type; 
} 

if (!$user->rights->confirm->allactions->read || $filter == 'mine')
{	
	// If no permission to see all, we show only affected to me
	$filtera = $user->id;
	$filtert = $user->id;
	$filterd = $user->id;
}

$actionstatic = new ActionComm($db);
$societestatic = new Societe($db);
$formproject = new FormProjets($db);

$form = new Form($db);
$actions = array();

$now = dol_now();
$delay_warning = $conf->global->MAIN_DELAY_ACTIONS_TODO * 24 * 60 * 60;

$title = $langs->trans("ActionList");
if ($status == 'done')
{
   $title = $langs->trans("ToConfirmedActionList");
} 
if ($status == 'todo')
{
   $title = $langs->trans("ConfirmedActionList");
} 

if ($socid)
{
	$societe = new Societe($db);
	$societe->fetch($socid);
	$newtitle = $langs->trans($title).' '.$langs->trans("For").' '.$societe->nom;
}
else
{
	$newtitle = $langs->trans($title);
}

/*
 * Add file in email form
 */
if ($_POST['addfile'])
{
    // Set tmp user directory
    $vardir=$conf->user->dir_output."/".$user->id;
    $upload_dir_tmp = $vardir.'/temp';

    dol_add_file_process($upload_dir_tmp, 0, 0);
    
    $action = 'sendconf';
}

/*
 * Remove file in email form
 */
if (! empty($_POST['removedfile']))
{
    // Set tmp user directory
    $vardir=$conf->user->dir_output."/".$user->id;
    $upload_dir_tmp = $vardir.'/temp';

    dol_remove_file_process($_POST['removedfile'], 0);
    
    $action = 'sendconf';
}
						
if ($id > 0)
{
	$rdv = new Confirm($db);
	$result = $rdv->fetch_from_action($id);
	
    if ($result < 0)
    {
	    header("Location: ".$_SERVER['PHP_SELF']);
    }
    
    $actioncomm = $rdv->action;  
	$soc = $actioncomm->societe;
	
}

if ($action == 'buildconf')
{
	$canbuild = 0;
	if (($rdv->userconf->id == $user->id  && $user->rights->confirm->myactions->create) || $user->rights->confirm->allactions->create)
	{
		$canbuild = 1;
	}
	else
	{
		$error = true;
		$message = $langs->trans('NotEnoughPermissions');
		$action = '';
	}	
}

if ($action == 'sendconf')
{
	$cansend = 0;
	if (($rdv->userconf->id == $user->id  && $user->rights->confirm->myactions->send) || $user->rights->confirm->allactions->send)
	{
		$cansend = 1;
	}
	else
	{
		$error = true;
		$message = $langs->trans('NotEnoughPermissions');
		$action = '';
	}	
}
	
if ($action == 'confirm')
{
	$canbuild = 0;
	if (($rdv->userconf->id == $user->id  && $user->rights->confirm->myactions->create) || $user->rights->confirm->allactions->create)
	{
		$canbuild = 1;
	}
	else
	{
		$error = true;
		$message = $langs->trans('NotEnoughPermissions');
		$action = '';
	}    

	if (!$error)
	{
		$pCid = GETPOST("contactid",'alpha');
		$pMobile = GETPOST("mobile", 'alpha');
		$pPhone = GETPOST("phone",'alpha');

		$pAddress = GETPOST("address",'alpha');
		$pTown = GETPOST("town",'alpha');
		$pZip = GETPOST("zip",'alpha');

		// Remove previous confirmation
		$rdv->fk_action = $id;
		$rdv->delete_from_action($user);
				  
		$rdv->id = 0;
		$rdv->ref = 'PROV';

		$rdv->fk_action = $actioncomm->id;
		$rdv->conf_sent = 0;
		$rdv->conf_built = 0;
	
		$rdv->userconf = $user;
	
		$rdv->entity = $conf->entity;                        

		$rdv->datec = $now; // Confirmation date         
		$rdv->datef = $now; // Creation date for ref numbering 
		$rdv->dates = null;
	
		$rdv->phone = '';
		$rdv->address = '';
									
		if ($pCid == -1)
		{ //User fill in form
		
			$rdv->phone = $pPhone;
			$rdv->address = $pAddress ."\n". $pZip .' '. $pTown;
										  
		}
		elseif($pCid == 0)
		{
			//Use society phone number  and address
		
			$rdv->phone = $soc->phone;
		
			if ($soc->id > 0)
			{
				$rdv->address = $soc->name ."\n". $soc->getFullAddress();
			}
			else
			{
				$rdv->address = $soc->name ."\n". $soc->address ."\n". $soc->zip .' '. $soc->town;
			}
			
		}
		else
		{
			//Use contact phone number and address
					
			$c = new Contact($db);
			$result = $c->fetch($pCid);
		
			if ($result < 0)
			{

				$error = true;
				$message = $langs->trans('ContactDoesNotExist'); 
				$action = 'buildconf';                                
			}
			else
			{
				$rdv->phone = ($pMobile ? $c->phone_mobile : $c->phone_pro);
				$rdv->address = $c->getFullName($langs) ."\n". $c->getFullAddress();
			}
		}         
		   
		$result = $rdv->add($user);
   
		if ($result > 0)
		{
			$action = '';   
			
			//Build confirmation PDF
			$rdv->setDocModel($user, GETPOST('model'));
		
			// Define output language
			$outputlangs = $langs;
			$newlang = '';
		
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $rdv->action->societe->default_lang;
		
			if (! empty($newlang))
			{
				$outputlangs = new Translate("",$conf);
				$outputlangs->setDefaultLang($newlang);
			}
		
			$result = confirm_pdf_create($db, $rdv, '', $rdv->modelpdf, $outputlangs);
		
			if ($result <= 0)
			{
				$message = $langs->trans('ActionConfirmedButPDFNotBuilt');
				$error = true;           
			}
			else
			{
				$rdv->conf_built = 1;

				$result = $rdv->update($user);
			
				if ($result > 0)
				{
					$message = $langs->trans('ActionConfirmed');
					$error = false;                  
				}
				else
				{
					$essage = $rdv->error;
					$error = true;               
				}
			
			}            
		}
		else
		{
			$message = $langs->trans('ActionNotConfirmed');
			$error = true;                     
		} 
	}               
}

if (($action == 'send') && ! $_POST['addfile'] && ! $_POST['removedfile'] && ! $_POST['cancel'])
{
    $langs->load('mails');

	$action = '';
	
	$sendto = GETPOST('sendto');
	$receiver = GETPOST('receiver');
	$fromname = GETPOST('fromname');
	$frommail = GETPOST('frommail');
	$replytomail = GETPOST('replytomail');
	$replytoname = GETPOST('replytoname');
	$message = GETPOST('message');
	$deliveryreceipt = GETPOST('deliveryreceipt');
	$sendtocc = GETPOST('sendtocc');
	$subject = GETPOST('subject');
	
    $actiontypecode = '';
    $subject = '';
    $actionmsg = '';
    $actionmsg2 = '';

    $ref = dol_sanitizeFileName($rdv->ref);
    $file = $conf->global->CONFIRM_DIR_OUTPUT . '/' . $ref . '/' . $ref . '.pdf';

    if (is_readable($file))
    {
        if ($sendto)
        {
            // Le destinataire a ete fourni via le champ libre
            $sendto = $sendto;
            $sendtoid = 0;
        }
        elseif ($receiver != '-1')
        {
            // Recipient was provided from combo list
            if ($receiver == 'thirdparty') // Id of third party
            {
                $sendto = $soc->email;
                $sendtoid = 0;
            }
            else	// Id du contact
            {
                $sendto = $soc->contact_get_property($receiver, 'email');
                $sendtoid = $receiver;
            }
        }

        if (dol_strlen($sendto))
        {

            $from = $fromname . ' <' . $frommail .'>';
            $replyto = $replytoname. ' <' . $replytomail.'>';


			if (empty($subject))
			{ 
				$subject = $langs->transnoentities('SubjectMail');
			}
			 
			$actiontypecode = 'AC_CONFRDV';
			
			$actionmsg=$langs->transnoentities('MailSentBy').' '.$from.' '.$langs->transnoentities('To').' '.$sendto.".\n";
			if ($message)
			{
				$actionmsg.=$langs->transnoentities('MailTopic').": ".$subject."\n";
				$actionmsg.=$langs->transnoentities('TextUsedInTheMessageBody').":\n";
				$actionmsg.=$message;
			}
            


            // Create form object

            $formmail = new FormMail($db);

            $attachedfiles = $formmail->get_attached_files();
            $filepath = $attachedfiles['paths'];
            $filename = $attachedfiles['names'];
            $mimetype = $attachedfiles['mimes'];

            // Send mail
            $mailfile = new CMailFile($subject,$sendto,$from,$message,$filepath,$mimetype,$filename,$sendtocc,'',$deliveryreceipt,-1);
            
            if ($mailfile->error)
            {
                $message = $mailfile->error;
    			$error = true;             
            }
            else
            {
                $result = $mailfile->sendfile();
                if ($result)
                {
                    // Initialisation donnees
                    $rdv->sendtoid		= $sendtoid;
                    $rdv->actiontypecode	= $actiontypecode;
                    $rdv->actionmsg		= $actionmsg;  // Long text
                    $rdv->actionmsg2		= $actionmsg2; // Short text
                    $rdv->fk_element		= $rdv->id;
					$rdv->elementtype	= $rdv->element;

                    // Appel des triggers
                    
                    $interface=new Interfaces($db);
                    $result=$interface->run_triggers('CONFIRM_SENTBYMAIL',$rdv,$user,$langs,$conf);
                    // Fin appel triggers

                    if ($result < 0)
                    {
                        $message = join('<br />', $interface->errors);
                        $error = true;                      
                    }
                    else
                    {
                        // Redirect here
                        $rdv->conf_sent = 1;
                        $rdv->usersend = $user;
                        $rdv->dates = $now;
                        
                        $result = $rdv->update($user); 
                        
                        $message =  $langs->trans('ConfirmationSent');

                    }
                }
                else
                {
                    $message = $mailfile->error;
                    $error = true;                                       
                }
            }
        }
        else
        {
                   
            dol_syslog('Recipient email is empty');
            
            $message = $langs->trans('ErrorMailRecipientIsEmpty');
			$error = true;            
        }
    }
    else
    {
        dol_syslog('Failed to read file: '.$file);
        
        $message = $langs->trans('ErrorCantReadFile',$file);
		$error = true;          
    }

}
				
// Load all actions
if (empty($action) && ($user->rights->confirm->myactions->read || $user->rights->confirm->allactions->read))
{
	$sql = "SELECT s.nom as societe, s.rowid as socid, s.client, a.id, a.datep as dp, a.datep2 as dp2,";
	$sql.= " a.fk_contact, a.note, a.label, c.code as acode, c.libelle, a.fk_user_author as useridauthor, a.fk_user_action as useridtodo,";
	$sql.= " a.fk_user_done as useriddone, co.userconf as useridconf, co.usersend as useridsend, sp.lastname, sp.firstname, co.ref as confref,";
	$sql.= " co.rowid as confid, co.conf_sent, co.conf_built, co.datec as dp3, co.dates as dp4";
	$sql.= " FROM (".MAIN_DB_PREFIX."c_actioncomm as c,";
	if (!$user->rights->societe->client->voir && !$socid) $sql.= " ".MAIN_DB_PREFIX."societe_commerciaux as sc,";
	$sql.= " ".MAIN_DB_PREFIX."actioncomm as a)";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON a.fk_soc = s.rowid AND s.entity IN (0, ".$conf->entity.")";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as sp ON a.fk_contact = sp.rowid";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."confirm as co ON a.id = co.fk_action";
	$sql.= " WHERE c.id = a.fk_action AND a.fk_action IN (1, 5)";// Get all phone calls and appointments (fk_action = 1 or 5)
	$sql.= " AND a.entity = ".$conf->entity;	// To limit to entity

	if ($pid)
	{
	  $sql.= " AND a.fk_project=".$db->escape($pid);  
	} 
	if (!$user->rights->societe->client->voir && !$socid)
	{
	  $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;  
	} 
	if ($socid)
	{
	  $sql.= " AND s.rowid = ".$socid;  
	} 

	if ($type)
	{
	   $sql.= " AND c.id = ".$type; 
	} 
	if ($status == 'tosend') 
	{ 
		$sql.= " AND co.conf_sent = 0"; 
	}
	if ($status == 'sent') 
	{ 
		$sql.= " AND co.conf_sent = 1"; 
	}

	if ($status == 'tobuild') 
	{ 
		$sql.= " AND co.conf_built = 0"; 
	}
	if ($status == 'built') 
	{ 
		$sql.= " AND co.conf_built = 1"; 
	}

	if ($filtera > 0 || $filtert > 0 || $filterd > 0 || $filterc > 0)
	{
		$sql.= " AND (";
		if ($filtera > 0) $sql.= " a.fk_user_author = ".$filtera;
		if ($filtert > 0) $sql.= ($filtera > 0 ? " OR ":"")." a.fk_user_action = ".$filtert;
		if ($filterd > 0) $sql.= ($filtera > 0 || $filtert > 0 ? " OR ":"")." a.fk_user_done = ".$filterd;
		if ($filterc > 0) $sql.= ($filtera > 0 || $filtert > 0 || $filterd > 0 ? " OR ":"")." co.userconf = ".$filterc; 
		if ($filters > 0) $sql.= ($filtera > 0 || $filtert > 0 || $filterd > 0 || $filterc > 0? " OR ":"")." co.usersend = ".$filters;       
		$sql.= ")";
	}

	$sql.= $db->order($sortfield, $sortorder);
	$sql.= $db->plimit($limit+1, $offset);

	$resql = $db->query($sql);

	dol_syslog('Confirm:fiche.php list actions : '.$sql);
	
	if ($resql > 0)
	{
		$num = $db->num_rows($resql);

		$i = 0;

		while ($i < min($num, $limit))
		{
			$obj = $db->fetch_object($resql);

			$canread = 0;
			$cansend = 0;
			$canbuild = 0;
			$cansee = 1;
	
			if (($obj->useridauthor == $user->id && $user->rights->confirm->myactions->read) || $user->rights->confirm->allactions->read)
			{
				$canread = 1;
			}
			
			if (($obj->useridsend == $user->id  && $user->rights->confirm->myactions->send) || $user->rights->confirm->allactions->send)
			{
				$cansend = 1;
			}
			
			if (($obj->userconf == $user->id  && $user->rights->confirm->myactions->create) || $user->rights->confirm->allactions->create)
			{
				$canbuild = 1;
			}											
			
			$linkToSend = $_SERVER['PHP_SELF'].'?action=sendconf&mode=initmail&id='.$obj->id.'&sortfield='.$sortfield.'&sortorder='.$sortorder.$param;        
			$linkToBuild = $_SERVER['PHP_SELF'].'?action=buildconf&id='.$obj->id.'&sortfield='.$sortfield.'&sortorder='.$sortorder.$param;
			$linkToReload = $_SERVER['PHP_SELF'].'?action=buildconf&id='.$obj->id.'&sortfield='.$sortfield.'&sortorder='.$sortorder.$param;
			
			$linkToSee = DOL_URL_ROOT.'/document.php?modulepart=confirm&file='.urlencode($obj->confref.'/'.$obj->confref.'.pdf');
					
			$urlToBuild = '';
			$urlToSend = '';
			$urlToSee = '';
			$urlToReload = '';

			if ($cansee)
			{            
			   $urlToSee =  '<a href="'.$linkToSee.'">'.img_picto($langs->trans("See"),'pdf2').'</a>';                    
			}
			else
			{
				$urlToSee =  img_picto($langs->trans("See"),'pdf2');     
			}  		
		   
			if ($cansend)
			{            
			   $urlToSend = '<a href="'.$linkToSend.'">'.img_picto($langs->trans("Send"),'object_email').'</a>';                    
			}
			else
			{
			   $urlToSend = img_picto($langs->trans("Send"),'object_email');     
			}      

			if ($canbuild)
			{            
			   $urlToBuild = '<a href="'.$linkToBuild.'">'.img_picto($langs->trans("Build"),'off').'</a>';          
			   $urlToReload =  '<a href="'.$linkToReload.'">'.img_picto($langs->trans("Reload"),'refresh').'</a>';                    
			}
			else
			{
			   $urlToBuild = img_picto($langs->trans("Build"),'off');          
			   $urlToReload =  img_picto($langs->trans("Reload"),'refresh');         
			}
			
			
			$actionstatic->id = $obj->id;
			$actionstatic->type_code = $obj->acode;
			$actionstatic->libelle = $obj->label;
	
			$a = new StdClass();
			$a->confid = (empty($obj->confid) ? 0 : $obj->confid);
			$a->ref = (empty($obj->confref) ? '' : $obj->confref);
			$a->late = 0;
			$a->type = $obj->acode;
			$a->soc = '&nbsp;';

			$a->userauthor = '&nbsp;';
			$a->usertodo = '&nbsp;';
			$a->userdone = '&nbsp;';
			$a->userconf = '&nbsp;';
			$a->usersend = '&nbsp;';        
			$a->title = $actionstatic->getNomUrl(1,28);
			
			$a->dateconf = $obj->dp3 ? dol_print_date($db->jdate($obj->dp3),"day") : ''; //Confirmation date 
			$a->datesend = $obj->dp4 ? dol_print_date($db->jdate($obj->dp4),"day") : ''; //Send date       
			$a->dates = $obj->dp ? dol_print_date($db->jdate($obj->dp),"day") : '';//Start date
			$a->datee = $obj->dp2 ? dol_print_date($db->jdate($obj->dp2),"day") : '';//End date
			$a->sent = $obj->conf_sent;
			$a->built = $obj->conf_built;
	
			$a->tosend = $urlToSend;
			$a->tobuild = $urlToBuild;
			$a->tosee = $urlToSee;
			$a->toreload = $urlToReload;
	
			if ($obj->dp && $db->jdate($obj->dp) < ($now - $delay_warning)){
				 $a->late = 1;
			} 
	
			if (!$obj->dp && $obj->dp2 && $db->jdate($obj->dp) < ($now - $delay_warning)){
				$a->late = 1;
			} 
							
			if ($obj->socid)
			{
				$societestatic->id = $obj->socid;
				$societestatic->client = $obj->client;
				$societestatic->name = $obj->societe;
		
				$a->soc = $societestatic->getNomUrl(1,'',10);
			}
	
	
			if ($obj->useridauthor)
			{
				$userstatic=new User($db);
				$userstatic->fetch($obj->useridauthor);
		
				$a->userauthor = $userstatic->getNomUrl(1);
			} 

			if ($obj->useridtodo)
			{
				$userstatic=new User($db);
				$userstatic->fetch($obj->useridtodo);
		
				$a->usertodo = $userstatic->getNomUrl(1); 
			}

			if ($obj->useriddone)
			{
				$userstatic=new User($db);
				$userstatic->fetch($obj->useriddone);
		
				$a->userdone = $userstatic->getNomUrl(1); 
			}

			if ($obj->useridconf)
			{
				$userstatic=new User($db);
				$userstatic->fetch($obj->useridconf);
		
				$a->userconf = $userstatic->getNomUrl(1); 
			}

			if ($obj->useridsend)
			{
				$userstatic=new User($db);
				$userstatic->fetch($obj->useridsend);

				$a->usersend = $userstatic->getNomUrl(1); 
			}
				
			$actions[$i] = $a;
												
			$i++;
		}

		$db->free($resql);   		
	}
	else
	{
		$error = true;
		$message = $db->error;
	}		 	
}
else
{
	$error = true;
	$message = $langs->trans('NotEnoughPermissions');
}	


//Get posted data

$pCid = GETPOST("contactid",'alpha');
$pMobile = GETPOST("mobile", 'alpha');
$pPhone = GETPOST("phone",'alpha');

$pAddress = GETPOST("address",'alpha');
$pTown = GETPOST("town",'alpha');
$pZip = GETPOST("zip",'alpha');


$now = dol_now();

$address = array();
$phones = array();

$addressc = array();
$phonesc = array();






if ($action == 'buildconf')
{      
    if ($soc->id > 0)
    {
        if (!empty($soc->address))
        {
            $ad = new StdClass();
            
            $ad->address = '<b>'.$soc->name .'</b>' ."\n" .$soc->getFullAddress();
            $ad->cid = 0;
            
            $address[] = $ad;
        }
    
        if (!empty($soc->phone))
        {
            $ph = new StdClass();
            
            $ph->phone = '<b>'.$soc->name .'</b>' ."\n" .$soc->phone;
            $ph->cid = 0;
            $ph->mobile = 0;
            
            $phones[] = $ph;
        }
            
        // Get address contacts ?    
        $co = $soc->contact_array();
        
        if (sizeof($co))
        {
            
            foreach($co as $cid => $contact)
            {
                
                $c = new Contact($db);
                $c->fetch($cid);
                
                if (!empty($c->address))
                {
                    $ad = new StdClass();
                    
                    $ad->address = '<b>'.$c->getNomUrl(1) .'</b> ('.$soc->name .')' ."\n" .$c->getFullAddress();
                    $ad->cid = $cid;
                    
                    $addressc[] = $ad;                
                }
                
                if (!empty($c->phone_pro))
                {
                    $ph = new StdClass();
                    
                    $ph->phone = '<b>'.$c->getNomUrl(1) .'</b>' ."\n" .$c->phone_pro .' ('.$langs->trans("PhonePro").')';
                    $ph->cid = $cid;
                    $ph->mobile = 0;
                    
                    $phonesc[] = $ph;
                }
                
                if (!empty($c->phone_mobile))
                {
                    $ph = new StdClass();
                    
                    $ph->phone = '<b>'.$c->getNomUrl(1) .'</b>' ."\n" .$c->phone_mobile.' ('.$langs->trans("PhoneMobile").')';
                    $ph->cid = $cid;
                    $ph->mobile = 1;
                    
                    $phonesc[] = $ph;
                }                        
            }
        }        
    }
    
    $isPhoneCall = $actioncomm->phonecall;
      
    $model = new ModeleConfirm();
    $liste = $model->liste_modeles($db);
        
    require_once('tpl/confirm.build.tpl.php'); 
}
else if ($action == 'sendconf')
{
	$mode = GETPOST('mode');
	
    $ref = dol_sanitizeFileName($rdv->ref);
    $file = $conf->global->CONFIRM_DIR_OUTPUT . '/' . $ref . '/' . $ref . '.pdf';

    // Construit PDF si non existant
    if (! is_readable($file))
    {
        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id');
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $rdv->action->societe->default_lang;
        
        if (! empty($newlang))
        {
            $outputlangs = new Translate("",$conf);
            $outputlangs->setDefaultLang($newlang);
        }
        
        $result = confirm_pdf_create($db, $rdv, '', $rdv->modelpdf, $outputlangs);
    }

    // Cree l'objet formulaire mail
    
    $formmail = new FormMail($db);
    $formmail->fromtype = 'user';
    $formmail->fromid   = $user->id;
    $formmail->fromname = $user->getFullName($langs);
    $formmail->frommail = $conf->global->CONFIRM_EMAIL_FROM;
    $formmail->withfrom = 1;
    $formmail->withto = 1;
    $formmail->withtosocid = $soc->id;
    $formmail->withtocc=1;
    $formmail->withtoccsocid=0;
    $formmail->withtoccc = $conf->global->MAIN_EMAIL_USECCC;
    $formmail->withtocccsocid = 0;
    $formmail->withtopic = $langs->transnoentities('SubjectMail');
    $formmail->withfile = 2;
    $formmail->withbody = $langs->transnoentities('BodyMail');
    $formmail->withdeliveryreceipt = 0;
    $formmail->withcancel = 1;

    $formmail->param['action']='send';
    $formmail->param['id'] = $actioncomm->id;
    $formmail->param['returnurl']= $_SERVER["PHP_SELF"].'?id='.$actioncomm->id;

    // Init list of files

    

    if (! empty($mode) && $mode=='initmail')
    {
        $formmail->clear_attached_files();
        $formmail->add_attached_files($file, dol_sanitizeFilename($ref.'.pdf'), 'application/pdf'); 
    }   

    require_once('./tpl/confirm.send.tpl.php');
}
else
{
	$h = 0;
	$head = array();

	$current_head = 'confirm';
	
	$head[$h][0] = dol_buildpath('/confirm/confirm.php',1).'?param='.$param;
	$head[$h][1] = $langs->trans("ConfRDV");
	$head[$h][2] = 'confirm';

	require_once('./tpl/confirm.index.tpl.php');
}

$db->close();

?>
