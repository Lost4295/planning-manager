<?php

namespace App\Enum;

enum RepeatableEnum: int
{
    case Daily = 1;
    case Weekly = 2;
    case Monthly = 3;
    case Yearly = 4;

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Quotidien',
            self::Weekly => 'Hebdomadaire',
            self::Monthly => 'Mensuel',
            self::Yearly => 'Annuel',
        };
    }

    public function shift(\DateTimeInterface $base, int $index): \DateTimeImmutable
    {
        $base = \DateTimeImmutable::createFromInterface($base);
        $unit = match ($this) {
            self::Daily => 'day',
            self::Weekly => 'week',
            self::Monthly => 'month',
            self::Yearly => 'year',
        };
        $result = $base->modify(sprintf('+%d %s', $index, $unit));

        // Évite le débordement (ex : 31 janvier + 1 mois => 3 mars) en se calant sur la fin du mois
        if (in_array($this, [self::Monthly, self::Yearly], true) && $result->format('j') !== $base->format('j')) {
            $result = $result->modify('last day of previous month');
        }

        return $result;
    }
}
