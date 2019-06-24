Clear Cache
===========

Clears the complete TYPO3 Cache. Since TYPO3 6.x including clearing the OpCode Cache.

Call it like:

typo3/cli\_dispatch.phpsh mklib -fc

or

typo3/cli\_dispatch.phpsh mklib --flush-cache

HINT: This method is deprecated and not longer available since TYPO3 9. Use the package 
helhum/typo3-console instead if you want to clear the cache via the CLI. 