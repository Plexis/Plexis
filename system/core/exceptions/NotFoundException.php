<?php
/**
 * Plexis Content Management System
 *
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 */
 
/**
 * Thrown when the action does not exist in the controller, or the controller
 * class does not exists. This exeption is mainly thrown for a 404
 *
 * @package     Core
 * @subpackage  Exceptions
 * @see         Core\Module::dispatch()
 */
class NotFoundException extends Exception {}