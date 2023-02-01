<?php

/**
 *
 * Interfaccia cms
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.10.2016
 * @link
 * @see
 * @since
 * 
 */
interface itaCms {

    public function getContent($slug, $params);

    public function loadCms();

    public function getCmsRequire();

    public function getHeader();

    public function getFooter();

    public function getStyleURL();

    public function getNavigation();
}

?>
