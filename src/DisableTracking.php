<?php
    /**
     * Piwik - free/libre analytics platform
     *
     * @link    http://piwik.org
     * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
     */

    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Common;
    use Piwik\Db;
    use Piwik\Plugin;

    class DisableTracking extends
        Plugin {


        const TABLEDISABLETRACKINGMAP = 'disable_site_tracking';


        /**
         * @return array The information for each tracked site if it is disabled or not.
         */
        public static function getSitesStates() {
            $ret = array();

            $sql = "
              SELECT
                `idsite` as `id`,
                `name`,
                `main_url`
              FROM
                `" . Common::prefixTable('site') . "`
              ORDER BY
                `name` ASC
            ";

            $rows = Db::query($sql);

            while (($row = $rows->fetch()) !== FALSE) {
                $ret[] = array(
                    'id'       => $row['id'],
                    'label'    => $row['name'],
                    'url'      => $row['main_url'],
                    'disabled' => self::isSiteTrackingDisabled($row['id']),
                );
            }

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
        public static function isSiteTrackingDisabled($siteId) {
            // TODO fill with logic.

            return FALSE;
        }


        /**
         * Generate table to store disable states while install plugin.
         *
         * @throws \Exception
         */
        public function install() {
            try {
                $sql = "CREATE TABLE " . Common::prefixTable(self::TABLEDISABLETRACKINGMAP) . " (
                        id INT NOT NULL AUTO_INCREMENT,
                        siteId INT NOT NULL,
                        created_at DATETIME NOT NULL,
                        deleted_at DATETIME NOT NULL,
                        PRIMARY KEY (id)
                    )  DEFAULT CHARSET=utf8";
                Db::exec($sql);
            } catch (Exception $e) {
                // ignore error if table already exists (1050 code is for 'table already exists')
                if (Db::get()
                      ->isErrNo($e, '1050') === FALSE
                ) {
                    throw $e;
                }
            }
        }


        /**
         * Remove plugins table, while uninstall the plugin.
         */
        public function uninstall() {
            Db::dropTables(Common::prefixTable(self::TABLEDISABLETRACKINGMAP));
        }


    }
