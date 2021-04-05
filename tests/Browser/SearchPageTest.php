<?php

namespace Tests\Browser;

use App\Models\Availability;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\DateTimePicker;
use Tests\DuskTestCase;

class SearchPageTest extends DuskTestCase
{

    use DatabaseMigrations;

    private array $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];


    public function testWhenBookRoomCustomDateRestrictions()
    {
        (new RolesAndPermissionsSeeder())->run();
        $room = Room::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::where('name', 'super-admin')->first();
        $room->dateRestrictions()->sync([
            $role->id => [
                'min_days_advance' => 30,
                'max_days_advance' => 31
            ]
        ]);

        foreach ($this->weekdays as $weekday) {
            Availability::create([
                'weekday' => $weekday,
                'opening_hours' => '07:00',
                'closing_hours' => '23:00',
                'room_id' => $room->id
            ]);
        }
        $this->browse(function (Browser $browser) use ($room, $admin) {
            $browser->loginAs($admin);


            $startId = 'start_time_0';
            $endId = 'end_time_0';
            $browser->visit('/bookings/search')
                ->assertSee($room->name)
                ->click('@room-select-' . $room->id)
                ->mouseover('@createBookingModal')
                ->mouseover('#addAnotherDate');

            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(10,13);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(10,14);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
                ->assertSee("later than 30 days");


            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(40,13);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(40,14);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
                ->assertSee("sooner than 31 days");
        });
    }

    public function testWhenBookRoomDateFail()
    {
        (new RolesAndPermissionsSeeder())->run();
        $room = Room::factory()->create(["min_days_advance"=> 2, "max_days_advance"  => 5]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::where('name', 'super-admin')->first();
    
        foreach ($this->weekdays as $weekday) {
            Availability::create([
                'weekday' => $weekday,
                'opening_hours' => '07:00',
                'closing_hours' => '23:00',
                'room_id' => $room->id
            ]);
        }
        $this->browse(function (Browser $browser) use ($room, $admin) {
            $browser->loginAs($admin);


            $startId = 'start_time_0';
            $endId = 'end_time_0';
            $browser->visit('/bookings/search')
                ->assertSee($room->name)
                ->click('@room-select-' . $room->id)
                ->mouseover('@createBookingModal')
                ->mouseover('#addAnotherDate');

            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(1,13);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(1,14);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
              ->assertSee("later than 2 days");

            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(10,13);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(10,14);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
              ->assertSee("sooner than 5 days");

        });
    }

        public function testWhenBookRoomTimefail()
    {
        (new RolesAndPermissionsSeeder())->run();
        $room = Room::factory()->create(["min_days_advance"=> 0, "max_days_advance"  => 5]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::where('name', 'super-admin')->first();
    
        foreach ($this->weekdays as $weekday) {
            Availability::create([
                'weekday' => $weekday,
                'opening_hours' => '07:00',
                'closing_hours' => '23:00',
                'room_id' => $room->id
            ]);
        }
        $this->browse(function (Browser $browser) use ($room, $admin) {
            $browser->loginAs($admin);


            $startId = 'start_time_0';
            $endId = 'end_time_0';
            $browser->visit('/bookings/search')
                ->assertSee($room->name)
                ->click('@room-select-' . $room->id)
                ->mouseover('@createBookingModal')
                ->mouseover('#addAnotherDate');

            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(1,1);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(1,2);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
              ->assertSee("These dates and times are not within the room's availabilities!");

        });
    }

    public function testWhenBookRoomSuccess()
    {
        (new RolesAndPermissionsSeeder())->run();
        $room = Room::factory()->create(["min_days_advance"=> 2, "max_days_advance"  => 5]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::where('name', 'super-admin')->first();
    
        foreach ($this->weekdays as $weekday) {
            Availability::create([
                'weekday' => $weekday,
                'opening_hours' => '07:00',
                'closing_hours' => '23:00',
                'room_id' => $room->id
            ]);
        }
        $this->browse(function (Browser $browser) use ($room, $admin) {
            $browser->loginAs($admin);


            $startId = 'start_time_0';
            $endId = 'end_time_0';
            $browser->visit('/bookings/search')
                ->assertSee($room->name)
                ->click('@room-select-' . $room->id)
                ->mouseover('@createBookingModal')
                ->mouseover('#addAnotherDate');

            $browser->within( new DateTimePicker($startId), function($browser) {
                $browser->setDatetime(3,13);
            })->pause(1000);

            $browser->within( new DateTimePicker($endId), function($browser) {
                $browser->setDatetime(3,14);
            })->pause(1000);

            $browser->click("#createBookingRequest")->pause(5000)
                ->assertpathis("/bookings/create");

        });
    }

    public function testWhenUpdateRoomRestrictions()
    {
        (new RolesAndPermissionsSeeder())->run();

        $room = Room::factory()->create(['name'=>'qwertqwert']);
        $room2 = Room::factory()->create(['name'=>'asdfasdf']);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $role = Role::where('name', 'super-admin')->first();
        $room->restrictions()->sync($role);

        $this->browse(function (Browser $browser) use ($room, $admin, $room2) {
            $browser->loginAs($admin);
            $browser->visit('/bookings/search')
                ->assertSee($room2->name)
                ->assertDontSee($room->name);
        });
    }
    public function testViewRoomDetails()
    {
        (new RolesAndPermissionsSeeder())->run();

        $room = Room::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->browse(function (Browser $browser) use ($room, $admin) {
            $browser->loginAs($admin);
            $browser->visit('/bookings/search')
                ->assertSee($room->name)
                ->press('VIEW DETAILS')->pause(500)
            ->assertSee("Detailed View of ".$room->name);
        });
    }

}
