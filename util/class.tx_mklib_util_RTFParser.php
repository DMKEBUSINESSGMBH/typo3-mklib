<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/*
Rich Text Format - Parsing Class
================================
(c) 2000 Markus Fischer
<mfischer@josefine.ben.tuwien.ac.at>
http://josefine.ben.tuwien.ac.at/~mfischer/
Latest versions of this class can always be found at
http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/rtfclass.phps
Testing suite is available at
http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/
License: GPLv2
Specification:
http://msdn.microsoft.com/library/default.asp?URL=/library/specs/rtfspec.htm
General Notes:
==============
Unknown or unspupported control symbols are silently gnored
Group stacking is still not supported :(
group stack logic implemented; however not really used yet
Example on how to use this class:
=================================
$r = new rtf( stripslashes( $rtf));
$r->output( "xml");
$r->parse();
if( count( $r->err) == 0) // no errors detected
echo $r->out;

History:
========
Sat Nov 25 09:52:12 CET 2000 mfischer
First version which has useable but only well-formed xml output; rtf
data structure is only logically rebuild, no real parsing yet
Mon Nov 27 16:17:18 CET 2000 mfischer
Wrote handler for \plain control word (thanks to Peter Kursawe for this
one)
Tue Nov 28 02:22:16 CET 2000 mfischer
Implemented alignment (left, center, right) with HTML <DIV .. tags
Also implemented translation for < and > character when outputting html or xml
Mon Oct 25 14:15:03 CET 2004 smanciles
Implemented parsing of special characteres for spanish and catalan (ú�...)
Remarks:
========
This class and all work done here is dedicated to Tatjana.
*/
/*
 Rich Text Format - Parsing Class
 ================================
 (c) 2000 Markus Fischer
 <mfischer@josefine.ben.tuwien.ac.at>
 http://josefine.ben.tuwien.ac.at/~mfischer/
 Latest versions of this class can always be found at
 http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/rtfclass.phps
 Testing suite is available at
 http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/
 License: GPLv2
 Specification:
 http://msdn.microsoft.com/library/default.asp?URL=/library/specs/rtfspec.htm
 General Notes:
 ==============
 Unknown or unspupported control symbols are silently gnored
 Group stacking is still not supported :(
 group stack logic implemented; however not really used yet
 Example on how to use this class:
 =================================
 $r = new rtf( stripslashes( $rtf));
 $r->output( "xml");
 $r->parse();
 if( count( $r->err) == 0) // no errors detected
 echo $r->out;
 
 History:
 ========
 Sat Nov 25 09:52:12 CET 2000 mfischer
 First version which has useable but only well-formed xml output; rtf
 data structure is only logically rebuild, no real parsing yet
 Mon Nov 27 16:17:18 CET 2000 mfischer
 Wrote handler for \plain control word (thanks to Peter Kursawe for this
 one)
 Tue Nov 28 02:22:16 CET 2000 mfischer
 Implemented alignment (left, center, right) with HTML <DIV .. tags
 Also implemented translation for < and > character when outputting html or xml
 Mon Oct 25 14:15:03 CET 2004 smanciles
 Implemented parsing of special characteres for spanish and catalan (ú�...)
 Remarks:
 ========
 This class and all work done here is dedicated to Tatjana.
 */
