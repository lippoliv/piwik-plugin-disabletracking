<?php
    /**
     * Piwik - free/libre analytics platform
     *
     * @link    http://piwik.org
     * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
     */

    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Plugin;
    use Piwik\Tracker\RequestSet;

    class DisableTracking extends
        Plugin {


        /**
         * Register the events to listen on in this plugin.
         *
         * @return array
         */
        public function registerEvents() {
            return array(
                'Tracker.initRequestSet' => 'newTrackingRequest',
            );
        }


        /**
         * Event-Handler for a new tracking request.
         */
        public function newTrackingRequest() {
            if (isset($_GET['idsite']) === TRUE) {
                $siteId = intval($_GET['idsite']);
                $disabled = FALSE;

                // TODO check whether this site is currently disabled.

                if ($disabled === TRUE) {
                    // End tracking here, as of tracking for this page should be disabled, admin sais.
                    die();
                }
            }
        }


    }
