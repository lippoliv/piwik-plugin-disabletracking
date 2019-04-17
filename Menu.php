<?php

namespace Piwik\Plugins\DisableTracking;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    /**
     * Adds a menu entry for the admin.
     *
     * @param \Piwik\Menu\MenuAdmin $menu the current Menu
     */
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addMeasurableItem('Disable Tracking', $this->urlForDefaultAction());
        }
    }
}
