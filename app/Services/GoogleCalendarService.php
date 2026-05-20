<?php

namespace App\Services;

use App\Models\Appointment;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarService
{
    private Calendar $calendar;

    private string $calendarId;

    private string $timezone;

    public function __construct()
    {
        $credentials = config('services.google_calendar.credentials');
        $this->calendarId = (string) config('services.google_calendar.calendar_id');
        $this->timezone = (string) config('services.google_calendar.timezone', 'Europe/Warsaw');

        if (! $credentials || ! $this->calendarId) {
            throw new RuntimeException('Google Calendar credentials or calendar ID are not configured.');
        }

        $credentialsPath = Str::startsWith($credentials, '/')
            ? $credentials
            : base_path($credentials);

        if (! file_exists($credentialsPath)) {
            throw new RuntimeException("Google Calendar credentials file does not exist: {$credentialsPath}");
        }

        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([Calendar::CALENDAR]);

        $this->calendar = new Calendar($client);
    }

    public function createEvent(Appointment $appointment): string
    {
        $event = $this->calendar->events->insert(
            $this->calendarId,
            $this->eventPayload($appointment)
        );

        return $event->getId();
    }

    public function updateEvent(Appointment $appointment): string
    {
        if (! $appointment->google_event_id) {
            return $this->createEvent($appointment);
        }

        $event = $this->calendar->events->update(
            $this->calendarId,
            $appointment->google_event_id,
            $this->eventPayload($appointment)
        );

        return $event->getId();
    }

    public function deleteEvent(Appointment $appointment): void
    {
        if (! $appointment->google_event_id) {
            return;
        }

        try {
            $this->calendar->events->delete($this->calendarId, $appointment->google_event_id);
        } catch (\Google\Service\Exception $exception) {
            if ($exception->getCode() !== 404 && $exception->getCode() !== 410) {
                throw $exception;
            }
        }
    }

    private function eventPayload(Appointment $appointment): Event
    {
        $appointment->loadMissing(['customer', 'service', 'user']);

        return new Event([
            'summary' => $this->summary($appointment),
            'description' => $this->description($appointment),
            'start' => new EventDateTime([
                'dateTime' => $appointment->start_time->copy()->timezone($this->timezone)->toRfc3339String(),
                'timeZone' => $this->timezone,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $appointment->end_time->copy()->timezone($this->timezone)->toRfc3339String(),
                'timeZone' => $this->timezone,
            ]),
            'colorId' => $this->colorId($appointment),
        ]);
    }

    private function summary(Appointment $appointment): string
    {
        return trim(sprintf(
            '%s %s - %s',
            $appointment->customer?->first_name,
            $appointment->customer?->last_name,
            $appointment->service?->name,
        ));
    }

    private function description(Appointment $appointment): string
    {
        return collect([
            'Customer: '.trim($appointment->customer?->first_name.' '.$appointment->customer?->last_name),
            'Phone: '.($appointment->customer?->phone ?: '-'),
            'Email: '.($appointment->customer?->email ?: '-'),
            'Service: '.($appointment->service?->name ?: '-'),
            'Stylist: '.($appointment->user?->name ?: '-'),
            'Status: '.$appointment->status,
        ])->implode("\n");
    }

    private function colorId(Appointment $appointment): string
    {
        $availableColorIds = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'];
        $key = (string) ($appointment->user_id ?: $appointment->user?->email ?: 'default');

        return $availableColorIds[abs(crc32($key)) % count($availableColorIds)];
    }
}
