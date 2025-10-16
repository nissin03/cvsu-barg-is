<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AddonReservation;
use App\Models\AddonPayment;
use App\Models\AddonTransaction;
use App\Models\TransactionReservation;

class AddonTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $trxIndex = TransactionReservation::query()
            ->with('availability:id,date_from,date_to')
            ->get()
            ->mapWithKeys(function ($trx) {
                $from = optional($trx->availability)->date_from;
                $to   = optional($trx->availability)->date_to;
                $k = $this->keyFor($from, $to);
                return [$k => $trx->id];
            });

        AddonReservation::query()
            ->orderBy('id')
            ->get()
            ->each(function (AddonReservation $ar) use ($trxIndex) {
                $k = $this->keyFor($ar->date_from, $ar->date_to);

                $transactionReservationId = $trxIndex[$k] ?? $this->fallbackTransactionId($trxIndex, $ar);

                if (!$transactionReservationId) {
                    return;
                }

                $paymentId = AddonPayment::where('addon_reservation_id', $ar->id)->value('id');

                AddonTransaction::updateOrCreate(
                    [
                        'transaction_reservation_id' => $transactionReservationId,
                        'addon_id'                   => $ar->addon_id,
                        'addon_reservation_id'       => $ar->id,
                    ],
                    [
                        'addon_payment_id' => $paymentId,
                        'status'           => 'unpaid',
                    ]
                );
            });
    }

    private function keyFor(?string $from, ?string $to): string
    {
        $f = $from ?: 'NULL';
        $t = $to   ?: 'NULL';
        return "{$f}|{$t}";
    }

    private function fallbackTransactionId($trxIndex, AddonReservation $ar): ?int
    {
        if ($ar->date_from && !$ar->date_to) {
            foreach ($trxIndex as $k => $id) {
                [$f, $t] = explode('|', $k);
                if ($f === $ar->date_from) {
                    return (int) $id;
                }
            }
        }
        if (!$ar->date_from && !$ar->date_to) {
            $key = $this->keyFor(null, null);
            if (isset($trxIndex[$key])) {
                return (int) $trxIndex[$key];
            }
        }
        return collect($trxIndex)->first() ?: null;
    }
}
