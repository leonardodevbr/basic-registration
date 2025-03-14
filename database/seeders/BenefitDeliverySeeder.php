<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BenefitDelivery;
use App\Models\Person;
use App\Models\Benefit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BenefitDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('benefit_deliveries')->truncate(); // Limpa os dados antes de inserir novos

        $users = User::pluck('id')->toArray();
        $benefits = Benefit::pluck('id')->toArray();

        // Se não houver usuários ou benefícios, criar alguns exemplos
        if (empty($users)) {
            $users = [User::factory()->create()->id];
        }

        if (empty($benefits)) {
            $benefits = [Benefit::factory()->create(['name' => 'Peixe Solidário'])->id];
        }

        // Lista fixa de selfies
        $selfieFiles = [
            "67d1a0e3e7f91.png", "67d1bd86e7919.png", "67d1c2b817415.png",
            "67d1c35b16cd2.png", "67d1c3a746067.png", "67d1c4e212fd3.png",
            "67d1c7ae462ba.png", "67d1c94984987.png", "67d1c9604e7a5.png",
            "67d1dfb22576e.png", "67d1dfb33d96f.png", "67d1e00fbb9b4.png",
            "67d250b75b811.png", "67d25a958b69d.png", "67d276a6952aa.png",
            "67d28c7683d78.png", "67d28d3a1eda6.png", "67d2bf9ff00d8.png",
            "67d347b2ecc47.png", "67d34ca84bd35.png", "67d3542203ec3.png",
            "67d35464a25a5.png", "67d35a7e0cefa.png", "67d35d0032fbb.png",
            "67d35eb9e59ae.png", "67d35f7ab1dd1.png", "67d35fb1c43dd.png",
            "67d396774d4f9.png", "67d3976ce820c.png", "67d397eaf3d32.png",
            "67d3986750b79.png"
        ];

        // Lista fixa de thumbs (não têm o mesmo nome das selfies)
        $thumbFiles = [
            "67d1a0e3e7f95.png", "67d1bd86e791e.png", "67d1c2b817417.png",
            "67d1c35b16cda.png", "67d1c3a74606e.png", "67d1c4e212fd6.png",
            "67d1c7ae462be.png", "67d1c94984989.png", "67d1c9604e7a8.png",
            "67d1dfb225771.png", "67d1dfb33d971.png", "67d1e00fbb9b6.png",
            "67d250b75b816.png", "67d25a958b6a6.png", "67d276a6952b2.png",
            "67d28c7683d7b.png", "67d28d3a1edb0.png", "67d2bf9ff00dc.png",
            "67d347b2ecc4c.png", "67d34ca84bd39.png", "67d3542203ed5.png",
            "67d35464a25a8.png", "67d35a7e0cefc.png", "67d35d0032fbd.png",
            "67d35eb9e59b1.png", "67d35f7ab1dd3.png", "67d35fb1c43e0.png",
            "67d396774d4fb.png", "67d3976ce8210.png", "67d397eaf3d37.png",
            "67d3986750b7d.png", "67d44737e0f0f.png"
        ];

        for ($i = 0; $i < 1000; $i++) {
            $selectedSelfie = fake()->randomElement($selfieFiles);
            $selectedThumb  = fake()->randomElement($thumbFiles);

            $random = rand(1, 100);
            if ($random <= 40) {
                $status = 'PENDING';  // 40%
            } elseif ($random <= 70) {
                $status = 'DELIVERED';  // 30%
            } elseif ($random <= 90) {
                $status = 'EXPIRED';  // 20%
            } else {
                $status = 'REISSUED';  // 10%
            }

            $deliveredAt = $status === 'DELIVERED' ? Carbon::now()->subDays(rand(0, 10)) : null;
            $ticketCode = random_int(100000, 999999);

            $person = Person::create([
                'name'        => fake()->name(),
                'cpf'         => fake()->unique()->numerify('###.###.###-##'),
                'phone'       => fake()->numerify('(##) #####-####'),
                'selfie_path' => "selfies/{$selectedSelfie}",
                'thumb_path'  => "selfies/thumbs/{$selectedThumb}",
            ]);

            BenefitDelivery::create([
                'benefit_id'    => fake()->randomElement($benefits),
                'person_id'     => $person->id,
                'ticket_code'   => $ticketCode,
                'valid_until'   => Carbon::now()->addDays(rand(-10, 10)),
                'status'        => $status,
                'registered_by' => fake()->randomElement($users),
                'delivered_by'  => $deliveredAt ? fake()->randomElement($users) : null,
                'delivered_at'  => $deliveredAt,
                'unit_id'       => null,
            ]);
        }

        echo "✅ Seed de 1000 registros gerado com sucesso!";
    }
}
