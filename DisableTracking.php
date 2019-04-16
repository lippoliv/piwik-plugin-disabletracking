<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DisableTracking;

use Hashids\Hashids;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\Manager;

class DisableTracking extends Plugin
{
    const TABLE_DISABLE_TRACKING_MAP = 'disable_site_tracking';

    /**
     * @throws \Exception
     *
     * @return array The information for each tracked site if it is disabled or not.
     */
    public static function getSitesStates()
    {
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

        $rows = Db::fetchAll($sql);

        foreach ($rows as $row) {
            $ret[] = array(
                'id'       => $row['id'],
                'label'    => $row['name'],
                'url'      => $row['main_url'],
                'disabled' => self::isSiteTrackingDisabled($row['id']),
            );

        }

        return isset($ret) ? $ret : [];
    }

    /**
     * Disables tracking for the given site.
     *
     * @throws \Exception
     *
     * @param integer $id The site do enable tracking for.
     */
    private static function disableSiteTracking($id)
    {
        if (self::isSiteTrackingDisabled($id) === FALSE) {
            $sql = '
                    INSERT INTO `' . Common::prefixTable(self::TABLE_DISABLE_TRACKING_MAP) . '`
                        (siteId, created_at)
                    VALUES
                        (:siteId, NOW())
                ';
            Db::query($sql, [':siteId' => $id]);
        }
    }

    /**
     * Enables tracking for all sites except the given siteIds.
     *
     * @throws \Exception
     *
     * @param array $siteIds The sites to exclude from process.
     */
    private static function enableAllSiteTrackingExcept($siteIds)
    {
        $sql = '
                UPDATE
                    `' . Common::prefixTable(self::TABLE_DISABLE_TRACKING_MAP) . '`
                SET
                    `deleted_at`= NOW()
                WHERE 
                    `deleted_at` IS NULL
            ';
        if (count($siteIds) !== 0) {
            $sql .= ' AND `siteId` NOT IN (' . implode(',', $siteIds) . ')';
        }
        Db::query($sql);
    }

    /**
     * Register the events to listen on in this plugin.
     *
     * @return array
     */
    public function registerEvents()
    {
        return array(
            'Tracker.initRequestSet' => 'newTrackingRequest',
        );
    }

    /**
     * Event-Handler for a new tracking request.
     *
     * @throws \Exception
     */
    public function newTrackingRequest()
    {
        if (isset($_GET['idsite']) === TRUE) {
            $siteId = (int)$_GET['idsite'];
            if (Manager::getInstance()->isPluginActivated('ProtectTrackID')) {
                $settings = StaticContainer::get('Piwik\Plugins\ProtectTrackID\SystemSettings');
                $base =  $settings->base->getValue();
                $salt = $settings->salt->getValue();
                $length = $settings->length->getValue();
                $Hashid = new Hashids($salt, $length, $base);
                $siteId = (int)$Hashid->decode($_GET['idsite'])[0];
            }
            if (self::isSiteTrackingDisabled($siteId)) {
                // End tracking here, as of tracking for this page should be disabled, admin sais.
                die();
            }
        }
    }

    /**
     * @param integer $siteId The site id to check.
     *
     * @throws \Exception
     *
     * @return bool Whether new tracking requests are ok or not.
     */
    public static function isSiteTrackingDisabled($siteId)
    {
        $sql = '
                SELECT
                  count(*) AS `disabled`
                FROM ' . Common::prefixTable(self::TABLE_DISABLE_TRACKING_MAP) . '
                WHERE
                    siteId = :siteId AND
                    deleted_at IS NULL;
            ';

        $state = Db::fetchAll($sql, [':siteId' => $siteId]);

        return (bool) $state[0]['disabled'];
    }

    /**
     * Generate table to store disable states while install plugin.
     *
     * @throws \Exception
     */
    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . Common::prefixTable(self::TABLE_DISABLE_TRACKING_MAP) . ' (
                        id INT NOT NULL AUTO_INCREMENT,
                        siteId INT NOT NULL,
                        created_at DATETIME NOT NULL,
                        deleted_at DATETIME,
                        PRIMARY KEY (id)
                    )  DEFAULT CHARSET=utf8';
        Db::exec($sql);
    }

    /**
     * Remove plugins table, while uninstall the plugin.
     */
    public function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::TABLE_DISABLE_TRACKING_MAP));
    }

    /**
     * Save new input.
     *
     * @throws \Exception
     */
    public static function save()
    {
        foreach ($_POST as $key => $state) {
            if (strpos($key, '-') !== FALSE) {
                $id = explode('-', $key);
                $id = $id[1];
                if ($state === 'on') {
                    self::disableSiteTracking($id);
                    $disabled[] = $id;
                }
            }
        }

        self::enableAllSiteTrackingExcept(isset($disabled) ? $disabled : []);
    }
}
