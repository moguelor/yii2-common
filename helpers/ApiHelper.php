<?php

namespace jmoguelruiz\yii2\common\helpers;

use yii\web\BadRequestHttpException;

class ApiHelper
{

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

    /**
     * Remove elements in array.
     * 
     * @param arr $elements Fields to search in data for remove.
     * @param arr $data All data.
     */
    public static function unsetElementsInArray($elements, $data)
    {
        foreach ($elements as $element) {
            if (isset($data[$element])) {
                unset($data[$element]);
            }
        }
    }
}
