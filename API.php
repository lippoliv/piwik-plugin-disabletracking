<?php

namespace Piwik\Plugins\DisableTracking;

use Exception;
use Piwik\Piwik;
use Piwik\Plugin\API as BaseAPI;

/**
 * Disable Tracking API.
 */
class API extends BaseAPI
{
    /**
     * Change disabled status for websites.
     *
     * @param string $idSites the list of comma separated websites IDs
     * @param $disable 'on' to disable, 'off' to re-enable
     *
     * @throws Exception if an error occurred
     */
    public function changeDisableState($idSites, $disable)
    {
        Piwik::checkUserHasAdminAccess($idSites);

        DisableTracking::changeDisableState(explode(',', $idSites), $disable);
    }
}
