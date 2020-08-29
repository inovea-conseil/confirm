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
 *	\file       htdocs/includes/modules/confrdv/doc/pdf_langouste.modules.php
 *	\ingroup    confrdv
 *	\brief      File of class to generate appointment confirmation from langouste model
 */



dol_include_once("/confirm/core/modules/confirm/modules_confirm.php");

require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');
/**
 *	\class      pdf_langouste
 *	\brief      Classe permettant de generer les confirmations de rendez-vous au modele Langouste
 */

class pdf_langouste extends ModeleConfirm
{
	var $emetteur;	// Objet societe qui emet

    var $phpmin = array(4,3,0); // Minimum version of PHP required by module
    var $version = 'dolibarr';


	/**
	 *		Constructor
	 *		@param		db		Database access handler
	 */
	function pdf_langouste($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("confirm@confirm");

		$this->db = $db;
		$this->name = "langouste";
		$this->description = $langs->trans('PDFLangousteDescription');

		// Dimension page pour format A4
		$this->type = 'pdf';
		$this->page_largeur = 210;
		$this->page_hauteur = 297;
		$this->format = array($this->page_largeur, $this->page_hauteur);
		$this->marge_gauche=10;
		$this->marge_droite=10;
		$this->marge_haute=10;
		$this->marge_basse=10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_multilang = 1;               // Dispo en plusieurs langues

		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->pays_code){
		      $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // By default, if was not defined
		} 
	}


	/**
     *  Function to build pdf onto disk
     *  @param      object          Id of object to generate
     *  @param      outputlangs     Lang output object
     *  @param      srctemplatepath Full path of source filename for generator using a template file
     *  @return     int             1=OK, 0=KO
	 */
	function write_file($object, $outputlangs, $srctemplatepath='')
	{
		global $user, $langs, $conf, $mysoc;

		if (! is_object($outputlangs)) $outputlangs = $langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!class_exists('TCPDF')) $outputlangs->charset_output='UTF-8';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("confirm@confirm");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if ($conf->global->CONFIRM_DIR_OUTPUT)
		{

			// Definition of $dir and $file
            
			if ($object->specimen)
			{
				$dir = $conf->global->CONFIRM_DIR_OUTPUT;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->global->CONFIRM_DIR_OUTPUT . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}
			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{

                $pdf = pdf_getInstance($this->format);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("ConfRDV"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("ConfRDV"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// New page
				$pdf->AddPage();
				$pagenb++;
                
				$this->_pagehead($pdf, $object, 1, $outputlangs);


        
                // Show main text
        		$posy = 140;
        		$posx = $this->marge_gauche;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);
        		$pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('DearClientPDF')), 0, 'L');
             
        		$posy = 150;
        		$posx = $this->marge_gauche;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);
                if ($object->action->usertodo->id){
                    $usertodo = new User($this->db);
                    $result = $usertodo->fetch($object->action->usertodo->id);
                    
                    if ($result > 0){
                        if ($object->action->phonecall){
                            $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextPhone', $usertodo->getFullName($outputlangs))), 0, 'L');
                        }else{
                            $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextAppointment', $usertodo->getFullName($outputlangs))), 0, 'L');
                        }                         
                    }else{
                        if ($object->action->phonecall){
                            $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextPhoneAlt')), 0, 'L');
                        }else{
                            $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextAppointmentAlt')), 0, 'L');
                        }                         
                    }
                }else{
                    if ($object->action->phonecall){
                        $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextPhoneAlt')), 0, 'L');
                    }else{
                        $pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('MainTextAppointmentAlt')), 0, 'L');
                    }                     
                }
               
                  
        		$posy = 160;
        		$posx = $this->marge_gauche+40;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);
        		$pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('TheDate')), 0, 'L');

        		$posy = 160;
        		$posx = $this->marge_gauche+80;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','B', $default_font_size);
        		$pdf->MultiCell(96,4, $outputlangs->convToOutputCharset(dol_print_date($object->action->datep, "dayhourtext")), 0, 'L');

        		$posy = 170;
        		$posx = $this->marge_gauche+40;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);

                if ($object->action->phonecall){
                    $pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('AtPhone')), 0, 'L');
                }else{
                    $pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('AtAddress')), 0, 'L');
                }
                                                 
        		$posy = 170;
        		$posx = $this->marge_gauche+80;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);

                if ($object->action->phonecall){
                    $pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($object->phone), 0, 'L');
                }else{
                    $pdf->MultiCell(96,4, $outputlangs->convToOutputCharset($object->address), 0, 'L');
                }

        		$posy = 195;
        		$posx = $this->marge_gauche;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);
        		$pdf->MultiCell(190,4, $outputlangs->convToOutputCharset($outputlangs->transnoentities('EndText')), 0, 'L');
                 
                 //Last, put a sign
        		$posy = 215;
        		$posx = 140;
        		$pdf->SetXY($posx+2,$posy+3);
        		$pdf->SetFont('','', $default_font_size);
        		$pdf->MultiCell(60,4, $outputlangs->convToOutputCharset($mysoc->name), 0, 'L');
                                               
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');
				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","CONFIRM_DIR_OUTPUT");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}


	/**
	 *   	\brief      Show header of page
	 *      \param      pdf             Object PDF
	 *      \param      object          Object invoice
	 *      \param      showaddress     0=no, 1=yes
	 *      \param      outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress=1, $outputlangs)
	{
		global $conf, $langs;

		$outputlangs->load("main");
		$outputlangs->load("confirm@confirm");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);


		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, 24);	// width=0 (auto), max height=24
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B',$default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$this->emetteur->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

        // Sender properties
		$carac_emetteur = pdf_build_address($outputlangs,$this->emetteur);

        // Show sender
		$posy = 40;
		$posx = $this->marge_gauche;


        // Show sender name
		$pdf->SetXY($posx+2,$posy+3);
		$pdf->SetFont('','B', $default_font_size);
		$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');

        // Show sender information
		$pdf->SetXY($posx+2,$posy+8);
		$pdf->SetFont('','', $default_font_size - 1);
		$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');
        
        // Show client
		$posy = 90;
		$posx = $this->marge_gauche+120; 
               
        if ($object->action->societe->id > 0){
            // Show client name
    		$pdf->SetXY($posx+2,$posy+3);
    		$pdf->SetFont('','B', $default_font_size);
    		$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($object->action->societe->name), 0, 'L');  
                  
            // Show client address
    		$pdf->SetXY($posx+2,$posy+8);
    		$pdf->SetFont('','', $default_font_size - 1);

            $pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($object->action->societe->getFullAddress()), 0, 'L');
        }
		              

        // Show date
		$pdf->SetXY($posx+2,$posy+28);
		$pdf->SetFont('','', $default_font_size - 1);
        $dateText = $outputlangs->convToOutputCharset($this->emetteur->town.', '.$outputlangs->transnoentities('TheDate').' '.dol_print_date($object->datec, "daytext"));
		
        $pdf->MultiCell(80, 4, $dateText, 0, 'L'); 
                        
	}

	/**
	 *   	\brief      Show footer of page
	 *   	\param      pdf     		PDF factory
	 * 		\param		object			Object
	 *      \param      outputlangs		Object lang for output
	 * 		\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
		return pdf_pagefoot($pdf,$outputlangs,'',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

}

?>
