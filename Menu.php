<?php
    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Menu\MenuAdmin;
    use Piwik\Piwik;

    class Menu extends
        \Piwik\Plugin\Menu {


        /**
         * Adds a menu entry for the admin.
         *
         * @param \Piwik\Menu\MenuAdmin $menu The current Menu.
         */
        public function configureAdminMenu(MenuAdmin $menu) {
            if (Piwik::hasUserSuperUserAccess() === TRUE) {
                $menu->addMeasurableItem('Disable Tracking', $this->urlForDefaultAction());
            }
        }


    }
