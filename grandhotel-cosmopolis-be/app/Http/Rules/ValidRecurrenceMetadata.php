<?php

namespace App\Http\Rules;

use App\Http\Controllers\Event\Recurrence;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class ValidRecurrenceMetadata implements ValidationRule
{
    private mixed $recurrence;

    public function __construct(mixed $recurrence) {
        $this->recurrence = $recurrence;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $recurrence = Recurrence::from($this->recurrence);
        } catch (Throwable) {
            $fail('Invalid value for recurrence');
            return;
        }

        switch ($recurrence) {
            case Recurrence::EVERY_X_DAYS:
                if ($value <= 0) {
                    $fail('Invalid value for :attribute');
                }
                return;
            case Recurrence::EVERY_MONTH_AT_DAY_X:
                if ($value < 0 || $value > 31) {
                    $fail('Invalid value for :attribute');
                }
                return;
            case Recurrence::EVERY_FIRST_DAY_IN_MONTH:
            case Recurrence::EVERY_LAST_DAY_IN_MONTH:
            case Recurrence::EVERY_SECOND_DAY_IN_MONTH:
            case Recurrence::EVERY_THIRD_DAY_IN_MONTH:
                if ($value < 0 || $value > 6) {
                    $fail('Invalid value for :attribute');
                }
                return;
        }
    }
}
