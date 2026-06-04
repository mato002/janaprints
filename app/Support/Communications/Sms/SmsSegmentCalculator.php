<?php

namespace App\Support\Communications\Sms;

class SmsSegmentCalculator
{
    /**
     * @return array{characters: int, segments: int}
     */
    public function calculate(string $message): array
    {
        $characters = mb_strlen($message);

        if ($characters === 0) {
            return ['characters' => 0, 'segments' => 0];
        }

        if ($characters <= 160) {
            return ['characters' => $characters, 'segments' => 1];
        }

        return [
            'characters' => $characters,
            'segments' => (int) ceil($characters / 153),
        ];
    }
}
