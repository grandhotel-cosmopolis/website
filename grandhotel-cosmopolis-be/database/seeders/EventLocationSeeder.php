<?php

namespace Database\Seeders;

use App\Models\EventLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') === 'local') {
            EventLocation::factory()
                ->count(5)
                ->create();
        }

        $this::createGrandhotelLocations();
    }

    private function createGrandhotelLocations(): void {
        if (EventLocation::query()->where('guid', '42936e15-853b-4d15-b873-90edf11f7ac5')->count() == 0) {
            $cafe = new EventLocation;
            $cafe->guid = '42936e15-853b-4d15-b873-90edf11f7ac5';
            $cafe->name = 'Grandhotel Cosmopolis';
            $cafe->street = 'Springergässchen 5';
            $cafe->city = '86152 Augsburg';
            $cafe->additional_information = 'Cafe';
            $cafe->save();
        }

        if(EventLocation::query()->where('guid', '47a90a69-0ccd-42a9-bb03-56adfbff3758')->count() == 0) {
            $souterrain = new EventLocation;
            $souterrain->guid = '47a90a69-0ccd-42a9-bb03-56adfbff3758';
            $souterrain->name = 'Grandhotel Cosmopolis';
            $souterrain->street = 'Springergässchen 5';
            $souterrain->city = '86152 Augsburg';
            $souterrain->additional_information = 'Souterrain';
            $souterrain->save();
        }

        if(EventLocation::query()->where('guid', 'a5700fc4-5b48-47b5-97a4-4db868a65fca')->count() == 0 ) {
            $mitmachWerkstatt = new EventLocation;
            $mitmachWerkstatt->guid = 'a5700fc4-5b48-47b5-97a4-4db868a65fca';
            $mitmachWerkstatt->name = 'Grandhotel Cosmopolis';
            $mitmachWerkstatt->street = 'Springergässchen 5';
            $mitmachWerkstatt->city = '86152 Augsburg';
            $mitmachWerkstatt->additional_information = 'Mitmachwerkstatt';
            $mitmachWerkstatt->save();
        }

        if(EventLocation::query()->where('guid', '67572c8f-1f7d-4c8b-a4f5-d2242108fa5c')->count() == 0) {
            $seminarRaum = new EventLocation;
            $seminarRaum->guid = '67572c8f-1f7d-4c8b-a4f5-d2242108fa5c';
            $seminarRaum->name = 'Grandhotel Cosmopolis';
            $seminarRaum->street = 'Springergässchen 5';
            $seminarRaum->city = '86152 Augsburg';
            $seminarRaum->additional_information = 'Seminarraum';
            $seminarRaum->save();
        }

        if(EventLocation::query()->where('guid', '65b25bd1-0f37-4258-a91d-3230f30b97a9')->count() == 0) {
            $noRoom = new EventLocation;
            $noRoom->guid = '65b25bd1-0f37-4258-a91d-3230f30b97a9';
            $noRoom->name = 'Grandhotel Cosmopolis';
            $noRoom->street = 'Springergässchen 5';
            $noRoom->city = '86152 Augsburg';
            $noRoom->save();
        }
    }
}
