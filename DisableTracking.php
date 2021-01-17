<?php
    /**
     * Piwik - free/libre analytics platform
     *
     * @link    http://piwik.org
     * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
     */

    namespace Piwik\Plugins\DisableTracking;

    use Exception;
    use Piwik\Common;
    use Piwik\Db;
    use Piwik\Plugin;

    class DisableTracking extends
        Plugin {


        const TABLEDISABLETRACKINGMAP = 'disable_site_tracking';


        /**
         * @return array The information for each tracked site if it is disabled or not.
         * @throws \Exception
         */
        public static function getSitesStates() {
            $ret = array();

            $sql = '
              SELECT
                `idsite` as `id`,
                `name`,
                `main_url`
              FROM
                `' . Common::prefixTable('site') . '`
              ORDER BY
                `name` ASC
            ';

            $rows = Db::query($sql);

            while (($row = $rows->fetch()) !== FALSE) {
                $ret[] = array(
                    'id'       => $row['id'],
                    'label'    => $row['name'],
                    'url'      => $row['main_url'],
                    'disabled' => FALSE,
                );
            }

            // Get disabled states seperately to not destroy our db query resultset.
            for ($i = 0; $i < count($ret); $i++) {
                $ret[$i]['disabled'] = self::isSiteTrackingDisabled($ret[$i]['id']);
            }

            return $ret;
        }


        /**
         * Disables tracking for the given site.
         *
         * @param integer $id The site do enable tracking for.
         *
         * @throws \Exception
         */
        public static function disableSiteTracking($id) {
            if (self::isSiteTrackingDisabled($id) === FALSE) {
                $sql = '
                    INSERT INTO `' . Common::prefixTable(self::TABLEDISABLETRACKINGMAP) . '`
                        (siteId, created_at)
                    VALUES
                        (?, NOW())
                ';
                Db::query(
                    $sql,
                    $id
                );
            }
        }


        /**
         * Enables tracking for all sites except the given siteIds.
         *
         * @param array $siteIds The sites to exclude from process.
         *
         * @throws \Exception
         */
        public static function enableAllSiteTrackingExcept($siteIds = array()) {
            $sql = '
                UPDATE
                    `' . Common::prefixTable(self::TABLEDISABLETRACKINGMAP) . '`
                SET
                    `deleted_at`= NOW()
                WHERE 
                    `deleted_at` IS NULL 
            ';

            if (count($siteIds) !== 0) {
                $sql .= 'AND `siteId` NOT IN (?)';
            }

            Db::query(
                $sql,
                join(',', $siteIds)
            );
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
         * @throws \Exception
         */
        public static function isSiteTrackingDisabled($siteId) {
            $sql = '
                SELECT
                  count(*) AS `disabled`
                FROM `' . Common::prefixTable(self::TABLEDISABLETRACKINGMAP) . '`
                WHERE
                    siteId = ? AND
                    deleted_at IS NULL;
            ';

            $state = Db::fetchAll(
                $sql,
                $siteId
            );

            return boolval($state[0]['disabled']);
        }


        /**
         * Generate table to store disable states while install plugin.
         *
         * @throws \Exception
         */
        public function install() {
            try {
                $sql = 'CREATE TABLE `' . Common::prefixTable(self::TABLEDISABLETRACKINGMAP) . '` (
                        id INT NOT NULL AUTO_INCREMENT,
                        siteId INT NOT NULL,
                        created_at DATETIME NOT NULL,
                        deleted_at DATETIME,
                        PRIMARY KEY (id)
                    )  DEFAULT CHARSET=utf8';
                Db::exec($sql);
            } catch (Exception $e) {
                // ignore error if table already exists (1050 code is for 'table already exists')
                if (Db::get()
                      ->isErrNo($e, '1050') === FALSE) {
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


        /**
         * Save new input.
         */
        public static function save() {
            $disabled = array();

            foreach ($_POST as $key => $state) {
                if (strpos($key, '-') !== FALSE) {
                    $id = preg_split("/-/", $key);
                    $id = $id[1];

                    if ($state === 'on') {
                        self::disableSiteTracking($id);
                        $disabled[] = $id;
                    }
                }
            }

            self::enableAllSiteTrackingExcept($disabled);
        }


    }
