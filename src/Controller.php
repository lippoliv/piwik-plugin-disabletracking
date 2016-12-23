<?php
    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Plugin\ControllerAdmin;

    class Controller extends
        ControllerAdmin {


        /**
         * Rendering the overview over all pages for managing the disable-state.
         *
         * @return string
         */
        public function index() {
            return $this->renderTemplate('index', array('sites' => DisableTracking::getSitesStates()));
        }


    }