<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;

use App\Models\Facility;
use App\Models\FacilityAttribute;
use App\Models\Price;
use App\Models\Availability;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\TransactionReservation;
use App\Models\QualificationApproval;

class FacilityAndRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Copy image & document from public/storage into storage/app/public
        Storage::disk('public')->putFileAs(
            'facilities',
            new File(public_path('storage/facilities/seeder.jpg')),
            'seeder.jpg'
        );
        Storage::disk('public')->putFileAs(
            'facilities/thumbnails',
            new File(public_path('storage/facilities/thumbnails/seeder.jpg')),
            'seeder.jpg'
        );
        Storage::disk('public')->putFileAs(
            'facilities',
            new File(public_path('storage/facilities/seeder.docx')),
            'seeder.docx'
        );

        // 2) Create the Facility
        $facility = Facility::create([
            'name'                  => 'Seeder Facility',
            'facility_type'         => 'individual',
            'slug'                  => Str::slug('Seeder Facility'),
            'description'           => 'This is a seeded facility for testing purposes.',
            'rules_and_regulations' => 'Please follow all standard rules.',
            'requirements'          => 'facilities/seeder.docx',
            'featured'              => false,
            'image'                 => 'facilities/seeder.jpg',
            'images'                => json_encode(['facilities/seeder.jpg']),
            'status'                => true,
            'archived'              => false,
            'archived_at'           => null,
            'created_by'            => 1,  // ensure a user with ID=1 exists
        ]);

        // 3) Facility Attributes
        $attribute = FacilityAttribute::create([
            'facility_id'     => $facility->id,
            'room_name'       => 'Seeder Room',
            'capacity'        => 10,
            'whole_capacity'  => 100,
            'sex_restriction' => null,
        ]);

        // 4) Price
        $price = Price::create([
            'facility_id'         => $facility->id,
            'name'                => 'Standard Rate',
            'value'               => 100.00,
            'price_type'          => 'individual',
            'is_based_on_days'    => false,
            'is_there_a_quantity' => false,
            'date_from'           => null,
            'date_to'             => null,
        ]);

        // 5) Availability
        $availability = Availability::create([
            'facility_id'           => $facility->id,
            'facility_attribute_id' => $attribute->id,
            'remaining_capacity'    => $attribute->capacity,
            'date_from'             => now()->toDateString(),
            'date_to'               => now()->addDay()->toDateString(),
        ]);

        // 6) Payment
        $payment = Payment::create([
            'availability_id' => $availability->id,
            'user_id'         => 1,
            'status'          => 'pending',
            'total_price'     => $price->value,
        ]);

        // 7) Payment Detail
        PaymentDetail::create([
            'payment_id'   => $payment->id,
            'facility_id'  => $facility->id,
            'quantity'     => 1,
            'total_price'  => $price->value,
        ]);

        // 8) Transaction Reservation
        TransactionReservation::create([
            'availability_id'        => $availability->id,
            'facility_attribute_id'  => $attribute->id,
            'price_id'               => $price->id,
            'quantity'               => 1,
            'user_id'                => 1,
            'status'                 => 'pending',
        ]);

        // 9) Qualification Approval
        QualificationApproval::create([
            'availability_id' => $availability->id,
            'user_id'         => 1,
            'qualification'   => null,
            'status'          => 'pending',
        ]);
    }
}
