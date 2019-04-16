<?php

namespace Piwik\Plugins\DisableTracking;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\API as BaseAPI;

class API extends BaseAPI
{
    private static $instance = null;

    /**
     * @throws \Exception
     *
     * @return API
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
     * @param $idSites
     * @param $archive
     *
     * @throws Exception
     */
    public function changeArchiveState($idSites, $archive)
    {
        Piwik::checkUserHasAdminAccess($idSites);

        DisableTracking::changeArchiveState(explode(',', $idSites), $archive);
    }

}
