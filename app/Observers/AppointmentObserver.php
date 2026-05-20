<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\GoogleCalendarService;
use Throwable;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        if ($appointment->status === 'odwołana') {
            return;
        }

        $this->syncCreatedEvent($appointment);
    }

    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged([
            'customer_id',
            'service_id',
            'user_id',
            'start_time',
            'end_time',
            'status',
        ])) {
            return;
        }

        try {
            $calendar = app(GoogleCalendarService::class);

            if ($appointment->status === 'odwołana') {
                $calendar->deleteEvent($appointment);
                $appointment->forceFill(['google_event_id' => null])->saveQuietly();

                return;
            }

            $eventId = $calendar->updateEvent($appointment);
            $appointment->forceFill(['google_event_id' => $eventId])->saveQuietly();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function deleting(Appointment $appointment): void
    {
        try {
            app(GoogleCalendarService::class)->deleteEvent($appointment);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function syncCreatedEvent(Appointment $appointment): void
    {
        try {
            $eventId = app(GoogleCalendarService::class)->createEvent($appointment);
            $appointment->forceFill(['google_event_id' => $eventId])->saveQuietly();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
