<?php

namespace Thoughtco\Dinein\OrderTypes;

use Igniter\Flame\Location\AbstractOrderType;
use Igniter\Flame\Location\WorkingSchedule;
use Igniter\Local\Facades\Location as LocationFacade;

class Dinein extends AbstractOrderType
{
    private $scheduleCode = 'opening';

    public function getOpenDescription(): string
    {
        return sprintf(
            lang('thoughtco.dinein::default.text_dinein_time_info'),
            sprintf(lang('igniter.local::default.text_in_minutes'), $this->getLeadTime())
        );
    }

    public function getOpeningDescription(string $format): string
    {
        $starts = make_carbon($this->getSchedule()->getOpenTime());

        return sprintf(
            lang('thoughtco.dinein::default.text_dinein_time_info'),
            sprintf(lang('igniter.local::default.text_starts'), '<b>'.$starts->isoFormat($format).'</b>')
        );
    }

    public function getClosedDescription(): string
    {
        return sprintf(
            lang('thoughtco.dinein::default.text_dinein_time_info'),
            lang('igniter.local::default.text_is_closed')
        );
    }

    public function getDisabledDescription(): string
    {
        return lang('thoughtco.dinein::default.text_dinein_is_unavailable');
    }

    public function isActive(): bool
    {
        return $this->code === LocationFacade::orderType();
    }

    public function isDisabled(): bool
    {
        return !$this->model->hasDinein();
    }

    public function getInterval(): int
     {
        return $this->model->getOrderTimeInterval('dinein');
     }

     public function getLeadTime(): int
     {
        return $this->model->getOrderLeadTime('dinein');
     }

     public function getFutureDays(): int
     {
        return 0;
     }

     public function getSchedule(): WorkingSchedule
     {
         if (!is_null($this->schedule))
             return $this->schedule;

         $schedule = $this->model->newWorkingSchedule(
             $this->scheduleCode, $this->getFutureDays()
         );

         return $this->schedule = $schedule;
     }

    public function getScheduleRestriction(): int
    {
        return $this->model->getOrderTimeRestriction($this->code);
    }
}
