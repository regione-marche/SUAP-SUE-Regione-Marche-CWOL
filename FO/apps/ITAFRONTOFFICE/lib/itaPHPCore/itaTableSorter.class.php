<?php

class itaTableSorter {

    function __construct() {
        
    }

    function __destruct() {
        
    }

    public function mostraPager($idTable = "", $Npage = "", $pageRows = "") {
        if ($Npage) {
            $value = $Npage;
        }

        if ($pageRows == "10") {
            $Sel10 = "selected";
        }

        if ($pageRows == "20") {
            $Sel20 = "selected";
        }

        if ($pageRows == "50") {
            $Sel50 = "selected";
        }

        $html = '
                <div align="center" style="width: 100%;" id="pager' . $idTable . '" class="pager">
                        <button type="button" class="first italsoft-button italsoft-button--circled">
                            <i class="icon ion-ios-skipbackward italsoft-icon" aria-hidden="true"></i>
                        </button>

                        <button type="button" class="prev italsoft-button italsoft-button--circled">
                            <i class="icon ion-arrow-left-b italsoft-icon" aria-hidden="true"></i>
                        </button>
                        
                        <input value="' . $value . '" name="ita-Npage" type="text" class="pagedisplay"/>
                            
                        <button type="button" class="next italsoft-button italsoft-button--circled">
                            <i class="icon ion-arrow-right-b italsoft-icon" aria-hidden="true"></i>
                        </button>
                        
                        <button type="button" class="last italsoft-button italsoft-button--circled">
                            <i class="icon ion-ios-skipforward italsoft-icon" aria-hidden="true"></i>
                        </button>
                        
                        <select name="ita-pageNrows" class="pagesize">
                            <option ' . $Sel10 . ' value="10">10 </option>
                            <option ' . $Sel20 . ' value="20">20 </option>
                            <option ' . $Sel50 . ' value="50">50 </option>
                        </select> per pagina
                </div>
                ';

        return $html;
    }

}
