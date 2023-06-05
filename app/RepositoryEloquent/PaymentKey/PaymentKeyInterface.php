<?php


namespace App\RepositoryEloquent\PaymentKey;

interface PaymentKeyInterface
{
    /**
     * @return mixed
     */
    public function getByUser($token);

    /**
     * @return mixed
     */
    public function createRandomKey();
}
