<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SURVEY_SCHEDULED = 'survey_scheduled';
    case READY_FOR_DELIVERY = 'ready_for_delivery';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::SURVEY_SCHEDULED => 'Survei Dijadwalkan',
            self::READY_FOR_DELIVERY => 'Siap Dikirim',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'amber',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::SURVEY_SCHEDULED => 'blue',
            self::READY_FOR_DELIVERY => 'violet',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'fa-clock',
            self::APPROVED => 'fa-check-circle',
            self::REJECTED => 'fa-times-circle',
            self::SURVEY_SCHEDULED => 'fa-calendar-check',
            self::READY_FOR_DELIVERY => 'fa-truck-fast',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-700";
    }

    public function requiresAction(): bool
    {
        return $this === self::PENDING;
    }

    public function isCancelled(): bool
    {
        return $this === self::REJECTED;
    }

    public function isTerminal(): bool
    {
        return $this === self::REJECTED;
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => $case->color(),
            'icon' => $case->icon(),
            'requires_action' => $case->requiresAction(),
        ], self::cases());
    }
}
