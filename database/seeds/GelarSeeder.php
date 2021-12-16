<?php

use Illuminate\Database\Seeder;

class GelarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ref.gelar_akademik')->truncate();
		$json = File::get('database/data/gelar.json');
		$data = json_decode($json);
        foreach($data as $obj){
			DB::table('ref.gelar_akademik')->insert([
				'gelar_akademik_id' => $obj->gelar_akademik_id,
				'kode' 				=> $obj->kode,
				'nama' 				=> $obj->nama,
				'posisi_gelar'		=> $obj->posisi_gelar,
				'created_at' 		=> $obj->created_at,
				'updated_at' 		=> $obj->updated_at,
				'deleted_at'		=> $obj->deleted_at,
				'last_sync'			=> date('Y-m-d H:i:s'),
			]);
    	}
    }
}
