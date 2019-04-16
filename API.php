<?php

namespace Piwik\Plugins\DisableTracking;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\API as BaseAPI;

/**
 * Disable Tracking API.
 */
class API extends BaseAPI
{
    private static $instance = null;

    /**
     * Get Disable Tracking API instance.
     *
     * @throws \Exception if unable to bind to the container
     *
     * @return API the class instance
     */
    public static function getInstance()
    {
        try {
            $instance = StaticContainer::get('DisableTracking_API');
            if (!($instance instanceof API)) {
                // Exception is caught below and corrected
                throw new Exception('DisableTracking_API must inherit API');
            }
            self::$instance = $instance;
        } catch (Exception $e) {
            self::$instance = StaticContainer::get('Piwik\Plugins\DisableTracking\API');
            StaticContainer::getContainer()->set('DisableTracking_API', self::$instance);
        }

        return self::$instance;
    }

    /**
     * Change archive status for websites.
     *
     * @param string $idSites the list of comma separated websites IDs
     * @param $archive 'on' to archive, 'off' to re-enable
     *
     * @throws Exception if an error occurred
     */
    public function changeArchiveState($idSites, $archive)
    {
        Piwik::checkUserHasAdminAccess($idSites);

        DisableTracking::changeArchiveState(explode(',', $idSites), $archive);
    }
}
