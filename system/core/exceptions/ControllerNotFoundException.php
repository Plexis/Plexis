<?php
/**
 * Plexis Content Management System
 *
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 */
 
/**
 * Thrown when the controller class does not exist in the controllers path
 * @package     Core
 * @subpackage  Exceptions
 * @see         Core\Module::dispatch()
 */
class ControllerNotFoundException extends NotFoundException {}