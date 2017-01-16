<?php
require_once dirname(__FILE__) . '/model/createeventcalendar/createeventcalendar.class.php';
/**
 * @package createeventcalendar
 */

abstract class CreateEventCalendarBaseManagerController extends modExtraManagerController {
    /** @var CreateEventCalendar $createeventcalendar */
    public $createeventcalendar;
    public function initialize() {
        $this->createeventcalendar = new CreateEventCalendar($this->modx);

        $this->addCss($this->createeventcalendar->getOption('cssUrl').'mgr.css');
        $this->addJavascript($this->createeventcalendar->getOption('jsUrl').'mgr/createeventcalendar.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            CreateEventCalendar.config = '.$this->modx->toJSON($this->createeventcalendar->options).';
            CreateEventCalendar.config.connector_url = "'.$this->createeventcalendar->getOption('connectorUrl').'";
        });
        </script>');
        
        parent::initialize();
    }
    public function getLanguageTopics() {
        return array('createeventcalendar:default');
    }
    public function checkPermissions() { return true;}
}