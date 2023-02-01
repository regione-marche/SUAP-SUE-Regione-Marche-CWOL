<?php

class itaFormHelper {

    public static function innerForm($model, $container, $alias = '') {
        if (!$alias) {
            $alias = $model . '_' . time() . '_' . rand();
        }

        itaLib::openInner($model, true, false, $container, '', '', $alias);
        $objModel = itaModel::getInstance($model, $alias);

        return $objModel;
    }

}
