<?php
    namespace Piwik\Plugins\DisableTracking;

    use Piwik\Menu\MenuAdmin;

    class Menu extends
        \Piwik\Plugin\Menu {


        public function configureAdminMenu(MenuAdmin $menu) {
            $menu->addMeasurableItem('Disable Tracking', $this->urlForDefaultAction());
        }


    }
