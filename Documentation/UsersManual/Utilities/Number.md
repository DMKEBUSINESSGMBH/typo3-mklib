Number
======

floatVal
--------

Creates a technically correct float value.

When a typecast is made by loatval(), doubleval(), (real), (float) or (double) the local configuration which is set through setlocale are considered. If it is set to german 5.45 becomes 5,45. This can make database queries fail as a numeric value can only contain numbers and a dot.

Classes:

-   tx\_mklib\_util\_Number

