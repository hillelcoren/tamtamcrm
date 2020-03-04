<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //$this->call(GatewaySeeder::class);
        $this->call(DesignSeeder::class);
        //$this->call(PaymentTypeSeeder::class);
        //$this->call(IndustrySeeder::class);
        //$this->call(FrequencySeeder::class);
        //$this->call(PaymentStatusSeeder::class);
        //factory(App\User::class, 50)->create();
        /*$this->call(leadColumns::class); */
        //$this->call(CurrenciesSeeder::class);
        /*$this->call(SourceTypeSeeder::class);
        $this->call(TaskSeeder::class); */
    }

}
