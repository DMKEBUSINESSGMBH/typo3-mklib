File
====

getRelPath
----------

Returns the relative path of a file or folder.

getServerPath
-------------

Returns an absolute server path of a file or folder.

getWebPath
----------

Returns an absolute web path of a file or folder.

createDenyHtaccess
------------------

Creates in the given folder a .htaccess file to avoid unwanted access. Existing .htaccess files are not touched. The default content is

~~~~ {.sourceCode .ts}
order deny,allow
deny from all
allow from 127.0.0.1
~~~~

Classes:

-   tx\_mklib\_util\_File

