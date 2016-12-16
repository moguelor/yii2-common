<?php

namespace jmoguelruiz\yii2\common\helpers;

use Yii;
use yii\db\Transaction;

class ApiHelper{
   
    /**
     * 
     * Valida los atributos requeridos.
     * 
     * @param arr $requiredAttributes Atributos que son requeridos.
     * @param arr $params Parametros a verificar.
     * @throws BadRequestHttpException Missing parameter: '{parameter}' is required.
     */
    public static function validRequiredAttributes($requiredAttributes, $params)
    {
        foreach ($requiredAttributes as $attribute) {
            if (empty($params[$attribute])) {
                throw new BadRequestHttpException(Yii::t('base', "Missing parameter: '{parameter}' is required.", ['parameter' => $attribute]));
            }
        }
    }

}

