<?php

namespace Piwik\Plugins\DisableTracking;

use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;

/**
 * Disable Tracking plugin controller.
 */
class Controller extends ControllerAdmin
{
    /**
     * Rendering the overview over all pages for managing the disable-state.
     *
     * @throws \Exception if an error occurred
     *
     * @return string the rendered template to show
     */
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        if (true === isset($_POST) && true === isset($_POST['saveDisabledSitesState'])) {
            DisableTracking::save();
        }

        return $this->renderTemplate('index', ['sites' => DisableTracking::getSitesStates()]);
    }
}
