<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';
/**
 * Loads the home page.
 *
 * @package createeventcalendar
 * @subpackage controllers
 */
class CreateEventCalendarHomeManagerController extends CreateEventCalendarBaseManagerController {
    public function process(array $scriptProperties = array()) {

    }
    public function getPageTitle() { return $this->modx->lexicon('createeventcalendar'); }
    public function loadCustomCssJs() {
    
    }

}