<?php


namespace App\ReadXYZ\Models;


class Timer
{
    private float $start;
    private string $description;
    private bool $record;

    /**
     * Timer constructor. creates instance and starts timer.
     * @param string $description
     * @param bool $record
     */
    public function __construct(string $description, bool $record=false) {
        $this->description = $description;
        $this->start = microtime(true);
        $this->record = $record;
    }

    /**
     * Stop the timer and record it in docs/elapsedTimes.log if recording has been elected
     * @return float elasped time
     */
    public function stop(): float
    {
        $this->end = microtime(true);
        $mSecs= ($this->end - $this->start) * 1000.0;
        if ($this->record) Log::elapsedTime($this->description, $mSecs);
        return $mSecs;
    }
}
