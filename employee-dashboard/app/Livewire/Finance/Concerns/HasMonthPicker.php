<?php

namespace App\Livewire\Finance\Concerns;

trait HasMonthPicker
{
    public bool $showMonthPicker = false;

    public int $pickerYear = 0;

    public function openMonthPicker(): void
    {
        $this->pickerYear = (int) explode('-', $this->periode)[0];
        $this->showMonthPicker = true;
    }

    public function closeMonthPicker(): void
    {
        $this->showMonthPicker = false;
    }

    public function previousPickerYear(): void
    {
        $this->pickerYear = max(2000, $this->pickerYear - 1);
    }

    public function nextPickerYear(): void
    {
        $this->pickerYear = min(2100, $this->pickerYear + 1);
    }

    public function selectMonth(int $month): void
    {
        if ($month < 1 || $month > 12) {
            return;
        }

        $this->periode = sprintf('%04d-%02d', $this->pickerYear, $month);
        $this->showMonthPicker = false;
    }
}
