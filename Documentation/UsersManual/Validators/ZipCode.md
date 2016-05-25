ZIP Code
========

Checks if a zip code belongs to the country.

To have the rules for every country, you need to update static\_countries. Add the two new database table fields with the extension manager. After that the rules can be imported with the update script in the extension manager.

The validator has two methods, which expect the first parameter to implement the interface tx\_mklib\_interface\_IZipCountry.

~~~~ {.sourceCode .php}
// get the model with the data for germany
tx_rnbase::load('tx_mklib_model_StaticCountry');
$country = tx_mklib_model_StaticCountry::getInstance(54);
// validate
tx_rnbase::load('tx_mklib_validator_ZipCode');
return tx_mklib_validator_ZipCode::validate($country, '09113')
      ? true : tx_mklib_validator_ZipCode::getFormatInfo($country);
~~~~

Supported countries
-------------------

-   Deutschland (DE)
-   Oesterreich (AT)
-   Schweiz (CH)
-   Niederlande (NL)
-   Liechtenstein (LI)
-   Belgien (BE)
-   Dänemark (DK)
-   Grossbritannien (GB)
-   Italien (IT)
-   Spanien (ES)
-   Frankreich (FR)
-   Luxemburg (LU)
-   Schweden (SW)
-   Tuerkei (TR)
-   Island (IS)
-   Kroatien (HR)
-   Rumänien (RO)
-   Slovakische Republik (SK)
-   USA (US)
-   China (CN)
-   Tschechien (CZ)
-   Zypern (CY)
-   Norwegen (NO)
-   Polen (PL)
-   Ungarn (HU)
-   Kanada (CA)
-   Finnland (FI)
-   Bulgarien (BG)
-   Estland (EE)
-   Griechenland (GR)
-   Irland (IE)
-   Kasachstan (KZ)
-   Lettland (LV)
-   Litauen (LT)
-   Malta (MT)
-   Mazedonien (MK)
-   Monaco (MC)
-   Portugal (PT)
-   Russland (RU)
-   Serbien (RS)
-   Slowenien (SI)
-   Süd Afrika (ZA)

