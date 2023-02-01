<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaCmsFactory.class.php';

function cwbCmsTest() {
    $cwbAlfcityTest = new cwbCmsTest();
    $cwbAlfcityTest->parseEvent();
    return;
}

class cwbCmsTest extends itaModel {

    public function parseEvent() {
        $cms = itaCmsFactory::getCms();
        $component_out = $cms->getContent('loop', 'cat_news');

        Out::html('cwbCmsTest_divToReplace', $component_out);
    }

}

