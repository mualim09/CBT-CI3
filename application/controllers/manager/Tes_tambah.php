<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tes_tambah extends Member_Controller {
	private $kode_menu = 'tes-tambah';
	private $kelompok = 'tes';
	private $url = 'manager/tes_tambah';
	
    function __construct(){
		parent:: __construct();
		$this->load->model('cbt_modul_model');
		$this->load->model('cbt_tes_model');
        $this->load->model('cbt_tes_user_model');
		$this->load->model('cbt_tesgrup_model');
		$this->load->model('cbt_user_grup_model');
		$this->load->model('cbt_tes_topik_set_model');
		$this->load->model('cbt_modul_model');
        $this->load->model('cbt_topik_model');
        $this->load->model('cbt_konfigurasi_model');
        $this->load->model('cbt_soal_model');
        $this->load->model('Master_model', 'master');

       // parent::cek_akses($this->kode_menu);
	}
	
    public function index($tes_id=null){
        $data = [
			//'user' => $this->ion_auth->user()->row(),
			//'judul'	=> 'CBT SMKN PASTI BISA',
			//'subjudul' => 'Bank Soal',
		   'mapel'	=> $this->master->mapel(),
			'sesi'	=> $this->master->sesi(),
            'ruang'   => $this->master->ruang(),
			'jenis'	=> $this->master->getjenis()

		];
        $data['kode_menu'] = $this->kode_menu;
        $data['url'] = $this->url;

        $is_edit = 0;
        if(!empty($tes_id)){
        	$query_tes = $this->cbt_tes_model->count_by_kolom('tes_id', $tes_id)->row()->hasil;
        	if($query_tes>0){
        		$is_edit = 1;
        	}
        }
        /*
        $queryjenjang = $this->cbt_konfigurasi_model->get_by_kolom_limit('konfigurasi_kode', 'cbt_jenjang_sekolah', 1)->row()->konfigurasi_isi;
       
        if( $queryjenjang == 'sd' or $queryjenjang == 'smp'){
       
			$data['jenjang_pendidikan'] = 'disabled';
      
    }*/
     $data['tesid'] = $this->cbt_tes_model->getidtes()->row()->id;    
        $tanggal_awal = date('Y-m-d H:i');
        $tanggal_akhir = date('Y-m-d H:i', strtotime('+ 1 days'));
        
        $data['rentang_waktu'] = $tanggal_awal.' - '.$tanggal_akhir;

        $query_group = $this->cbt_user_grup_model->get_group();
        $query_jenis = $this->cbt_user_grup_model->get_jenis();
        $query_sesi = $this->cbt_user_grup_model->get_sesi();
        if($query_group->num_rows()>0){
        	$select = '';
        	$query_group = $query_group->result();
        	foreach ($query_group as $temp) {
        		if($is_edit!=0){
        			if($this->cbt_tesgrup_model->count_by_tes_and_group($tes_id, $temp->grup_id)->row()->hasil>0){
        				$select = $select.'<option value="'.$temp->grup_id.'" selected>'.$temp->grup_nama.'</option>';
        			}else{
        				$select = $select.'<option value="'.$temp->grup_id.'">'.$temp->grup_nama.'</option>';
        			}
        		}else{
        			$select = $select.'<option value="'.$temp->grup_id.'">'.$temp->grup_nama.'</option>';
        		}
        	}

        }else{
        	$select = '<option value="0">Tidak Ada Group</option>';
        }
        $data['select_group'] = $select;
        ///sesi
if($query_sesi->num_rows()>0){
            $sesi = '';
            $query_sesi = $query_sesi->result();
            foreach ($query_sesi as $temp) {
                if($is_edit!=0){
                    if($this->cbt_tesgrup_model->count_by_tes_and_group($tes_id, $temp->sesi_kode)->row()->hasil>0){
                        $sesi = $sesi.'<option value="'.$temp->sesi_kode.'" selected>'.$temp->sesi_nama.'</option>';
                    }else{
                        $sesi = $sesi.'<option value="'.$temp->sesi_kode.'">'.$temp->sesi_nama.'</option>';
                    }
                }else{
                    $sesi = $sesi.'<option value="'.$temp->sesi_kode.'">'.$temp->sesi_nama.'</option>';
                }
            }

        }else{
            $sesi = '<option value="0">Tidak Ada Group</option>';
        }
        $data['select_sesi'] = $sesi;
//
       if($query_jenis->num_rows()>0){
        	$select1 = '';
        	$query_jenis = $query_jenis->result();
        	foreach ($query_jenis as $temp) {
        		if($is_edit!=0){
        			if($this->cbt_tesgrup_model->count_by_tes_and_group($tes_id, $temp->jenis_id)->row()->hasil>0){
                        $select1 = '<option value="0" selected>Tidak Ada Group</option>';
        			}else{
                        $select1 = '<option value="0" selected>Tidak Ada Group</option>';
        			}
        		}else{
        			$select1 = '<option value="0" selected>Tidak Ada Group</option>';
        		}
        	}

        }else{
        	$select1 = '<option value="0" selected>Tidak Ada Group</option>';
        }
        $data['select_jenis'] = $select1;

        $query_modul = $this->cbt_modul_model->get_modul();
        $counter = 0;
        if($query_modul->num_rows()>0){
        	$select = '';
        	$query_modul = $query_modul->result();
        	foreach ($query_modul as $temp) {
                $select = $select.'<option value="'.$temp->modul_id.'">'.$temp->modul_nama.'</option>';
                $counter++;
        	}
        }
        if($counter==0){
        	$select = '<option value="kosong">Tidak Ada Modul</option>';
        }
        $data['select_modul'] = $select;


// topik
      if(isset($tes_id)){
        $modulid = $this->cbt_tes_topik_set_model->get_tessoal($tes_id);
        $modulid = $modulid->row();
        $query3 = $this->cbt_topik_model->get_by_kolom('topik_modul_id', $modulid->modul_id);
        $counter = 0;
        if($query3->num_rows()>0){
            $select3 = '';
            $query3 = $query3->result();
            foreach ($query3 as $temp) {
        $jml_soal = $this->cbt_soal_model->count_by_kolom('soal_topik_id', $temp->topik_id)->row()->hasil;
     $select3 = $select3.'<option value="'.$temp->topik_id.'" selected>'.$temp->topik_nama.' ['.$jml_soal.' soal]</option>';
                $counter++;
            }
        }
        if($counter==0){
            $select3 = '<option value="kosong">Tidak Ada Topik</option>';
        }

          $data['select_topik'] = $select3;
}

        if($is_edit!=0){
        	$data['data_tes'] = '
        		edit(\''.$tes_id.'\');
        	';
        }

      
$this->template->display_admin($this->kelompok.'/tes_tambah_view', 'Tambah Tes', $data);
    }

    function get_topik_by_modul($modul=null){
        $data['data']=0;

        $data['select_topik'] = '<option value="kosong">Tidak Ada Topik</option>';
        if(!empty($modul)){
            $data['data']=1;
        $query_topik = $this->cbt_topik_model->get_by_kolom_aktif('topik_modul_id', $modul, 1);
            if($query_topik->num_rows()){
                $query_topik = $query_topik->result();
                $select = '';
                foreach ($query_topik as $topik) {
                    $jml_soal = $this->cbt_soal_model->count_by_kolom('soal_topik_id', $topik->topik_id)->row()->hasil;
                    $select = $select.'<option value="'.$topik->topik_id.'">'.$topik->topik_nama.' ['.$jml_soal.' soal]</option>';
                }

                $data['select_topik'] = $select;
            }
        }

        echo json_encode($data);
    }
    function tambah_tes(){
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('tambah-nama', 'Nama Tes','required|strip_tags');
       // $this->form_validation->set_rules('tambah-deskripsi', 'Deskripsi Tes','required|strip_tags');
        $this->form_validation->set_rules('tambah-rentang-waktu', 'Rentang Waktu Pengerjaan Tes','required|strip_tags');
       // $this->form_validation->set_rules('tambah-jenis', 'Jenis Ujian','required|strip_tags');
     //   $this->form_validation->set_rules('tambah-pg', 'Passing Grade Ujian','required|strip_tags');
      //  $this->form_validation->set_rules('tambah-status', 'Status Ujian','required|strip_tags');
        $this->form_validation->set_rules('tambah-waktu', 'Waktu Pengerjaan Tes','required|integer|strip_tags');
     //   $this->form_validation->set_rules('tambah-group[]', 'Grup','required|strip_tags');
        $this->form_validation->set_rules('tambah-poin', 'Poin Objektif','required|numeric|strip_tags');
        $this->form_validation->set_rules('tambah-poin-essai', 'Poin Essai','required|numeric|strip_tags');
        $this->form_validation->set_rules('tambah-poin-salah', 'Poin Jawaban Salah','required|numeric|strip_tags');
        $this->form_validation->set_rules('tambah-poin-kosong', 'Poin Jawaban Kosong','required|numeric|strip_tags');
       // var_dump($this->input->post('soal-topik', true));
        if($this->form_validation->run() == TRUE){
        	$tes_id = $this->input->post('tambah-id', true);
        	$nama_lama = $this->input->post('tambah-nama-lama', true);
            $data['tes_nama'] = $this->input->post('tambah-nama', true);
          //  $data['tes_detail'] = $this->input->post('tambah-deskripsi', true);
            $data['tes_jenis'] = $this->input->post('tambah-jenis', true);
            $data['tes_status'] = $this->input->post('tambah-status', true);
            $data['tes_duration_time'] = $this->input->post('tambah-waktu', true);
            $data['tes_score_right'] = $this->input->post('tambah-poin', true);
            $data['tes_score_right_essai'] = $this->input->post('tambah-poin-essai', true);
            $data['tes_score_wrong'] = $this->input->post('tambah-poin-salah', true);
            $data['tes_score_unanswered'] = $this->input->post('tambah-poin-kosong', true);
            $data['tes_detail'] = $this->input->post('tambah-sesi', true);
            $data['tes_ruang'] = $this->input->post('tambah-ruang', true);
            $data['tes_pg'] = $this->input->post('selesai-tombol', true);
            $data2['tset_tes_id'] =  $this->input->post('soal-tes-id', true);
            $data2['tset_topik_id'] = $this->input->post('soal-topik', true);
            $data2['tset_tipe'] = $this->input->post('soal-tipe', true);
            $data2['tset_modul'] = $this->input->post('soal-modul', true);
            $data2['tset_difficulty'] = $this->input->post('soal-kesulitan', true);
            $data2['tset_jumlah'] = $this->input->post('soal-jml', true);
            $jmlhlama = $this->input->post('soal-jml', true);
            $tunjukkan_hasil = $this->input->post('tambah-tunjukkan-hasil', true);

             $tambah_putar= $this->input->post('tambah-putar', true);
            if(!empty($tambah_putar)){
                $data['tes_audio_play'] = $tambah_putar;
            }else{
                $data['tes_audio_play'] = '0';
            }
             $data_tes2 = $this->input->post('data_tes', true);
            if(!empty($tunjukkan_hasil)){
            	$data['tes_results_to_users'] = $tunjukkan_hasil;
            }else{
            	$data['tes_results_to_users'] = '0';
            }

            $detail_hasil = $this->input->post('tambah-detail-hasil', true);
            if(!empty($detail_hasil)){
                $data['tes_detail_to_users'] = $detail_hasil;
            }else{
                $data['tes_detail_to_users'] = '0';
            }
            
            $token = $this->input->post('tambah-token', true);
            if(!empty($token)){
            	$data['tes_token'] = $token;
            }else{
            	$data['tes_token'] = '0';
            }

 //untuk soal
           // var_dump($data2['tset_jumlah']);

$query = $this->cbt_konfigurasi_model->get_by_kolom_limit('konfigurasi_kode', 'cbt_jenjang_sekolah', 1);
        $jenjang = 'sd';
        if($query->num_rows()>0){
            $jenjang = $query->row()->konfigurasi_isi;
        }
    
    if($jenjang=='sma'){
        $op1 = 5;
          }else{
          $op1 = 4;
      }

          $data2['tset_jawaban'] = $op1;
            

            $acak_soal = $this->input->post('soal-acak-soal', true);
            if(!empty($acak_soal)){
                $data2['tset_acak_soal'] = $acak_soal;
            }else{
                $data2['tset_acak_soal'] = '0';
            }

            $acak_jawaban = $this->input->post('soal-acak-jawaban', true);
            if(!empty($acak_jawaban)){
                $data2['tset_acak_jawaban'] = $acak_jawaban;
            }else{
                $data2['tset_acak_jawaban'] = '0';
            }
            //untuk soal
            $rentang_waktu = $this->input->post('tambah-rentang-waktu', true);
            $tanggal = explode(" - ", $rentang_waktu);
            $data['tes_begin_time'] = $tanggal[0];
            $data['tes_end_time'] = $tanggal[1];

            $cek_nama = 1;
            if(!empty($nama_lama)){
            	if($nama_lama==$data['tes_nama']){
            		$cek_nama = 0;
            	}
            }
            $jml_soal = $this->cbt_soal_model->count_by_kolom('soal_topik_id', $data2['tset_topik_id'])->row()->hasil;
            if($cek_nama==1 and $this->cbt_tes_model->count_by_kolom('tes_nama', $data['tes_nama'])->row()->hasil>0 or $jml_soal<=1 or $this->cbt_tes_user_model->count_by_kolom('tesuser_tes_id', $data2['tset_tes_id'])->row()->hasil>0){
            	$status['status'] = 0;
            	$status['pesan'] = 'Jumlah soal '.$jml_soal;
            }else{
                $is_process = 1;
            	// Menyimpan data tes
            	if(empty($tes_id)){
            		$tes_id = $this->cbt_tes_model->save($data);
                   // $this->cbt_tes_topik_set_model->save($data2);
                    // simpan soal
                     $jml_soal = $this->cbt_soal_model->count_by_kolom('soal_topik_id', $data2['tset_topik_id'])->row()->hasil;

                // Apakah jml soal yang dimasukkan sudah sesuai dengan jumlah soal pada topik
                if($data2['tset_jumlah']<=$jml_soal){
                    // Cek apakah soal sudah ada pada tabel subject_set sebelumnya
                    if($this->cbt_tes_topik_set_model->count_by_test_topik($data2['tset_tes_id'], $data2['tset_topik_id'])->row()->hasil>0){
                        $status['status'] = 0;
                        $status['pesan'] = 'Data soal sudah terdapat pada Tes, silahkan cek kembali';
                    }else{
                        $this->cbt_tes_topik_set_model->save($data2);

                        $status['status'] = 1;
                        $status['pesan'] = 'Tes dan setting soal berhasil ditambahkan';

                        // update max score di table ctb_test
                        $data_tes['tes_max_score'] = $this->hitung_skor($data2['tset_tes_id']);
                        $this->cbt_tes_model->update('tes_id', $data2['tset_tes_id'], $data_tes);
                        $status['max_score']=$data_tes['tes_max_score'];
                    }
                }else{

                    $status['status'] = 0;
                    $status['pesan'] = $jml_soal;
                }

            	}else{
            		// Cek dulu apakah kondisi tes sudah berjalan
                    if($this->cbt_tes_user_model->count_by_kolom('tesuser_tes_id', $tes_id)->row()->hasil>0){
                        $data_time['tes_begin_time'] = $data['tes_begin_time'];
                        $data_time['tes_end_time'] = $data['tes_end_time'];
                       // $this->cbt_tes_model->update('tes_id', $tes_id, $data_time);

                        $status['status'] = 0;
                        $status['pesan'] = 'Rentang Waktu Tes saja yang dapat diubah, karena Tes masih digunakan.';
                        $is_process = 0;
                    }else{
                    $query2 = $this->cbt_tes_topik_set_model->get_tessoal($tes_id);
                    $query2 = $query2->row();
                     $this->cbt_tes_topik_set_model->update('tset_id',$query2->tset_id, $data2);
                        $this->cbt_tes_model->update('tes_id', $tes_id, $data);
                             //   var_dump($query2->tset_id);

                         $status['status'] = 0;
                         $status['status'] = 0;
                        $status['pesan'] = 'Bismillah.';
                    }
            	}

                if($is_process==1){
                    // Menyimpan data group yang mengikuti tes
                    $groups = $this->input->post('tambah-group', true);
                    // menghapus data group berdasarkan tes terlebih dahulu
                   $this->cbt_tesgrup_model->delete('tstgrp_tes_id', $tes_id);
                    foreach ($groups as $group) {
                        $data_group['tstgrp_tes_id'] = $tes_id;
                        $data_group['tstgrp_grup_id'] = $group;

                        // Jika group tidak kosong
                        if($group!=0){
                           $this->cbt_tesgrup_model->save($data_group);
                        }
                    }

                    // Mengupdate score maximal
                    $data_tes['tes_max_score'] = $this->hitung_skor($tes_id);
                    $this->cbt_tes_model->update('tes_id', $tes_id, $data_tes);
                    $status['max_score']=$data_tes['tes_max_score'];
                    

                    if(!empty($tes_id)){
                        $status['status'] = 1;
                        $status['tes_id'] = $tes_id;
                        $status['tes_nama'] = $data['tes_nama'];
                        $status['pesan'] = 'Tes dan Setting soal telah disimpan';
                    }else{
                        $status['status'] = 1;
                        $status['tes_id'] = $tes_id;
                        $status['pesan'] = 'Tes dan Setting soal telah disimpan';
                    }
                }
            }
     //   $modulid = $this->cbt_tes_topik_set_model->get_tessoal($tes_id);
       // $modulid = $modulid->row();
      //  $query3 = $this->cbt_topik_model->get_by_kolom('topik_modul_id', $modulid->modul_id);

// untuk soal
   /*  if(isset($data_tes2)){
        $cek = 'ok';
var_dump($data_tes2);
     }else{
        */

            
/*}*/

        }else{
            $status['status'] = 0;
            $status['pesan'] = validation_errors();
        }
        
        echo json_encode($status);
    }

    function tambah_soal(){
    	$this->load->library('form_validation');
        
        $this->form_validation->set_rules('soal-tes-id', 'Tes','required|strip_tags');
        $this->form_validation->set_rules('soal-modul', 'Tes','required|strip_tags');
        $this->form_validation->set_rules('soal-topik', 'Topik','required|strip_tags');
        $this->form_validation->set_rules('soal-jml', 'Jumlah Soal','required|greater_than[1]|integer|strip_tags');
        $this->form_validation->set_rules('soal-tipe', 'Tipe Soal','required|strip_tags');
        $this->form_validation->set_rules('soal-kesulitan', 'Tingkat Kesulitan','required|strip_tags');
       // $this->form_validation->set_rules('soal-jml-jawaban', 'Jumlah Jawaban','required|greater_than[2]|integer|strip_tags');
        
        if($this->form_validation->run() == TRUE){
        	$data['tset_tes_id'] = $this->input->post('soal-tes-id', true);
        	$data['tset_topik_id'] = $this->input->post('soal-topik', true);
            $data['tset_tipe'] = $this->input->post('soal-tipe', true);
            $data['tset_modul'] = $this->input->post('soal-modul', true);
        	$data['tset_difficulty'] = $this->input->post('soal-kesulitan', true);
            $data['tset_jumlah'] = $this->input->post('soal-jml', true);
 $query = $this->cbt_konfigurasi_model->get_by_kolom_limit('konfigurasi_kode', 'cbt_jenjang_sekolah', 1);
        $jenjang = 'sd';
        if($query->num_rows()>0){
            $jenjang = $query->row()->konfigurasi_isi;
        }
    
    if($jenjang='sma'){
        $op1 = 5;
          }else{
          $op1 = 4;
      }

          $data['tset_jawaban'] = $op1;
            

            $acak_soal = $this->input->post('soal-acak-soal', true);
            if(!empty($acak_soal)){
                $data['tset_acak_soal'] = $acak_soal;
            }else{
                $data['tset_acak_soal'] = '0';
            }

            $acak_jawaban = $this->input->post('soal-acak-jawaban', true);
            if(!empty($acak_jawaban)){
                $data['tset_acak_jawaban'] = $acak_jawaban;
            }else{
                $data['tset_acak_jawaban'] = '0';
            }
            

        	// Cek dulu apakah kondisi tes sudah berjalan
            if($this->cbt_tes_user_model->count_by_kolom('tesuser_tes_id', $data['tset_tes_id'])->row()->hasil>0){
                $status['status'] = 0;
                $status['pesan'] = 'Data Soal tidak bisa di tambah. Tes masih digunakan.';
            }else{
                $jml_soal = $this->cbt_soal_model->count_by_kolom('soal_topik_id', $data['tset_topik_id'])->row()->hasil;

                // Apakah jml soal yang dimasukkan sudah sesuai dengan jumlah soal pada topik
                if($data['tset_jumlah']<=$jml_soal){
                    // Cek apakah soal sudah ada pada tabel subject_set sebelumnya
                    if($this->cbt_tes_topik_set_model->count_by_test_topik($data['tset_tes_id'], $data['tset_topik_id'])->row()->hasil>0){
                        $status['status'] = 0;
                        $status['pesan'] = 'Data soal sudah terdapat pada Tes, silahkan cek kembali';
                    }else{
                        $this->cbt_tes_topik_set_model->save($data);

                        $status['status'] = 1;
                        $status['pesan'] = 'Soal berhasil ditambahkan';

                        // update max score di table ctb_test
                        $data_tes['tes_max_score'] = $this->hitung_skor($data['tset_tes_id']);
                        $this->cbt_tes_model->update('tes_id', $data['tset_tes_id'], $data_tes);
                        $status['max_score']=$data_tes['tes_max_score'];
                    }
                }else{
                    $status['status'] = 0;
                    $status['pesan'] = 'Jumlah soal pada Topik lebih sedikit dari Jumlah Soal yang diminta';
                }
            }   
        }else{
        	$status['status'] = 0;
            $status['pesan'] = validation_errors();
        }

        echo json_encode($status);
    }

    function hapus_soal_by_id($id=null){
    	$data['data'] = 0;
		if(!empty($id)){
			// Cek dulu apakah kondisi tes sudah berjalan
            $query_soal = $this->cbt_tes_topik_set_model->get_by_kolom_limit('tset_id', $id, 1)->row();
            if($this->cbt_tes_user_model->count_by_kolom('tesuser_tes_id', $query_soal->tset_tes_id)->row()->hasil>0){
                $data['data'] = 0;
                $data['pesan'] = 'Data Soal tidak bisa di hapus. Tes masih digunakan.';
            }else{
                // hapus soal dari tes
                $this->cbt_tes_topik_set_model->delete('tset_id', $id);
                $data['data'] = 1;
                $data['pesan'] = 'Data Soal berhasil dihapus';

                // update max score di table ctb_test
                $data_tes['tes_max_score'] = $this->hitung_skor($query_soal->tset_tes_id);
                $this->cbt_tes_model->update('tes_id', $query_soal->tset_tes_id, $data_tes);
                $data['max_score'] = $data_tes['tes_max_score'];
            }
		}
		echo json_encode($data);
    }

    private function hitung_skor($tes_id=null){
        $max_score = 0;
        if(!empty($tes_id)){
            $query_tes = $this->cbt_tes_model->get_by_kolom_limit('tes_id', $tes_id, 1);
            if($query_tes->num_rows()>0){
                $query_tes = $query_tes->row();
                $query_soal = $this->cbt_tes_topik_set_model->get_by_kolom('tset_tes_id', $tes_id)->result();
                foreach ($query_soal as $soal) {
                    $max_score = $max_score+($soal->tset_jumlah*$query_tes->tes_score_right);
                }
            }
        }
        return $max_score;
    }
    
    function get_by_id($id=null){
    	$data['data'] = 0;
		if(!empty($id)){
			$query = $this->cbt_tes_model->get_by_kolom('tes_id', $id);
           $query2 = $this->cbt_tes_topik_set_model->get_tessoal($id);

			if($query->num_rows()>0){
				$query = $query->row();
               $query2 = $query2->row();
            	$data['data'] = 1;
				$data['id'] = $query->tes_id;
				$data['nama'] = $query->tes_nama;
				$data['sesi'] = $query->tes_detail;
                $data['ruang'] = $query->tes_ruang;
                $data['waktu'] = $query->tes_duration_time;
                $data['jenis'] = $query->tes_jenis;
                $data['pg'] = $query->tes_pg;
	            $data['poin'] = $query->tes_score_right;
                $data['putar'] = $query->tes_audio_play;
                $data['poinessai'] = $query->tes_score_right_essai;
	            $data['poin_salah'] = $query->tes_score_wrong;
	            $data['poin_kosong'] = $query->tes_score_unanswered;
	            $data['tunjukkan_hasil'] = $query->tes_results_to_users;
                $data['detail_hasil'] = $query->tes_detail_to_users;
	            $data['token'] = $query->tes_token;
	            $data['rentang_waktu'] = $query->tes_begin_time.' - '.$query->tes_end_time;
                $data['modul_nama'] = $query2->modul_id;
                $data['jmlh'] = $query2->tset_jumlah;
               
			}
		}
       
      
		echo json_encode($data);
       
    }
    
    function get_datatable_soal(){
    	$tes_id = $this->input->get('tes-id');

		// variable initialization
		$search = "";
		$start = 0;
		$rows = 10;

		// get search value (if any)
		if (isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
			$search = $_GET['sSearch'];
		}

		// limit
		$start = $this->get_start();
		$rows = $this->get_rows();

		// run query to get user listing
		$query = $this->cbt_tes_topik_set_model->get_datatable($start, $rows, $tes_id);
		$iFilteredTotal = $query->num_rows();
		
		$iTotal= $this->cbt_tes_topik_set_model->get_datatable_count($tes_id)->row()->hasil;
	    
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
	        "iTotalRecords" => $iTotal,
	        "iTotalDisplayRecords" => $iTotal,
	        "aaData" => array()
	    );

	    // get result after running query and put it in array
		$i=$start;
		$query = $query->result();
	    foreach ($query as $temp) {			
			$record = array();
            
          
            
            $pg ='<b>MAPEL:</b>';
            $ket_acak = '';
            if($temp->tset_acak_soal==1){
                $ket_acak = $ket_acak.' <b>Acak Soal:</b>YA';
            }else{
                $ket_acak = $ket_acak.' <b>Acak Soal:</b>TDK';
            }
            if($temp->tset_acak_jawaban==1){
                $ket_acak = $ket_acak.'; <b>Acak JWB:</b>YA';
            }else{
                $ket_acak = $ket_acak.'; <b>Acak JWB:</b>TDK';
            }

			$query_topik = $this->cbt_topik_model->get_by_kolom_limit('topik_id', $temp->tset_topik_id, 1)->row();

            $record[] = '<b>'.$query_topik->topik_nama.'</b> ['.$temp->tset_jumlah.'] ['.$temp->tset_jawaban.'] ' .$pg.' '.$temp->modul_nama.''.$ket_acak;
            $record[] = '<a onclick="hapus_soal(\''.$temp->tset_id.'\')" title="Hapus Daftar Soal" style="cursor: pointer;" class="btn btn-default btn-xs"><i class="icon fa fa-trash fa-sm"></i></a>';

			$output['aaData'][] = $record;
		}
		// format it to JSON, this output will be displayed in datatable
        
		echo json_encode($output);
	}
	
	/**
	* funsi tambahan 
	* 
	* 
*/
	
	function get_start() {
		$start = 0;
		if (isset($_GET['iDisplayStart'])) {
			$start = intval($_GET['iDisplayStart']);

			if ($start < 0)
				$start = 0;
		}

		return $start;
	}

	function get_rows() {
		$rows = 10;
		if (isset($_GET['iDisplayLength'])) {
			$rows = intval($_GET['iDisplayLength']);
			if ($rows < 5 || $rows > 500) {
				$rows = 10;
			}
		}

		return $rows;
	}

	function get_sort_dir() {
		$sort_dir = "ASC";
		$sdir = strip_tags($_GET['sSortDir_0']);
		if (isset($sdir)) {
			if ($sdir != "asc" ) {
				$sort_dir = "DESC";
			}
		}

		return $sort_dir;
	}
}