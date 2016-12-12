<?php

namespace Clab\DeliveryBundle\Exception\DeliverySchedule;

class DuplicateChildren extends \Exception
{
    protected $day;
    protected $schedule;

    public function __construct($schedule, $day)
    {
        $this->schedule = $schedule;
        $this->day = $day;
        parent::__construct('Le jour ' . $day->format('d/m/Y') . ' a déjà été sélectionné pour la tournée ' . $schedule->getName());
    }
}
