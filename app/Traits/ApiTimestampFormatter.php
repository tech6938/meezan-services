<?php

namespace App\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ApiTimestampFormatter
{
    protected function formatApiDateTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $timezone = config('app.timezone', 'Asia/Karachi');

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->setTimezone($timezone)->format('d M Y, h:i A');
        }

        if (is_string($value) && trim($value) !== '') {
            return Carbon::parse($value)->setTimezone($timezone)->format('d M Y, h:i A');
        }

        return null;
    }

    protected function transformForApi($value)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $this->formatApiDateTime($value);
        }

        if ($value instanceof Collection) {
            return $value->map(fn ($item) => $this->transformForApi($item))->values();
        }

        if ($value instanceof Model) {
            return $this->transformForApi($value->toArray());
        }

        if (is_array($value)) {
            $transformed = [];

            foreach ($value as $key => $item) {
                $transformed[$key] = $this->shouldFormatApiKey($key)
                    ? $this->formatApiDateTime($item)
                    : $this->transformForApi($item);
            }

            return $transformed;
        }

        return $value;
    }

    protected function shouldFormatApiKey($key): bool
    {
        if (!is_string($key)) {
            return false;
        }

        $normalizedKey = strtolower($key);

        if (in_array($normalizedKey, ['created_at', 'updated_at', 'deleted_at', 'seen_at', 'read_at', 'login_time', 'timestamp', 'uploaded_at', 'latest_message_time', 'started_at', 'starting_date'])) {
            return true;
        }

        return str_ends_with($normalizedKey, '_at') || str_ends_with($normalizedKey, '_time') || str_contains($normalizedKey, 'created_at');
    }
}