/**
 * RTF Parser
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_RTFParser {
	protected $rtf;		// rtf core stream
	protected $len;		// length in characters of the stream (get performace due avoiding calling strlen everytime)
	protected $err = array();		// array of error message, no entities on no error

	protected $wantXML;	// convert to XML
	protected $wantHTML;	// convert to HTML

	// the only variable which should be accessed from the outside
	protected $out;		// output data stream (depends on which $wantXXXXX is set to true
	protected $outstyles;	// htmlified styles (generated after parsing if wantHTML
	protected $styles;	// if wantHTML, stylesheet definitions are put in here

	// internal parser variables --------------------------------
	// control word variables
	protected $cword;		// holds the current (or last) control word, depending on $cw
	protected $cw;		// are we currently parsing a control word ?
	protected $cfirst;	// could this be the first character ? so watch out for control symbols

	protected $flags = array();		// parser flags

	protected $queue;		// every character which is no sepcial char, not belongs to a control word/symbol; is generally considered being 'plain'

	protected $stack = array();	// group stack
	
	/**
	 * Sonderzeichen, die ersetzt werden müssen
	 
	 * @var array
	 */
	protected $aSpecialChars = array(
			"c1" => "&Aacute;",
			"e1" => "&aacute;",
			"c0" => "&Agrave;",
			"e0" => "&agrave;",
			"c9" => "&Eacute;",
			"e9" => "&eacute;",
			"c8" => "&Egrave;",
			"e8" => "&egrave;",
			"cd" => "&Iacute;",
			"ed" => "&iacute;",
			"cc" => "&Igrave;",
			"ec" => "&igrave;",
			"d3" => "&Oacute;",
			"f3" => "&oacute;",
			"d2" => "&Ograve;",
			"f2" => "&ograve;",
			"da" => "&Uacute;",
			"fa" => "&uacute;",
			"d9" => "&Ugrave;",
			"f9" => "&ugrave;",
			"80" => "&#8364;",
			"d1" => "&Ntilde;",
			"f1" => "&ntilde;",
			"c7" => "&Ccedil;",
			"e7" => "&ccedil;",
			"e4" => "&auml;", // ä
			"c4" => "&Auml", // Ä
			"f6" => "&ouml", // ö
			"d6" => "&Ouml", // Ö
			"dc" => "&Uuml;",
			"fc" => "&uuml;",
			"bf" => "&#191;",
			"a1" => "&#161;",
			"b7" => "&middot;",
			"a9" => "&copy;",
			"ae" => "&reg;",
			"ba" => "&ordm;",
			"aa" => "&ordf;",
			"b2" => "&sup2;",
			"b3" => "&sup3;",
			"df" => "&szlig;",
	);

	/* keywords which don't follw the specification (used by Word '97 - 2000) */
	// not yet used
	protected $control_exception = array(
		"clFitText",
		"clftsWidth(-?[0-9]+)?",
		"clNoWrap(-?[0-9]+)?",
		"clwWidth(-?[0-9]+)?",
		"tdfrmtxtBottom(-?[0-9]+)?",
		"tdfrmtxtLeft(-?[0-9]+)?",
		"tdfrmtxtRight(-?[0-9]+)?",
		"tdfrmtxtTop(-?[0-9]+)?",
		"trftsWidthA(-?[0-9]+)?",
		"trftsWidthB(-?[0-9]+)?",
		"trftsWidth(-?[0-9]+)?",
		"trwWithA(-?[0-9]+)?",
		"trwWithB(-?[0-9]+)?",
		"trwWith(-?[0-9]+)?",
		"spectspecifygen(-?[0-9]+)?"
	);

	protected $charset_table = array(
		"0"	=>	"ANSI",
		"1"	=>	"Default",
		"2"	=>	"Symbol",
		"77" =>	"Mac",
		"128" =>	"Shift Jis",
		"129" =>	"Hangul",
		"130" =>	"Johab",
		"134" =>	"GB2312",
		"136" =>	"Big5",
		"161" =>	"Greek",
		"162" =>	"Turkish",
		"163" =>	"Vietnamese",
		"177" =>	"Hebrew",
		"178" =>	"Arabic",
		"179" =>	"Arabic Traditional",
		"180" =>	"Arabic user",
		"181" =>	"Hebrew user",
		"186" =>	"Baltic",
		"204" =>	"Russion",
		"222" =>	"Thai",
		"238" =>	"Eastern European",
		"255" =>	"PC 437",
		"255" =>	"OEM"
	);

	/* note: the only conversion table used */
	protected $fontmodifier_table = array(
		"bold"	=>	"b",
		"italic"	=> "i",
		"underlined"	=> "u",
		"strikethru"	=> "strike"
	);
	
	/*
	 Class Constructor:
	 Takes as argument the raw RTF stream
	 (Note under certain circumstances the stream has to be stripslash'ed before handling over)
	 Initialises some class-global variables
	 
	 @param $cleanRtf - sollen informationen zur colortable, fonttable und meta-infos vor dem
	 parsen entfernt werden?
	 */
	public function __construct( $data, $cleanRtf = false) {
		//da der text evtl. bereinigt wird, müssen wir vorher
		//wissen ob es Wingdings in der font table gibt und 
		//welchen index Wingdings einnimmt um die Symbole
		//durch einen Sonderzeichen Marker zu ersetzten
		
		//teil der Fontable mit Wingdings
		preg_match('/f[0-9]*.fnil *?Wingdings/i', $data, $matches);
		//jetzt brauchen wir noch den index von Wingdings in der fonttable
		//und ersetzten anschliessend alle Wingdings Sonderzeichen
		if(!empty($matches)){
			preg_match('/[0-9]+/', $matches[0], $aWingdingsFontTableIndex);
			//jetzt ersetzen aber ohne die magic quotes einstellungen zu beachten			
			$data = preg_replace(
				'/\{.f'.$aWingdingsFontTableIndex[0].'\\\\[a-z0-9\\\\]+ *(.) *\}/i',
				'###'.tx_mklib_util_MiscTools::getSpecialCharMarker().'$1###', 
				$data
			);
		}
		
		//@todo: warum ist es nötig den text zu bereinigen? wäre es nicht besser
		//den text zu reparieren damit z.B. evtl. das Sonderzeichen parsen funktioniert
		//womit die eigene ersetzung am ende von parse() wegfallen würde
		if($cleanRtf){
			//Fontable entfernen
			$data = preg_replace('/{.fonttbl.*?;}}/i','',$data);
			//Colortable entfernen
			$data = preg_replace('/{.colortbl.*?}/i','',$data);
			//Meta-Infos entfernen
			$data = preg_replace('/{.info.*?}}/i','',$data);
		}
		$this->len = strlen( $data);
		$this->rtf = $data;

		$this->wantXML = false;
		$this->wantHTML = false;

		$this->out = "";
		$this->outstyles = "";
		$this->styles = array();
		$this->text = "";

		if( $this->len == 0)
			array_push( $this->err, "No data in stream found");
	}

	protected function parserInit() {
		/*
		 Default values according to the specs
		 */
		$this->flags = array(
			"fontsize"	=>	24,
			"beginparagraph"	=> true
		);
	}

	/*
	 Sets the output type
	 */
	public function setOutputType( $typ) {
		switch( $typ) {
			case "xml": $this->wantXML = true; break;
			case "html": $this->wantHTML = true; break;
			default: break;//plain text
		}
	}

	protected function parseControl( $control, $parameter) {
		switch( $control) {
			// font table definition start
			case "fonttbl":
				$this->flags["fonttbl"] = true;	// signal fonttable control words they are allowed to behave as expected
				break;
				// define or set font
			case "f":
				if( $this->flags["fonttbl"]) {	// if its set, the fonttable definition is written to; else its read from
					$this->flags["fonttbl_current_write"] = $parameter;
				} else {
					$this->flags["fonttbl_current_read"] = $parameter;
				}
				break;
			case "fcharset":
				// this is for preparing flushQueue; it then moves the Queue to $this->fonttable .. instead to formatted output
				$this->flags["fonttbl_want_fcharset"] = $parameter;
				break;
			case "fs":
				// sets the current fontsize; is used by stylesheets (which are therefore generated on the fly
				$this->flags["fontsize"] = $parameter;
				break;
				// handle alignment
			case "qc":
				$this->flags["alignment"] = "center";
				break;
			case "qr":
				$this->flags["alignment"] = "right";
				break;
				// reset paragraph settings ( only alignment)
			case "pard":
				$this->flags["alignment"] = "";
				break;
				// define new paragraph (for now, thats a simple break in html)
			case "par":
				// begin new line
				$this->flags["beginparagraph"] = true;
				if( $this->wantHTML) {
					$this->out .= "</div>";
				}
				break;
				// bold
			case "bnone":
				$parameter = "0";
			case "b":
				// haven'y yet figured out WHY I need a (string)-cast here ... hm
				if( (string)$parameter == "0")
				$this->flags["bold"] = false;
				else
				$this->flags["bold"] = true;
				break;

				// underlined
			case "ulnone":
				$parameter = "0";
			case "ul":
				if( (string)$parameter == "0")
				$this->flags["underlined"] = false;
				else
				$this->flags["underlined"] = true;
				break;

				// italic
			case "inone":
				$parameter = "0";
			case "i":
				if( (string)$parameter == "0")
				$this->flags["italic"] = false;
				else
				$this->flags["italic"] = true;
				break;

				// strikethru
			case "strikenone":
				$parameter = "0";
			case "strike":
				if( (string)$parameter == "0")
				$this->flags["strikethru"] = false;
				else
				$this->flags["strikethru"] = true;
				break;

				// reset all font modifiers and fontsize to 12
			case "plain":
				$this->flags["bold"] = false;
				$this->flags["italic"] = false;
				$this->flags["underlined"] = false;
				$this->flags["strikethru"] = false;
				$this->flags["fontsize"] = 12;

				$this->flags["subscription"] = false;
				$this->flags["superscription"] = false;
				break;

				// sub and superscription
			case "subnone":
				$parameter = "0";
			case "sub":
				if( (string)$parameter == "0")
				$this->flags["subscription"] = false;
				else
				$this->flags["subscription"] = true;
				break;

			case "supernone":
				$parameter = "0";
			case "super":
				if( (string)$parameter == "0")
				$this->flags["superscription"] = false;
				else
				$this->flags["superscription"] = true;
				break;
					
		}
	}

	/*
	 Dispatch the control word to the output stream
	 */
	protected function flushControl() {
//		if( ereg( "^([A-Za-z]+)(-?[0-9]*) ?$", $this->cword, $match)) {
		if( preg_match( "/^([A-Za-z]+)(-?[0-9]*) ?$/", $this->cword, $match)) {

			$this->parseControl( $match[1], $match[2]);

			if( $this->wantXML) {
				$this->out.="<control word=\"".$match[1]."\"";
				if( strlen( $match[2]) > 0)
				$this->out.=" param=\"".$match[2]."\"";
				$this->out.="/>";
			}
		}
	}

	/*
	 If output stream supports comments, dispatch it
	 */
	protected function flushComment( $comment) {
		if( $this->wantXML || $this->wantHTML) {
			$this->out.="<!-- ".$comment." -->";
		}
	}

	/*
	 Dispatch start/end of logical rtf groups
	 (not every output type needs it; merely debugging purpose)
	 */
	protected function flushGroup( $state) {
		if( $state == "open") {

			/* push onto the stack */
			array_push( $this->stack, $this->flags);

			if( $this->wantXML)
			$this->out.="<group>";
		}
		if( $state == "close") {

			/* pop from the stack */
			$this->last_flags = $this->flags;
			$this->flags = array_pop( $this->stack);

			$this->flags["fonttbl_current_write"] = ""; // on group close, no more fontdefinition will be written to this id
			// this is not really the right way to do it !
			// of course a '}' not necessarily donates a fonttable end; a fonttable
			// group at least *can* contain sub-groups
			// therefore an stacked approach is heavily needed
			$this->flags["fonttbl"] = false; // no matter what you do, if a group closes, its fonttbl definition is closed too

			if( $this->wantXML)
			$this->out.="</group>";
		}
	}

	protected function flushHead() {
		if( $this->wantXML)
		$this->out.="<rtf>";
	}

	protected function flushBottom() {
		if( $this->wantXML)
		$this->out.="</rtf>";
	}


	protected function checkHtmlSpanContent( $command) {
		reset( $this->fontmodifier_table);
		while( list( $rtf, $html) = each( $this->fontmodifier_table)) {
			if( $this->flags[$rtf] == true) {
				if( $command == "start")
				$this->out .= "<".$html.">";
				else
				$this->out .= "</".$html.">";
			}
		}
	}
	/*
	 flush text in queue
	 */
	protected function flushQueue() {
		if( strlen( $this->queue)) {
			// processing logic
			//if( ereg( "^[0-9]+$", $this->flags["fonttbl_want_fcharset"])) {
			if( preg_match( "/^[0-9]+$/", $this->flags["fonttbl_want_fcharset"])) {
				$this->fonttable[$this->flags["fonttbl_want_fcharset"]]["charset"] = $this->queue;
				$this->flags["fonttbl_want_fcharset"] = "";
				$this->queue = "";
			}

			// output logic
			if( strlen( $this->queue)) {
				/*
				 Everything which passes this is (or, at leat, *should*) be only outputted plaintext
				 Thats why we can safely add the css-stylesheet when using wantHTML
				 */
				if( $this->wantXML){
					$this->out.= "<plain>".$this->queue."</plain>";
				}elseif( $this->wantHTML) {
					// only output html if a valid (for now, just numeric;) fonttable is given
					//if( ereg( "^[0-9]+$", $this->flags["fonttbl_current_read"])) {
					if( preg_match( "/^[0-9]+$/", $this->flags["fonttbl_current_read"])) {
							
						if( $this->flags["beginparagraph"] == true) {
							$this->flags["beginparagraph"] = false;
							$this->out .= "<div align=\"";
							switch( $this->flags["alignment"]) {
								case "right":
									$this->out .= "right";
									break;
								case "center":
									$this->out .= "center";
									break;
								case "left":
								default:
									$this->out .= "left";
							}
							$this->out .= "\">";
						}
							
						/* define new style for that span */
						$this->styles["f".$this->flags["fonttbl_current_read"]."s".$this->flags["fontsize"]] = "font-family:".$this->fonttable[$this->flags["fonttbl_current_read"]]["charset"]." font-size:".$this->flags["fontsize"].";";
						/* write span start */
						$this->out .= "<span class=\"f".$this->flags["fonttbl_current_read"]."s".$this->flags["fontsize"]."\">";

						/* check if the span content has a modifier */
						$this->checkHtmlSpanContent( "start");
						/* write span content */
						$this->out .= $this->queue;
						/* close modifiers */
						$this->checkHtmlSpanContent( "stop");
						/* close span */
					"</span>";
					}
				}else{//plain output
					$this->out.= $this->queue;
				}
				$this->queue = "";
			}
		}
	}

	/*
	 handle special charactes like \'ef
	 */
	protected function flushSpecial( $special) {
		if( strlen( $special) == 2) {
			if( $this->wantXML)
				$this->out .= "<special value=\"".$special."\"/>";
			elseif( $this->wantHTML) {
				$this->out .= "<special value=\"".$special."\"/>";
				switch( $special) {
					case "c1": $this->out .= "&Aacute;"; break;
					case "e1": $this->out .= "&aacute;"; break;
					case "c0": $this->out .= "&Agrave;"; break;
					case "e0": $this->out .= "&agrave;"; break;
					case "c9": $this->out .= "&Eacute;"; break;
					case "e9": $this->out .= "&eacute;"; break;
					case "c8": $this->out .= "&Egrave;"; break;
					case "e8": $this->out .= "&egrave;"; break;
					case "cd": $this->out .= "&Iacute;"; break;
					case "ed": $this->out .= "&iacute;"; break;
					case "cc": $this->out .= "&Igrave;"; break;
					case "ec": $this->out .= "&igrave;"; break;
					case "d3": $this->out .= "&Oacute;"; break;
					case "f3": $this->out .= "&oacute;"; break;
					case "d2": $this->out .= "&Ograve;"; break;
					case "f2": $this->out .= "&ograve;"; break;
					case "da": $this->out .= "&Uacute;"; break;
					case "fa": $this->out .= "&uacute;"; break;
					case "d9": $this->out .= "&Ugrave;"; break;
					case "f9": $this->out .= "&ugrave;"; break;
					case "80": $this->out .= "&#8364;"; break;
					case "d1": $this->out .= "&Ntilde;"; break;
					case "f1": $this->out .= "&ntilde;"; break;
					case "c7": $this->out .= "&Ccedil;"; break;
					case "e7": $this->out .= "&ccedil;"; break;
					case "e4": $this->out .= "&auml;"; break; // ä
					case "c4": $this->out .= "&Auml";break; // Ä
					case "f6": $this->out .= "&ouml"; break; // ö
					case "d6": $this->out .= "&Ouml"; break; // Ö
					case "dc": $this->out .= "&Uuml;"; break;
					case "fc": $this->out .= "&uuml;"; break;
					case "bf": $this->out .= "&#191;"; break;
					case "a1": $this->out .= "&#161;"; break;
					case "b7": $this->out .= "&middot;"; break;
					case "a9": $this->out .= "&copy;"; break;
					case "ae": $this->out .= "&reg;"; break;
					case "ba": $this->out .= "&ordm;"; break;
					case "aa": $this->out .= "&ordf;"; break;
					case "b2": $this->out .= "&sup2;"; break;
					case "b3": $this->out .= "&sup3;"; break;
				}
			} else {
				switch( $special) {
					case "e4": $this->out .= "ä"; break; // ä
					case "c4": $this->out .= "Ä";break; // Ä
					case "f6": $this->out .= "ö"; break; // ö
					case "d6": $this->out .= "Ö"; break; // Ö
					case "fc": $this->out .= "ü"; break; // ü
					case "dc": $this->out .= "Ü"; break; // Ü
				}
			}
		}
	}

	/*
	 Output errors at end
	 */
	protected function flushErrors() {
		if( count( $this->err) > 0) {
			if( $this->wantXML) {
				$this->out .= "<errors>";
				while( list($num,$value) = each( $this->err)) {
					$this->out .= "<message>".$value."</message>";
				}
				$this->out .= "</errors>";
			}
		}
	}

	protected function makeStyles() {
		$this->outstyles = "<style type=\"text/css\"><!--\n";
		reset( $this->styles);
		while( list( $stylename, $styleattrib) = each( $this->styles)) {
			$this->outstyles .= ".".$stylename." { ".$styleattrib." }\n";
		}
		$this->outstyles .= "--></style>\n";
	}

	/*
	 finally ..

	 How this parser (is supposed) to work:
	 ======================================
	 This parse simple starts at the beginning of the rtf core stream, catches every
	 controlling character {,} and \, automatically builds control words and control
	 symbols during his livetime, trashes every other character into the plain text
	 queue
	 */
	public function parse() {

		$this->parserInit();

		$i = 0;
		$this->cw= false;	// flag if control word is currently parsed
		$this->cfirst = false;// first control character ?
		$this->cword = "";	// last or current control word ( depends on $this->cw

		$this->queue = "";		// plain text data found during parsing

		$this->flushHead();

		while( $i < $this->len) {
			switch( $this->rtf[$i]) {
				case "{":	if( $this->cw) {
					$this->flushControl();
					$this->cw= false; $this->cfirst = false;
				} else
				$this->flushQueue();

				$this->flushGroup( "open");
				break;
				case "}":	if( $this->cw) {
					$this->flushControl();
					$this->cw= false; $this->cfirst = false;
				} else
				$this->flushQueue();

				$this->flushGroup( "close");
				break;
				case "\\":	if( $this->cfirst) {	// catches '\\'
					$this->queue .= '\\';
					$this->cfirst = false;
					$this->cw= false;
					break;
				}
				if( $this->cw) {
					$this->flushControl();
				} else
				$this->flushQueue();
				$this->cw = true;
				$this->cfirst = true;
				$this->cword = "";
				break;
				default:
					if( (ord( $this->rtf[$i]) == 10) || (ord($this->rtf[$i]) == 13)) break; // eat line breaks
					if( $this->cw) {	// active control word ?
						/*
						Watch the RE: there's an optional space at the end which IS part of
						the control word (but actually its ignored by flushControl)
						*/
						if( preg_match( '/^[a-zA-Z0-9-]?$/', $this->rtf[$i])) { // continue parsing
						//if( ereg( "^[a-zA-Z0-9-]?$", $this->rtf[$i])) { // continue parsing
							$this->cword .= $this->rtf[$i];
							$this->cfirst = false;
						} else {
							/*
							 Control word could be a 'control symbol', like \~ or \* etc.
								*/
							$specialmatch = false;
							if( $this->cfirst) {
								if( $this->rtf[$i] == '\'') { // expect to get some special chars
									$this->flushQueue();
									$this->flushSpecial( $this->rtf[$i+1].$this->rtf[$i+2]);
									$i+=2;
									$specialmatch = true;
									$this->cw = false; $this->cfirst = false; $this->cword = "";
								} else
								//if( ereg( "^[{}\*]$", $this->rtf[$i])) {
								if( preg_match( "^[{}\*]$", $this->rtf[$i])) {
									$this->flushComment( "control symbols not yet handled");
									$specialmatch = true;
								}
								$this->cfirst = false;
							} else {
								if( $this->rtf[$i] == ' ') {	// space delimtes control words, so just discard it and flush the controlword
									$this->cw = false;
									$this->flushControl();
									break;
								}
							}
							if( ! $specialmatch) {
								$this->flushControl();
								$this->cw = false; $this->cfirst = false;
								/*
								 The current character is a delimeter, but is NOT
								 part of the control word so we hop one step back
								 in the stream and process it again
									*/
								$i--;
							}
						}
					} else {
						// < and > need translation before putting into queue when XML or HTML is wanted
						if( ($this->wantHTML) || ($this->wantXML)) {
							switch( $this->rtf[$i]) {
								case "<":
									$this->queue .= "&lt;";
									break;
								case ">":
									$this->queue .= "&gt;";
									break;
								default:
									$this->queue .= $this->rtf[$i];
									break;
							}
						} else
						$this->queue .= $this->rtf[$i];
					}

			}
			$i++;
		}
		$this->flushQueue();
		$this->flushErrors();
		$this->flushBottom();

		if( $this->wantHTML) {
			$this->makeStyles();
		}
		
		//jetzt ersetzen wir noch alle übrigen Sonderzeichen da das während dem Parsen
		//nicht klappt für die Beispiele in den Tests. Wenn aber die Daten bspw. direkt
		//vom JJK Webservice kommen, werden die umlaute etc. schon vorher ersetzt
		//@TODO: warum klappt das nicht?
		foreach ($this->aSpecialChars as $sSpecialChar => $aSpecialCharReplacement) {
			$this->out = str_replace(
				'\''.$sSpecialChar,
				$aSpecialCharReplacement, 
				$this->out
			);
		}
		
		return $this->out;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_RTFParser.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_RTFParser.php']);
}
