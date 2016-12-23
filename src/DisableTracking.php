<?php
    /**
     * Piwik - free/libre analytics platform
     *
     * @link    http://piwik.org
     * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
     */

    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Plugin;

    class DisableTracking extends
        Plugin {


        /**
         * @return array The information for each tracked site if it is disabled or not.
         */
        public static function getSitesStates() {
            $ret = array(
                array(
                    'id'       => 10,
                    'label'    => 'Meeting Jesus',
                    'disabled' => FALSE,
                ),
            );

            return $ret;
        }


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

                if ($this->isSiteTrackingDisabled($siteId) === TRUE) {
                    // End tracking here, as of tracking for this page should be disabled, admin sais.
                    die();
                }
            }
        }


        /**
         * @param integer $siteId The site id to check.
         *
         * @return bool Whether new tracking requests are ok or not.
         */
        public function isSiteTrackingDisabled($siteId) {
            // TODO fill with logic.

            return FALSE;
        }


    }
