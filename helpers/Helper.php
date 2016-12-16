<?php

namespace jmoguelruiz\yii2\common\helpers;

use Yii;
use yii\db\Transaction;

class Helper{
   
     /**
     * 
     * Inicia una transaccion validando si
     * actualmente existe alguna.
     * 
     * @return Transaction
     */
    public static function beginTransaction()
    {
        return !empty(Yii::$app->db->getTransaction()) ? null : Yii::$app->db->beginTransaction();
    }

    /**
     * 
     * Cuando la transaccion es exitosa realiza
     * el commit.
     * 
     * @param Transaction $transaction
     */
    public static function transactionSuccessful($transaction)
    {
        if (!empty($transaction)) {
            $transaction->commit();
        }
    }

    /**
     * 
     * Cuando la transaccion NO es exitos realiza
     * un rollback();
     * 
     * @param Transaction $transaction
     */
    public static function transactionFailure($transaction)
    {
        if (!empty($transaction)) {
            $transaction->rollBack();
        }
    }

}

