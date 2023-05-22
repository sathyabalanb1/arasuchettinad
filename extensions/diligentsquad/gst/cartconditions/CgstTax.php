<?php

namespace Diligentsquad\Gst\CartConditions;

use Diligentsquad\Gst\Models\GstSettings;
use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;
use System\Models\Currencies_model;

class CgstTax extends CartCondition
{

    protected $taxCgst;

    public $taxCgstRate;

    protected $taxCgstRateLabel;

    public $taxInclusive;

    protected $taxDelivery;

    public $priority = 200;

    public function getLabel()
    {
        $label= $this->taxInclusive ? "{$this->taxCgstRateLabel}% ".lang('diligentsquad.gst::default.text_cgst_included') : "{$this->taxCgstRateLabel}%";
        return sprintf(lang($this->label), $label);
    }

    public function onLoad()
    {
        $this->taxCgst = GstSettings::get('tax_cgst');
        $this->taxCgstRate = $this->taxCgstRateLabel = GstSettings::get('tax_cgst_percentage');
        $this->taxInclusive = !(bool)GstSettings::get('tax_menu_price');
       // $this->taxDelivery = (bool)setting('tax_delivery_charge', 0);
    }

    public function beforeApply()
    {
        if (!$this->taxCgst || !$this->taxCgstRate)
            return FALSE;
    }

    public function getActions()
    {
        $precision = optional(Currencies_model::getDefault())->decimal_position ?? 2;
        return [
            [
                'value' => "{$this->taxCgstRate}%",
                'inclusive' => $this->taxInclusive,
                'valuePrecision' => $precision,
            ]
        ];
    }

    public function calculate($total)
    {
        $excludeDeliveryCharge = Location::orderTypeIsDelivery() && !$this->taxDelivery;
        if ($excludeDeliveryCharge) {
            $deliveryCharge = Location::coveredArea()->deliveryAmount($total);
            $total -= (float)$deliveryCharge;
        }

        $result = parent::calculate($total);

        if ($excludeDeliveryCharge) {
            $result += (float)$deliveryCharge;
        }
        return $result;
    }
}
