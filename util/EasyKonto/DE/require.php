<?php
/**
 * PHP SDK for the easyKonto web service.
 *
 * @category EasyKonto
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 * @since 2.0
 */

// Non-autoloader initialization
require_once 'EasyKonto/Exception.php';
require_once 'EasyKonto/ConnectionType.php';
require_once 'EasyKonto/ConnectionConfiguration.php';
require_once 'EasyKonto/HTTPClient.php';
require_once 'EasyKonto/DE/ModificationType.php';
require_once 'EasyKonto/DE/BankRecord.php';
require_once 'EasyKonto/DE/CheckResultType.php';
require_once 'EasyKonto/DE/CheckResult.php';
require_once 'EasyKonto/DE/ServiceInterface.php';
require_once 'EasyKonto/DE/Service.php';
