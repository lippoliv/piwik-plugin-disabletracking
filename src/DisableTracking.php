<?php
    /**
     * Piwik - free/libre analytics platform
     *
     * @link    http://piwik.org
     * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
     */

    namespace Piwik\Plugins\DisableTracking;

    class DisableTracking extends
        \Piwik\Plugin {


        /**
         * Register the events to listen on in this plugin.
         *
         * @return array
         */
        public function registerEvents() {
            return array(
                'Tracker.initRequestSet' => 'newTrackingRequest'
            );
        }


        /**
         * @param array $arg
         */
        public function newTrackingRequest ($arg) {
            die();
        }


    }
