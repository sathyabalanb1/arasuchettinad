<?php

namespace Diligentsquad\Gst\CartConditions;

use Diligentsquad\Gst\Models\GstSettings;
use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Reviews_model;
use System\Models\Currencies_model;
use Diligentsquad\Gst\Models\GstModel;

class SgstTax extends CartCondition
{

    protected $taxSgst;

    public $taxSgstRate;

    protected $taxSgstRateLabel;

    public $taxInclusive;

    protected $taxDelivery;

    public $priority = 200;

    public function getLabel()
    {
        $label= $this->taxInclusive ? "{$this->taxSgstRateLabel}% ".lang('diligentsquad.gst::default.text_sgst_included') : "{$this->taxSgstRateLabel}%";
        return sprintf(lang($this->label), $label);
    }

    public function onLoad()
    {
        $this->taxSgst = GstSettings::get('tax_sgst');
        $this->taxSgstRate = $this->taxSgstRateLabel = GstSettings::get('tax_sgst_percentage');
        $this->taxInclusive = !(bool)GstSettings::get('tax_menu_price');
    }

    public function beforeApply()
    {
        if (!$this->taxSgst || !$this->taxSgstRate)
            return FALSE;
    }

    public function getActions()
    {
        $precision = optional(Currencies_model::getDefault())->decimal_position ?? 2;
        return [
            [
                'value' => "{$this->taxSgstRate}%",
                'inclusive' => $this->taxInclusive,
                'valuePrecision' => $precision,
            ]
        ];
    }

    public function calculate($total)
    {
        $excludeDeliveryCharge = Location::orderTypeIsDelivery();
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
