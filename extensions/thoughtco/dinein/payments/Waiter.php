<?php

namespace Thoughtco\Dinein\Payments;

use Admin\Classes\BasePaymentGateway;
use ApplicationException;

class Waiter extends BasePaymentGateway
{
    /**
     * @param array $data
     * @param \Admin\Models\Payments_model $host
     * @param \Admin\Models\Orders_model $order
     *
     * @throws \ApplicationException
     */
    public function processPaymentForm($data, $host, $order)
    {
        if (!$paymentMethod = $order->payment)
            throw new ApplicationException('Payment method not found');

        $order->updateOrderStatus($host->order_status, ['notify' => FALSE]);
        $order->markAsPaymentProcessed();
    }
}
