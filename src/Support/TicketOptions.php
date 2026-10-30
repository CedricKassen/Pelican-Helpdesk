<?php

namespace CedricKassen\Helpdesk\Support;

use CedricKassen\Helpdesk\Enums\TicketCategory;
use CedricKassen\Helpdesk\Enums\TicketPriority;
use CedricKassen\Helpdesk\Models\HelpdeskSetting;
use Illuminate\Support\Str;

/**
 * Resolves the admin-configurable ticket categories and priorities.
 *
 * Each item is an array: ['value' => string, 'label' => string, 'color' => string].
 * Values are persisted in the helpdesk_settings table; when nothing is configured
 * (fresh install) the defaults are derived from the TicketCategory / TicketPriority enums.
 */
class TicketOptions
{
    public const SETTING_CATEGORIES = 'categories';

    public const SETTING_PRIORITIES = 'priorities';

    /** @var array<string, array<int, array{value: string, label: string, color: string}>> */
    protected static array $cache = [];

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function categories(): array
    {
        return static::resolve(self::SETTING_CATEGORIES, static fn () => static::defaultsFromEnum(TicketCategory::cases()));
    }

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function priorities(): array
    {
        return static::resolve(self::SETTING_PRIORITIES, static fn () => static::defaultsFromEnum(TicketPriority::cases()));
    }

    /** @return array<string, string> value => label */
    public static function categoryOptions(): array
    {
        return static::options(static::categories());
    }

    /** @return array<string, string> value => label */
    public static function priorityOptions(): array
    {
        return static::options(static::priorities());
    }

    public static function categoryLabel(?string $value): string
    {
        return static::label(static::categories(), $value);
    }

    public static function priorityLabel(?string $value): string
    {
        return static::label(static::priorities(), $value);
    }

    public static function categoryColor(?string $value): string
    {
        return static::color(static::categories(), $value);
    }

    public static function priorityColor(?string $value): string
    {
        return static::color(static::priorities(), $value);
    }

    public static function defaultCategory(): ?string
    {
        return static::categories()[0]['value'] ?? null;
    }

    public static function defaultPriority(): ?string
    {
        $priorities = static::priorities();

        foreach ($priorities as $priority) {
            if ($priority['value'] === 'normal') {
                return 'normal';
            }
        }

        return $priorities[0]['value'] ?? null;
    }

    /**
     * Normalise raw repeater input into stored items (deriving a stable value from the label).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function normalize(array $items): array
    {
        $result = [];
        $seen = [];

        foreach ($items as $item) {
            $label = trim((string) ($item['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $value = trim((string) ($item['value'] ?? ''));

            if ($value === '') {
                $value = Str::slug($label) ?: Str::slug(Str::random(6));
            }

            // Guarantee unique values.
            $base = $value;
            $i = 2;
            while (in_array($value, $seen, true)) {
                $value = $base . '-' . $i++;
            }
            $seen[] = $value;

            $color = (string) ($item['color'] ?? 'gray');

            $result[] = [
                'value' => $value,
                'label' => $label,
                'color' => $color,
            ];
        }

        return $result;
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }

    /**
     * @param  callable():array<int, array{value: string, label: string, color: string}>  $default
     * @return array<int, array{value: string, label: string, color: string}>
     */
    protected static function resolve(string $key, callable $default): array
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $stored = rescue(fn () => HelpdeskSetting::get($key), null, false);

        $items = is_array($stored) && $stored !== [] ? $stored : $default();

        return static::$cache[$key] = $items;
    }

    /**
     * @param  array<int, \UnitEnum>  $cases
     * @return array<int, array{value: string, label: string, color: string}>
     */
    protected static function defaultsFromEnum(array $cases): array
    {
        return array_map(static function ($case) {
            return [
                'value' => $case->value,
                'label' => method_exists($case, 'getLabel') ? $case->getLabel() : Str::headline($case->value),
                'color' => method_exists($case, 'getColor') ? $case->getColor() : 'gray',
            ];
        }, $cases);
    }

    /**
     * @param  array<int, array{value: string, label: string, color: string}>  $items
     * @return array<string, string>
     */
    protected static function options(array $items): array
    {
        $options = [];

        foreach ($items as $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

    /**
     * @param  array<int, array{value: string, label: string, color: string}>  $items
     */
    protected static function label(array $items, ?string $value): string
    {
        foreach ($items as $item) {
            if ($item['value'] === $value) {
                return $item['label'];
            }
        }

        return Str::headline((string) $value);
    }

    /**
     * @param  array<int, array{value: string, label: string, color: string}>  $items
     */
    protected static function color(array $items, ?string $value): string
    {
        foreach ($items as $item) {
            if ($item['value'] === $value) {
                return $item['color'];
            }
        }

        return 'gray';
    }
}
