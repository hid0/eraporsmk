<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Blade;
use CustomHelper;
use App\Setting;
use App\Sekolah;
use Illuminate\Support\Facades\Auth;
use Config;
use File;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    /*public function boot()
    {
        //
	}*/
	public function boot(Dispatcher $events){
		//
	}
	public function bootOld(Dispatcher $events){
		//dd(config());
		$with = '';
		$ta = '';
		$user = '';
		view()->composer('*', function($view) use ($with){
			$user = auth()->user();
			$ta = CustomHelper::get_ta();
			if ($view->getName() == 'adminlte::page' || $view->getName() == 'config' || $view->getName() == 'home') {
        		//$view->with('variable', 'Set variable');
				$user = auth()->user();
				if($user){
					$with = [
						'user' => $user,
						'sekolah'	=> Sekolah::with(['guru' => function($query){
							$query->with('gelar_depan');
							$query->with('gelar_belakang');
						}])->find($user->sekolah_id),
						'semester' => $ta,
					];
				}
				$view->with($with);
    		} else {
				if($user){
					$with = [
						'user' => $user,
						'semester' => $ta,
						'sekolah'	=> Sekolah::with(['guru' => function($query){
							$query->with('gelar_depan');
							$query->with('gelar_belakang');
						}])->find($user->sekolah_id),
					];
				}
				$view->with($with);
			}
		});
		if(Schema::hasTable('settings')){
			config([
				'global' => Setting::all([
					'key','value'
				])
				->keyBy('key') // key every setting by its name
				->transform(function ($setting) {
					return $setting->value; // return only the value
				})
				->toArray(),
				'site' => 
					[
						'app_name' 	=> 'e-Rapor SMK',
						'semester' 	=> $ta,
						'asd'		=> 'tambahan',
					]
			]);
		}
		$events->listen(BuildingMenu::class, function (BuildingMenu $event) use ($ta) {
			$url = parse_url(url('/'));
			$event->menu->add('periode');
			$event->menu->add([
				'text' => 'Beranda',
				'url' => 'home',
				'icon'  => 'dashboard',
				'active' => ['/', 'home'],
			]);
			if($url['host'] == 'localhost'){
				$event->menu->add([
					'text'        => 'Sinkronisasi',
					'url'         => '#',
					'icon'        => 'refresh',
					'permission'  		=> 'admin',
					'submenu' => [
						/*[
							'text' => 'eRapor 4.x.x',
							'url'  => 'sinkronisasi/erapor4',
							'icon' => 'refresh',
							'label'       => 'Offline',
							'label_color' => 'danger',
						],*/
						[
							'text' => 'Ambil Data Dapodik',
							'url'  => 'sinkronisasi/dapodik',
							'icon' => 'download',
							'label'       => 'Online',
							'label_color' => 'success',
							'active' => ['sinkronisasi/dapodik', 'sinkronisasi/guru', 'sinkronisasi/rombongan-belajar', 'sinkronisasi/ref-kd'],
						],
						[
							'text' => 'Kirim Data eRapor',
							'url'  => 'sinkronisasi/erapor',
							'icon' => 'upload',
							'label'       => 'Online',
							'label_color' => 'success',
						],
						[
							'text' => 'Kirim Nilai Dapodik',
							'url'  => 'sinkronisasi/kirim-nilai',
							'icon' => 'upload',
							'label'       => 'Offline',
							'label_color' => 'danger',
						],
					],
				]);
			} else {
				$event->menu->add([
					'text'        => 'Sinkronisasi',
					'url'         => '#',
					'icon'        => 'refresh',
					'permission'  		=> 'admin',
					'submenu' => [
						[
							'text' => 'Ambil Data Dapodik',
							'url'  => 'sinkronisasi/dapodik',
							'icon' => 'download',
							'label'       => 'Online',
							'label_color' => 'success',
							'active' => ['sinkronisasi/dapodik', 'sinkronisasi/guru', 'sinkronisasi/rombongan-belajar', 'sinkronisasi/ref-kd'],
						],
						[
							'text' => 'Kirim Data eRapor',
							'url'  => 'sinkronisasi/erapor',
							'icon' => 'upload',
							'label'       => 'Online',
							'label_color' => 'success',
						],
					],
				]);
			}
			$event->menu->add([
				'text' => 'Pengaturan',
				'url'  => '#',
				'icon'  => 'wrench',
				'permission'  => 'admin',
				'submenu' => [
					[
						'text' => 'Pengaturan Umum',
						'url'  => 'konfigurasi',
						'icon' => 'exchange',
					],
					[
						'text' => 'Hak Akses Pengguna',
						'url'  => 'users',
						'icon' => 'user',
						'active' => ['users', 'users/edit/*'],
					],
				],
			]);
			$event->menu->add([
				'text'	=> 'Referensi',
				'url'  => '#',
				'icon' => 'list',
				'permission'  => ['admin', 'guru'],
				'submenu' => [
					[
						'text' => 'Referensi GTK',
						'url'  => '#',
						'icon' => 'hand-o-right',
						'permission'  => 'admin',
						'submenu' => [
							[
								'text' => 'Referensi Guru',
								'url'  => 'guru',
								'icon' => 'graduation-cap',
							],
							[
								'text' => 'Referensi Tendik',
								'url'  => 'tendik',
								'icon' => 'graduation-cap',
							],
							[
								'text' => 'Referensi Instruktur',
								'url'  => 'instruktur',
								'icon' => 'graduation-cap',
								'active' => ['instruktur', 'tambah-instruktur'],
							],
							[
								'text' => 'Referensi Asesor',
								'url'  => 'asesor',
								'icon' => 'graduation-cap',
								'active' => ['asesor', 'tambah-asesor'],
							],
						],
					],
					[
						'text' => 'Referensi Rombel',
						'url'  => 'rombel',
						'icon' => 'hand-o-right',
						'permission'	=> ['admin', 'waka'],
					],
					[
						'text' => 'Referensi Peserta Didik',
						'url'  => '#',
						'icon' => 'hand-o-right',
						'submenu' => [
							[
								'text' => 'Peserta Didik Aktif',
								'url'  => 'pd-aktif',
								'icon' => 'users',
							],
							[
								'text' => 'Peserta Didik Keluar',
								'url'  => 'pd-keluar',
								'icon' => 'users',
								'icon_color'	=> 'red',
							],
						],
					],
					[
						'text' => 'Referensi Mata Pelajaran',
						'url'  => 'referensi/mata-pelajaran',
						'icon' => 'hand-o-right',
						'permission'	=> 'admin',
					],
					[
						'text' => 'Referensi Ekstrakurikuler',
						'url'  => 'referensi/ekskul',
						'icon' => 'hand-o-right',
						'permission'	=> 'admin',
					],
					[
						'text' => 'Referensi Teknik Penilaian',
						'url'  => 'referensi/metode',
						'icon' => 'hand-o-right',
						'permission'	=> 'admin',
						'active' => ['referensi/metode', 'referensi/tambah-metode', 'referensi/edit-metode/*'],
					],
					[
						'text' => 'Referensi Acuan Sikap',
						'url'  => 'referensi/sikap',
						'icon' => 'hand-o-right',
						'permission'	=> 'admin',
					],
					[
						'text' => 'Referensi Kompetensi Dasar',
						'url'  => 'referensi/kd',
						'icon' => 'hand-o-right',
						'permission'	=> 'guru',
					],
					[
						'text' => 'Referensi Uji Kompetensi',
						'url'  => 'referensi/ukk',
						'icon' => 'hand-o-right',
						'permission'	=> 'kaprog',
						'active' => ['referensi/ukk', 'referensi/tambah-ukk', 'referensi/tambah-unit-ukk/*'],
					],
				],
			]);
			$event->menu->add([
				'text'	=> 'Perencanaan',
				'url'  => '#',
				'icon' => 'check-square-o',
				'permission'  => 'guru',
				'submenu' => [
					[
						'text' => 'Rasio Nilai Akhir',
						'url'  => 'perencanaan/rasio',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Penilaian Pengetahuan',
						'url'  => 'perencanaan/pengetahuan',
						'icon' => 'hand-o-right',
						'active' => ['perencanaan/pengetahuan', 'perencanaan/tambah-pengetahuan', 'perencanaan/edit/1/*'],
					],
					[
						'text' => 'Penilaian Keterampilan',
						'url'  => 'perencanaan/keterampilan',
						'icon' => 'hand-o-right',
						'active' => ['perencanaan/keterampilan', 'perencanaan/tambah-keterampilan', 'perencanaan/edit/2/*'],
					],
					[
						'text' => 'Bobot Keterampilan',
						'url'  => 'perencanaan/bobot',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Penilaian UKK',
						'url'  => 'perencanaan/ukk',
						'icon' => 'hand-o-right',
						'active' => ['perencanaan/ukk', 'perencanaan/tambah-ukk'],
						'permission'	=> 'kaprog',
					],
				],
			]);
			$event->menu->add([
				'text'	=> 'Penilaian',
				'url'  => '#',
				'icon' => 'edit',
				'permission'  => ['guru', 'tu'],
				'submenu' => [
					[
						'text' => 'Penilaian Pengetahuan',
						'url'  => 'penilaian/pengetahuan',
						'icon' => 'hand-o-right',
						'permission'  => 'guru',
					],
					[
						'text' => 'Penilaian Keterampilan',
						'url'  => 'penilaian/keterampilan',
						'icon' => 'hand-o-right',
						'permission'  => 'guru',
					],
					[
						'text' => 'Penilaian Sikap',
						'url'  => 'penilaian/list-sikap',
						'icon' => 'hand-o-right',
						'active' => ['penilaian/list-sikap', 'penilaian/sikap'],
						'permission'  => 'guru',
					],
					[
						'text' => 'Penilaian Remedial',
						'url'  => 'penilaian/remedial',
						'icon' => 'hand-o-right',
						'permission'  => 'guru',
					],
					[
						'text' => 'Penilaian UKK',
						'url'  => 'penilaian/ukk',
						'icon' => 'hand-o-right',
						'permission'  => 'internal',
					],
					[
						'text' => 'Penilaian Ekstrakurikuler',
						'url'  => 'penilaian/ekskul',
						'icon' => 'hand-o-right',
						'permission'  => 'pembina_ekskul',
					],
				],
			]);
			if($ta && $ta->semester == 1){
				$event->menu->add([
					'text'	=> 'Laporan Hasil Belajar',
					'url'  => '#',
					'icon' => 'copy',
					'permission'  => ['wali', 'waka'],
					'submenu' => [
						[
							'text' => 'Catatan Akademik',
							'url'  => 'laporan/catatan-akademik',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Nilai Karakter',
							'url'  => 'laporan/nilai-karakter',
							'active' => ['laporan/nilai-karakter', 'laporan/tambah-nilai-karakter'],
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Ketidakhadiran',
							'url'  => 'laporan/ketidakhadiran',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Nilai Ekstrakurikuler',
							'url'  => 'laporan/nilai-ekskul',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Praktik Kerja Lapangan',
							'url'  => 'laporan/pkl',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Prestasi Peserta Didik',
							'url'  => 'laporan/prestasi',
							'icon' => 'hand-o-right',
							'active' => ['laporan/prestasi', 'laporan/tambah-prestasi'],
						],
						[
							'text' => 'Cetak Rapor UTS',
							'url'  => 'laporan/rapor-uts',
							'active' => ['laporan/rapor-uts', 'laporan/cetak-uts'],
							'icon' => 'print',
						],
						[
							'text' => 'Cetak Rapor Semester',
							'url'  => 'laporan/rapor-semester',
							'active' => ['laporan/rapor-semester', 'laporan/review-nilai/*', 'laporan/review-desc/*'],
							'icon' => 'print',
						],
						[
							'text' => 'Unduh Leger',
							'url'  => 'laporan/leger',
							'icon' => 'download',
						],
					],
				]);
			} else {
				$event->menu->add([
					'text'	=> 'Laporan Hasil Belajar',
					'url'  => '#',
					'icon' => 'copy',
					'permission'  => ['wali', 'waka'],
					'submenu' => [
						[
							'text' => 'Catatan Akademik',
							'url'  => 'laporan/catatan-akademik',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Nilai Karakter',
							'url'  => 'laporan/nilai-karakter',
							'active' => ['laporan/nilai-karakter', 'laporan/tambah-nilai-karakter'],
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Kehadiran',
							'url'  => 'laporan/kehadiran',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Nilai Ekstrakurikuler',
							'url'  => 'laporan/nilai-ekskul',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Praktik Kerja Lapangan',
							'url'  => 'laporan/pkl',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Prestasi Peserta Didik',
							'url'  => 'laporan/prestasi',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Kenaikan Kelas',
							'url'  => 'laporan/kenaikan',
							'icon' => 'hand-o-right',
						],
						[
							'text' => 'Cetak Rapor UTS',
							'url'  => 'laporan/rapor-uts',
							'active' => ['laporan/rapor-uts', 'laporan/cetak-uts'],
							'icon' => 'print',
						],
						[
							'text' => 'Cetak Rapor Semester',
							'url'  => 'laporan/rapor-semester',
							'active' => ['laporan/rapor-semester', 'laporan/review-nilai/*', 'laporan/review-desc/*'],
							'icon' => 'print',
						],
						[
							'text' => 'Unduh Leger',
							'url'  => 'laporan/leger',
							'icon' => 'download',
						],
					],
				]);
			}
			$event->menu->add([
				'text'	=> 'Monitoring Dan Analisis',
				'url'  => '#',
				'icon' => 'eye',
				'permission'  => 'guru',
				'submenu' => [
					[
						'text' => 'Rekap Nilai',
						'url'  => 'monitoring/rekap-nilai',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Analisis Hasil Penilaian',
						'url'  => 'monitoring/analisis-nilai',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Analisis Hasil Remedial',
						'url'  => 'monitoring/analisis-remedial',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Pencapaian Kompetensi',
						'url'  => 'monitoring/capaian-kompetensi',
						'icon' => 'hand-o-right',
					],
					[
						'text' => 'Prestasi Individu Peserta Didik',
						'url'  => 'monitoring/prestasi-individu',
						'icon' => 'hand-o-right',
					],
				],
			]);
			$event->menu->add([
				'text' => 'Profil Pengguna',
				'url'  => 'users/profile',
				'icon' => 'user',
				'permission'  => ['superadmin', 'admin', 'guru', 'siswa'],
				'active' => ['users/profile'],
			]);
			$event->menu->add([
				'text' => 'Daftar Perubahan',
				'url'  => 'changelog',
				'icon' => 'check-square-o',
				'permission'  => 'admin',
			]);
			$event->menu->add([
				'text' => 'Cek Pembaharuan',
				'url'  => 'check-update',
				'icon' => 'refresh',
				'permission'  => 'admin',
			]);
			$event->menu->add([
				'text' => 'Unduh Rekap Nilai Kelas XII',
				'url'  => 'rekap-nilai',
				'icon' => 'download',
				'permission'  => 'superadmin',
			]);
			$event->menu->add([
				'text'    => 'User Level',
				'icon'    => 'share',
				'permission'  => 'superadmin',
				'submenu' => [
					[
						'text' => 'Role',
						'url'  => 'role_index',
						'icon' => 'check',
						'active' => ['role_index', 'role_create'],
					],
					[
						'text' => 'Permission',
						'url'  => 'permission',
						'icon' => 'check',
						'active' => ['permission', 'permission_create'],
					],
					[
						'text' => 'Pengguna',
						'url'  => 'pengguna',
						'icon' => 'check',
						'active' => ['pengguna', 'tambah_pengguna'],
					],
				],
			]);
		});
	}
}
