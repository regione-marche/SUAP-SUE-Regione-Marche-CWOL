<?php
class utiIcons {
    const ICON_EXT_TEMPLATE = "ita-icon ita-icon-File-Ext-%size%x%size% ita-icon-File-Ext-%ext%-%size%x%size%";

    /**
     * 
     * @param type $file_name
     * @param type $size
     * @return type
     */
    static public function getExtensionIconClass($file_name,$size){
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $icon_class = str_replace('%size%',$size,str_replace("%ext%", $ext, self::ICON_EXT_TEMPLATE));
        return $icon_class;
    }
}
?>
