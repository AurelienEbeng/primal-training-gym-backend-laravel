<?php

namespace App\Services;

use Carbon\Carbon;
use Yasumi\Yasumi;

class ReservationService
{
    private $eightAm;
    private $sixAm;
    private $tenAm;

    public function __construct()
    {
        $this->eightAm = $this->convertStrToTime("08:00 AM");
        $this->sixAm = $this->convertStrToTime("06:00 AM");
        $this->tenAm = $this->convertStrToTime("10:00 AM");
    }

    public function isHoliday($date, $country = 'Canada')
    {
        $holidays = Yasumi::create($country, $date->year);

        return $holidays->isHoliday($date);
    }

    public function isWeekend($date)
    {
        return $date->isWeekend();
    }

    public function convertStrToTime($timeString)
    {
        try {
            return Carbon::createFromFormat('h:i A', $timeString)->format('H:i:s');
        } catch (\Exception $e) {
            return $timeString;
        }
    }

    public function validateFormData($date, $time, $classType)
    {
        $date = Carbon::parse($date);
        $time = Carbon::parse($time)->format('H:i:s');

        $isHoliday = $this->isHoliday($date);
        $isWeekend = $this->isWeekend($date);

        $isValid = true;
        $errorMessage = "";

        if ($classType === "Strength") {

            if (($isHoliday || $isWeekend) && $time !== $this->eightAm) {
                $isValid = false;
                $errorMessage = "The time choosen for weekend or holiday strength classes was not 8AM";
            } elseif (!$isHoliday && !$isWeekend && $time !== $this->sixAm) {
                $isValid = false;
                $errorMessage = "The time choosen for weekday strength classes was not 6AM";
            }

        } elseif ($classType === "Conditioning") {

            if (($isHoliday || $isWeekend) && $time !== $this->tenAm) {
                $isValid = false;
                $errorMessage = "The time choosen for weekend or holiday conditioning classes was not 10AM";
            } elseif (!$isHoliday && !$isWeekend && $time !== $this->eightAm) {
                $isValid = false;
                $errorMessage = "The time choosen for weekday conditioning classes was not 8AM";
            }

        } elseif ($classType === "Community Classes") {

            if ($isWeekend) {
                $isValid = false;
                $errorMessage = "We do not offer community classes during the weekend";
            } else {
                if (!$isHoliday && !$isWeekend && $time !== $this->eightAm) {
                    $isValid = false;
                    $errorMessage = "The time choosen for weekday community classes was not 8AM";
                }
            }

        } else {
            $isValid = false;
            $errorMessage = "We do not offer selected classes";
        }

        return [$isValid, $errorMessage];
    }

    public function updateMutableDataWithCorrectTimeFormat($data)
    {
        if (isset($data['time'])) {
            $data['time'] = $this->convertStrToTime($data['time']);
        }

        return $data;
    }

    public function isClassInThePast($date, $classType)
    {
        $date = Carbon::parse($date);
        $today = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

        $error = "Class is in the past";

        if ($date->lt($today)) {
            return [true, $error];
        }

        if ($currentTime > $this->tenAm) {
            return [true, $error];
        }

        $isHoliday = $this->isHoliday($date);
        $isWeekend = $this->isWeekend($date);

        if ($date->eq($today)) {

            if ($classType === "Strength") {

                if (!$isHoliday && !$isWeekend && $currentTime > $this->sixAm) {
                    return [true, $error];
                }

                if (($isHoliday || $isWeekend) && $currentTime > $this->eightAm) {
                    return [true, $error];
                }

            } elseif ($classType === "Conditioning") {

                if (!$isHoliday && !$isWeekend && $currentTime > $this->eightAm) {
                    return [true, $error];
                }

                if (($isHoliday || $isWeekend) && $currentTime > $this->tenAm) {
                    return [true, $error];
                }

            } elseif ($classType === "Community Classes") {

                if (!$isHoliday && !$isWeekend && $currentTime > $this->eightAm) {
                    return [true, $error];
                }
            }
        }

        return [false, null];
    }
}