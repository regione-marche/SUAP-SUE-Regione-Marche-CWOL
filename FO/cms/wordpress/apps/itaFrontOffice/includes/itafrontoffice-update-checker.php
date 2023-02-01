<?php

if (!class_exists('Itafrontoffice_Update_Checker')) {

    class Itafrontoffice_Update_Checker {

        private $cacheDir;
        private $updating = array();
        private $customConfigs = array(
            'itaFrontOffice' => array(
                '/config',
                '/lib/java/itaJ4SignDSS/conf',
                '/lib/java/itaComunicaWS/itaComunicaWS.properties',
                '/lib/java/itaJRGenerator/ItaReportGenerator.properties',
                '/vendor',
                '/vendor560/vendor'
            ),
            'suap_italsoft' => array(
                '/includes/PRATICHE_italsoft/customClass/*/config'
            )
        );

        public function __construct() {
            $repository_url = $this->get_repository_url();

            require_once ITA_FRONTOFFICE_INCLUDES . '/vendor/plugin-update-checker/plugin-update-checker.php';

            $this->cacheDir = WP_CONTENT_DIR . '/italsoft-cache';

            if (!file_exists($this->cacheDir)) {
                mkdir($this->cacheDir);
            }

            /*
             * Registro i plugin *_italsoft
             */

            $active_plugins = is_multisite() ? array_keys(get_site_option('active_sitewide_plugins')) : get_option('active_plugins');

            foreach ($active_plugins as $active_plugin) {
                if (!preg_match('/([a-zA-Z]+_italsoft)/', $active_plugin, $matches)) {
                    continue;
                }

                $plugin_name = $matches[1];

                $repository = "$repository_url/$plugin_name.json";
                $plugin_file = $this->get_plugin_dir($plugin_name) . "/$plugin_name.php";

                Puc_v4_Factory::buildUpdateChecker(
                    $repository, $plugin_file, $plugin_name
                );

                add_filter('upgrader_pre_install', array($this, "pre_update_hook_{$plugin_name}"));

                add_filter('upgrader_post_install', array($this, "post_update_hook_{$plugin_name}"));
            }

            /*
             * Registro il plugin di Framework (itaFrontOffice) ed il
             * tema itafrontoffice (se installato)
             */

            Puc_v4_Factory::buildUpdateChecker(
                "$repository_url/itaFrontOffice.json", $this->get_plugin_dir('itaFrontOffice') . '/itaFrontOffice.php', 'itaFrontOffice'
            );

            add_filter('upgrader_pre_install', array($this, "pre_update_hook_itaFrontOffice"));
            add_filter('upgrader_post_install', array($this, "post_update_hook_itaFrontOffice"));

            if (file_exists(get_theme_root() . '/itafrontoffice-theme/functions.php')) {
                Puc_v4_Factory::buildUpdateChecker(
                    "$repository_url/itafrontoffice-theme.json", get_theme_root() . '/itafrontoffice-theme/functions.php', 'itafrontoffice-theme'
                );

                add_filter('upgrader_pre_install', array($this, "pre_update_hook_itafrontoffice-theme"));
                add_filter('upgrader_post_install', array($this, "post_update_hook_itafrontoffice-theme"));
            }
        }

        private function get_repository_url() {
            $itafrontoffice_options = is_multisite() ? get_site_option('itafrontoffice_impostazioni_generali_options') : get_option('itafrontoffice_impostazioni_generali_options');
            if (!$itafrontoffice_options || empty($itafrontoffice_options['repository'])) {
                return false;
            }

            return rtrim($itafrontoffice_options['repository'], '/');
        }

        private function get_plugin_dir($plugin) {
            return WP_CONTENT_DIR . "/plugins/$plugin";
        }

        private function get_cache_dir($plugin) {
            return $this->cacheDir . "/$plugin";
        }

        public function pre_update_hook($plugin) {
            $is_current_upgrading = false;

            /**
             * Verifico che si stia effettuando l'update del plugin
             * oggetto dell'hook, in caso contrario non c'è bisogno di
             * spostare momentaneamente le config.
             */
            foreach (new FilesystemIterator(WP_CONTENT_DIR . '/upgrade') as $upgradeDir) {
                if (strpos($upgradeDir->getFilename(), $plugin) === 0) {
                    $is_current_upgrading = true;
                }
            }

            if (!$is_current_upgrading) {
                return false;
            }

            $this->updating[] = $plugin;
            $cache_dir = $this->get_cache_dir($plugin);
            $config_files = array_merge(glob($this->get_plugin_dir($plugin) . "/config.inc.*.php"), glob($this->get_plugin_dir($plugin) . "/config.inc.php"));

            /*
             * Pulisco la cartella di cache
             */
            $this->_rmdir($cache_dir);

            if (!file_exists($cache_dir)) {
                if (!mkdir($cache_dir)) {
                    return new WP_Error('U01', sprintf('Impossibile creare la cartella di cache \'%s\'.', $cache_dir));
                }
            }

            foreach ($config_files as $config_file) {
                if (strpos($config_file, 'sample.php') !== false) {
                    continue;
                }

                if (!copy($config_file, $cache_dir . '/' . basename($config_file))) {
                    return new WP_Error('U02', sprintf('Errore durante il backup del file di configurazione \'%s\'.', $config_file));
                }
            }

            /*
             * Configurazioni specifiche per i singoli plugin.
             */

            if (isset($this->customConfigs[$plugin])) {
                foreach ($this->customConfigs[$plugin] as $configPathPattern) {
                    foreach (glob($this->get_plugin_dir($plugin) . $configPathPattern) as $configPath) {
                        $cacheConfigPath = str_replace($this->get_plugin_dir($plugin), $cache_dir, $configPath);
                        if (!$this->_cpdir($configPath, $cacheConfigPath)) {
                            return new WP_Error('U04', sprintf('Errore durante il backup della configurazione \'%s\'.', $configPath));
                        }
                    }
                }
            }

            return true;
        }

        public function post_update_hook($plugin) {
            if (!in_array($plugin, $this->updating)) {
                return false;
            }

            $cache_dir = $this->get_cache_dir($plugin);

            if (!file_exists($cache_dir)) {
                return false;
            }

            /*
             * Configurazioni specifiche per i singoli plugin.
             */

            if (isset($this->customConfigs[$plugin])) {
                foreach ($this->customConfigs[$plugin] as $configPathPattern) {
                    foreach (glob($cache_dir . $configPathPattern) as $cacheConfigPath) {
                        $configPath = str_replace($cache_dir, $this->get_plugin_dir($plugin), $cacheConfigPath);
                        if (file_exists($configPath)) {
                            $this->_rmdir($configPath);
                        }

                        if (!rename($cacheConfigPath, $configPath)) {
                            printf('<b>Errore: impossibile ripristinare la configurazione \'%s\', procedere manualmente (<i>%s</i>).</b><br />', basename($configPath), $cache_dir . $configPath);
                        }
                    }
                }
            }

            foreach (new FilesystemIterator($cache_dir) as $config) {
                if ($config->isFile() && !rename($config->getPathname(), $this->get_plugin_dir($plugin) . '/' . $config->getFilename())) {
                    printf('<b>Errore: impossibile ripristinare il file \'%s\', procedere manualmente (<i>%s</i>).</b><br />', $config->getFilename(), $config->getPathname());
                }
            }

            /*
             * Aggiungo il log su repository
             */

            $repository_url = $this->get_repository_url();
            $plugin_data = $plugin === 'itafrontoffice-theme' ? get_theme_data(get_theme_root() . '/itafrontoffice-theme/functions.php') : get_plugin_data($this->get_plugin_dir($plugin) . "/$plugin.php");

            file_get_contents(sprintf('%s/logs/trigger.php?w=%s&p=%s&v=%s', $repository_url, urlencode(DOMAIN_CURRENT_SITE . PATH_CURRENT_SITE), urlencode($plugin), urlencode($plugin_data['Version'])));

            unset($this->updating[array_search($plugin, $this->updating)]);
        }

        public function __call($name, $arguments) {
            if (strpos($name, 'pre_update_hook_') === 0) {
                array_unshift($arguments, substr($name, strlen('pre_update_hook_')));
                return call_user_func_array(array($this, 'pre_update_hook'), $arguments);
            }

            if (strpos($name, 'post_update_hook_') === 0) {
                array_unshift($arguments, substr($name, strlen('post_update_hook_')));
                return call_user_func_array(array($this, 'post_update_hook'), $arguments);
            }
        }

        private function _cpdir($source, $destination) {
            if (!file_exists($source)) {
                return true;
            }

            if (is_dir($source)) {
                mkdir($destination, 0755, true);

                foreach (
                $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item
                ) {
                    if ($item->isDir()) {
                        mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    } else {
                        if (!copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                            return false;
                        }
                    }
                }
            } else {
                if (!file_exists(dirname($destination))) {
                    mkdir(dirname($destination), 0755, true);
                }

                if (!copy($source, $destination)) {
                    return false;
                }
            }

            return true;
        }

        private function _rmdir($source) {
            if (!file_exists($source) || !is_dir($source)) {
                return;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }

            rmdir($source);
        }

    }

}

new Itafrontoffice_Update_Checker;
