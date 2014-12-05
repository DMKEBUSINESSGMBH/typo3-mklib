<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib
 *
 *	@TODO: unbedingt umstellen,
 *		als standalone liefert es falsche ergebnisse.
 *		typo3 ändert intern das error reporting noch mehrmals!
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
?>

<!DOCTYPE html
	PUBLIC '-//W3C//DTD XHTML 1.1//EN'
	'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
	<head>
		<title>Error reporting test</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex, nofollow" />
		<style type="text/css">

			body {
				color:#66666;
				font:normal 100.1%/150.1% arial,sans-serif;
				text-align:left;
			}

			h1 {
				margin: 25px 0;
			}

			p {
				margin: 10px 0;
				color: #666666;
			}

			em {
				color: #333333;
			}
			
			.errormessage {
				color: #990000;
			}

			hr {
				border: 1px solid #999999;
			}
		</style>
	</head>
	<body>

		<h1>Errorreporting Testing</h1>
		
		<p>
			<strong>On dev warnings should be thrown. On live only fatal.</strong>
			<br />
			Notice on dev are helpful too.
		</p>
		
		<hr />
		
		<p><em>Test for a <strong>E_WARNING</strong>: </em> <span class="errormessage">
		<?php $file=fopen('nonexistantfile.txt','r'); ?>
		</span>... done</p>
		
		<hr />
		
		<p><em>Test for <strong>E_USER_NOTICE</strong>: </em> <span class="errormessage">
		<?php trigger_error('Testing ...', E_USER_NOTICE); ?>
		</span>... done</p>
		
		<hr />
		
		<p><em>Test for <strong>E_NOTICE</strong>: </em> <span class="errormessage">
		<?php 'Calling variable \$a' . $a; ?>
		</span>... done</p>
		
		<hr />
		
		<p><em>Test for <strong>E_USER_WARNING</strong>: </em> <span class="errormessage">
		<?php trigger_error('Testing ...', E_USER_WARNING); ?>
		</span>... done</p>
		
		<hr />
		
		<p><em>Test division by zero <strong>E_WARNING</strong>: </em> <span class="errormessage"> testing 10/0
		<?php $a =  10/0; ?>
		</span>... done</p>
		
		<hr />
		
		<p><em>Test for <strong>E_USER_ERROR</strong>: </em> <span class="errormessage">
		<?php trigger_error('Testing ...', E_USER_ERROR); ?>
		</span>... done</p>
		
		<hr />
		
		<p>All tests done.</p>
	
	</body>
</html>