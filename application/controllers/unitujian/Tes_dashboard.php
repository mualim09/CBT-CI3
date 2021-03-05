<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tes_dashboard extends Tes_Controller {
	private $kelompok = 'ujian';
	private $url = 'tes_dashboard';
	
    function __construct(){
		parent:: __construct();
		$this->load->model('cbt_user_model');
		$this->load->model('cbt_user_grup_model');
		$this->load->model('cbt_tes_model');
		$this->load->model('cbt_tes_token_model');
		$this->load->model('cbt_tes_topik_set_model');
		$this->load->model('cbt_tes_user_model');
		$this->load->model('cbt_tesgrup_model');
		$this->load->model('cbt_soal_model');
		$this->load->model('cbt_jawaban_model');
		$this->load->model('cbt_tes_soal_model');
		$this->load->model('cbt_tes_soal_jawaban_model');
		$this->load->model('Master_model', 'master');
	}
    
    public function index(){
		if (!$this->db->table_exists('cbt_remidi')){
			$cdbremidi = $this->cbt_tes_model->set_remidi();
		   }else{
			//$cdbremidi = $this->cbt_tes_model->set_remidi();
		   }
		$this->load->helper('form');
        $data['nama'] = $this->access_tes->get_nama();
		$data['group'] = $this->access_tes->get_group();
		$data['namaasli'] = $this->access_tes->get_nama_asli();
        $data['url'] = $this->url;
        $data['timestamp'] = strtotime(date('Y-m-d H:i:s'));
        $data['pengumuman'] = $this->master->getDataInfoPm();
        $username = $this->access_tes->get_username();
        $user_id = $this->cbt_user_model->get_by_kolom_limit('user_name', $username, 1)->row()->user_id;
        $query_tes = $this->cbt_tes_user_model->get_by_user_status($user_id);
        if($query_tes->num_rows()>0){
        	$query_tes = $query_tes->result();
        	$tanggal = new DateTime();
        	foreach ($query_tes as $tes) {
        	
            	$tanggal_tes = new DateTime($tes->tesuser_creation_time);
            	$tanggal_tes->modify('+'.$tes->tes_duration_time.' minutes');
            	if($tanggal>$tanggal_tes){
            		
            		$data_tes['tesuser_status']=4;
            		$this->cbt_tes_user_model->update('tesuser_id', $tes->tesuser_id, $data_tes);
            	}
        	}
        }


        $this->template->display_tes_mulai($this->kelompok.'/tes_dashboard_view2', 'Dashboard', $data);
    }

    /**
     * Cek
     *
     * @param      <type>  $tes_id  The tes identifier
     */
    function konfirmasi_test($tes_id=null){
		$cdbremidi = $this->cbt_tes_model->del_remidi();
    	if(!empty($tes_id)){
			$query_tes = $this->cbt_tes_model->get_by_kolom_limit('tes_id', $tes_id, 1);
			$query_jml = $this->cbt_tes_model->get_jml($tes_id)->row()->jml;
    		if($query_tes->num_rows()>0){
    			$query_tes = $query_tes->row();

    			$tanggal = new DateTime();
    			$tanggal_tes = new DateTime($query_tes->tes_end_time);
    			if($tanggal<$tanggal_tes){
    				
    				$username = $this->access_tes->get_username();
        			$user_id = $this->cbt_user_model->get_by_kolom_limit('user_name', $username, 1)->row()->user_id;

    				if($this->cbt_tes_user_model->count_by_user_tes($user_id, $query_tes->tes_id)->row()->hasil==0){
    					
    					$data['tes_id'] = $query_tes->tes_id;
	    				$data['nama'] = $this->access_tes->get_nama();
						$data['group'] = $this->access_tes->get_group();
						$data['namaasli'] = $this->access_tes->get_nama_asli();
				        $data['timestamp'] = strtotime(date('Y-m-d H:i:s'));
				        $data['url'] = $this->url;
				        $data['tes_nama'] = $query_tes->tes_nama;
				        $data['tes_waktu'] = $query_tes->tes_duration_time.' menit';
				        $data['tes_poin'] = $query_tes->tes_score_right;
						$data['tes_max_score'] = $query_tes->tes_max_score;
						$data['tes_pg'] = $query_tes->tes_pg;
						$data['tes_kkm'] = $query_tes->modul_kkm;
						$data['jml'] = $query_jml;
				        if($query_tes->tes_token==1){
				        	$data['tes_token'] = '
				        		<tr style="height: 45px;">
		                            <td></td>
		                            <td style="vertical-align: middle;text-align: right;"><b>Token : </b></td>
		                            <td style="vertical-align: middle;"><input type="text"  class="form-control" type="text" name="token" id="token" autocomplete="off"></td>
		                            <td></td>
		                        </tr>
				        	';
				        }else{
				        	$data['tes_token'] = '<input type="hidden" name="token" id="token">';
				        }

				        if($data['tes_max_score']>0){
				        	$this->template->display_tes_mulai($this->kelompok.'/tes_start_view', 'Mulai Tes', $data);	
				        }else{
				        	redirect('tes_dashboard');
				        }
		            }else{
		            	redirect('tes_dashboard');	
		            }
    			}else{
    				redirect('tes_dashboard');	
    			}
    		}else{
    			redirect('tes_dashboard');
    		}
    	}else{
    		redirect('tes_dashboard');
    	}
    }

    
    function mulai_tes(){

    	$this->load->library('form_validation');
        
		$this->form_validation->set_rules('tes-id', 'Tes','required|strip_tags');
        
        if($this->form_validation->run() == TRUE){
			$tes_id = $this->input->post('tes-id', TRUE);
			$token = $this->input->post('token', TRUE);
			
			$username = $this->access_tes->get_username();
			$user_id = $this->cbt_user_model->get_by_kolom_limit('user_name', $username, 1)->row()->user_id;

			$query_tes = $this->cbt_tes_model->get_by_kolom_limit('tes_id', $tes_id, 1);
			if($query_tes->num_rows()>0){
				$query_tes = $query_tes->row();
				
				if($this->cbt_tes_user_model->count_by_user_tes($user_id, $tes_id)->row()->hasil==0){
					
					$is_ok = 1;
					if($query_tes->tes_token==1){
						if(empty($token)){
							$is_ok = 0;
						}else{
							
							$query_token = $this->cbt_tes_token_model->get_by_token_now_limit($token, 1);
							if($query_token->num_rows()>0){
								$query_token = $query_token->row();
								
								// Mengecek token apakah dapat digunakan oleh semua TES
								if($query_token->token_tes_id==0){
									
									if($query_token->token_aktif==1){
										$data_tes['tesuser_token'] = $token;
									}else{
										if($this->cbt_tes_token_model->count_by_token_lifetime($token, $query_token->token_aktif)->row()->hasil>0){
											$data_tes['tesuser_token'] = $token;
										}else{
											$is_ok = 0;
										}
									}
								}else{
									
									if($query_token->token_tes_id==$tes_id){
										if($query_token->token_aktif==1){
											$data_tes['tesuser_token'] = $token;
										}else{
											if($this->cbt_tes_token_model->count_by_token_lifetime($token, $query_token->token_aktif)->row()->hasil>0){
												$data_tes['tesuser_token'] = $token;
											}else{
												$is_ok = 0;
											}
										}
									}else{
										$is_ok = 0;									
									}
								}
							}else{
								$is_ok = 0;
							}
						}
					}
					if($is_ok==1){
						
						if($this->cbt_tes_topik_set_model->count_by_kolom('tset_tes_id', $query_tes->tes_id)->row()->hasil>0){
							
							$this->db->trans_start();

							
							$data_tes['tesuser_tes_id'] = $tes_id;
							$data_tes['tesuser_user_id'] = $user_id;
							$data_tes['tesuser_status'] = 1;
							$data_tes['tesuser_creation_time'] = date('Y-m-d H:i:s');

							$tests_users_id = $this->cbt_tes_user_model->save($data_tes);

							// Mengambil data topik yang ada pada tes
							$query_subject_set = $this->cbt_tes_topik_set_model->get_by_kolom('tset_tes_id', $tes_id)->result();
							$i_soal = 0;
							// Mengambil data topik berdasarkan tes
							
							foreach ($query_subject_set as $subject_set) {
								
								if($subject_set->tset_acak_soal==1){
									$query_soal = $this->cbt_soal_model->get_by_topik_tipe_kesulitan_select_limit($subject_set->tset_topik_id, $subject_set->tset_tipe, $subject_set->tset_difficulty, 'soal_id,soal_topik_id,soal_tipe,soal_audio', $subject_set->tset_jumlah);
								}else{
									$query_soal = $this->cbt_soal_model->get_by_topik_tipe_kesulitan_select_limit_tanpa_acak($subject_set->tset_topik_id, $subject_set->tset_tipe, $subject_set->tset_difficulty, 'soal_id,soal_topik_id,soal_tipe,soal_audio', $subject_set->tset_jumlah);
								}
								//header('Location: google.com');
								if($query_soal->num_rows()>0){
									$query_soal = $query_soal->result();
									$insert_soal = array();
									foreach ($query_soal as $soal) {
										// Memasukkan data soal ke table tests_logs
										$data_soal['tessoal_tesuser_id'] = $tests_users_id;
										$data_soal['tessoal_soal_id'] = $soal->soal_id;
										//$data_soal['tessoal_nilai'] = 0;
										$data_soal['tessoal_nilai'] = $query_tes->tes_score_unanswered;
										$data_soal['tessoal_creation_time'] = date('Y-m-d H:i:s');
										$data_soal['tessoal_order'] = ++$i_soal;

										$insert_soal[] = $data_soal;
										//var_dump($tests_users_id);
										//var_dump($soal->soal_id);
										//var_dump($query_tes->tes_score_unanswered);
										//var_dump($insert_soal = $data_soal['tessoal_tesuser_id']);
									}
									// menggunakan batch query langsung untuk mengehemat waktu dan memory
									
									$this->cbt_tes_soal_model->save_batch($insert_soal);

									// Mengambil data soal pada test_log 
									$query_test_log = $this->cbt_tes_soal_model->get_by_testuser_select($tests_users_id, $subject_set->tset_topik_id, 'tessoal_id, soal_id, soal_tipe')->result();
									foreach ($query_test_log as $test_log) {
										// Jika tipe soal pilihan ganda
										if($test_log->soal_tipe==1){
											// Jika jawaban diacak 
											if($subject_set->tset_acak_jawaban==1){
												// mendapatkan jawaban dari soal yang ada dengan diacak terlebih dahulu
												$query_jawaban = $this->cbt_jawaban_model->get_by_soal_limit($test_log->soal_id, $subject_set->tset_jawaban);
												// Jika jumlah jawaban lebih dari 0
												if($query_jawaban->num_rows()>0){
													$query_jawaban = $query_jawaban->result();
													$i_jawaban = 0;
													
													 	 $b = chr(65);
													$insert_jawaban = array();
													foreach ($query_jawaban as $jawaban) {
														// Menyimpan data soal
														$data_jawaban['soaljawaban_jawaban_id'] = $jawaban->jawaban_id;
														$data_jawaban['soaljawaban_order'] = ++$i_jawaban;
														$data_jawaban['soaljawaban_selected'] = 0;
														$data_jawaban['soaljawaban_tessoal_id'] = $test_log->tessoal_id;
														                         
                            	
                                                     $data_jawaban['soaljawaban_abjad'] = $b++;
                                                  
                                                
														$insert_jawaban[] = $data_jawaban;

													
												}
													//insert batch
													$this->cbt_tes_soal_jawaban_model->save_batch($insert_jawaban);
												}
											}else{
												// Mendapatkan jawaban yang tidak diacak
												$query_jawaban = $this->cbt_jawaban_model->get_by_soal_tanpa_acak($test_log->soal_id);
												// Jika jumlah jawaban lebih dari 0
												if($query_jawaban->num_rows()>0){
													$query_jawaban = $query_jawaban->result();
													$i_jawaban = 0;
													$insert_jawaban = array();
													foreach ($query_jawaban as $jawaban) {
														// Menyimpan data soal
														$data_jawaban['soaljawaban_jawaban_id'] = $jawaban->jawaban_id;
														$data_jawaban['soaljawaban_order'] = ++$i_jawaban;
														$data_jawaban['soaljawaban_selected'] = 0;
														$data_jawaban['soaljawaban_tessoal_id'] = $test_log->tessoal_id;

														$insert_jawaban[] = $data_jawaban;
													}
													//insert batch
													$this->cbt_tes_soal_jawaban_model->save_batch($insert_jawaban);
												}
											}
										}
									}
								}
							}
							// Menutup transaction mysql
							$this->db->trans_complete();

							$status['status'] = 1;
							$status['tes_id'] = $tes_id;
            				$status['pesan'] = 'Pembuatan tes untuk user berhasil';
						}else{
							$status['status'] = 2;
            				$status['pesan'] = '';
						}
					}else{
						$status['status'] = 0;
            			$status['pesan'] = 'Masukkan Token Ujian !';
					}
				}else{
					$status['status'] = 2;
            		$status['pesan'] = '';
				}
			}else{
				$status['status'] = 2;
            	$status['pesan'] = '';
			}
        }else{
            $status['status'] = 0;
            $status['pesan'] = validation_errors();
        }
        
        echo json_encode($status);
    }


	
	function password(){
        $this->load->library('form_validation');
        
		$this->form_validation->set_rules('password-old', 'Password Lama','required|strip_tags');
		$this->form_validation->set_rules('password-new', 'Password Baru','required|strip_tags');
        $this->form_validation->set_rules('password-confirm', 'Confirm Password','required|strip_tags');
        
        if($this->form_validation->run() == TRUE){
			$old = $this->input->post('password-old', TRUE);
			$new = $this->input->post('password-new', TRUE);
			$confirm = $this->input->post('password-confirm', TRUE);
			
			$username = $this->access_tes->get_username();
			
			if($this->cbt_user_model->count_by_username_password($username, $old)>0){
				if($new==$confirm){
					$data['user_password'] = $new;

					$this->cbt_user_model->update('user_name', $username, $data);
					$status['status'] = 1;
					$status['error'] = '';
				}else{
					$status['status'] = 0;
					$status['error'] = 'Kedua password baru tidak sama';
				}
			}else{
				$status['status'] = 0;
				$status['error'] = 'Password Lama tidak Sesuai';
			}
        }else{
            $status['status'] = 0;
            $status['error'] = validation_errors();
        }
        
        echo json_encode($status);
    }

     function get_by_id($id=null){
    	$data['data'] = 0;
		if(!empty($id)){
			$query = $this->master->getInfobyid($id);
			if($query->num_rows()>0){
				$query = $query->row();
				
				$data['data'] = 1;
				$data['id'] = $query->info_id;
				$data['judul'] = $query->info_judul;
			   $data['isi'] = $query->info_isi;
               $data['kategori'] = $query->info_kategori;
				$data['tgl'] = $query->info_tgl;
				
			}
		}
		echo json_encode($data);
    }
    function get_datatable(){
		// variable initialization
		$search = "";
		$start = 0;
		$rows = 10;

		$group = $this->access_tes->get_group();
		$grup_id = $this->cbt_user_grup_model->get_by_kolom_limit('grup_nama', $group, 1)->row()->grup_id;
		$username = $this->access_tes->get_username();
		$user_id = $this->cbt_user_model->get_by_kolom_limit('user_name', $username, 1)->row()->user_id;

		// get search value (if any)
		if (isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
			$search = $_GET['sSearch'];
		}

		// limit
		$start = $this->get_start();
		$rows = $this->get_rows();

		// run query to get user listing
		$query = $this->cbt_tesgrup_model->get_datatable($start, $rows, $grup_id);
		$iFilteredTotal = $query->num_rows();
		
		$iTotal= $this->cbt_tesgrup_model->get_datatable_count($grup_id)->row()->hasil;
	    
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
    if($query_test_nilai_pg = $this->cbt_tes_user_model->get_by_user_tes_pg($temp->tes_id)->row()->tes_status<1){
		$record[] = '<b>'.$temp->tes_jenis.' '.$temp->modul_nama.' '.$temp->tes_nama.'</b>';
	           

		// Cek apakah sudah mengikuti tes tetapi belum selesai
		if($this->cbt_tes_user_model->count_by_user_tes($user_id, $temp->tes_id)->row()->hasil>0){
			
			// Cek apakah sudah selesai atau belum, jika blum selesai maka tes bisa dilanjutkan
			$tanggal = new DateTime();
			$query_test_user = $this->cbt_tes_user_model->get_by_user_tes($user_id, $temp->tes_id)->row();
			$query_test_nilai_pg = $this->cbt_tes_user_model->get_by_user_tes_pg($temp->tes_id)->row();
			$stat = 0;
			$tanggal_tes = new DateTime($query_test_user->tesuser_creation_time);
			$tanggal_tes->modify('+'.$temp->tes_duration_time.' minutes');
			
			if($tanggal<$tanggal_tes AND $query_test_user->tesuser_status!=4){
				
				// nilai kosong karena masih dalam pengerjaan
				$record[] = '';
				// Jika masih dalam waktu pengerjaan, maka tes dilanjutkan
				$record[] = '<a href="'.site_url().'unitujian/tes_kerjakan/index/'.$temp->tes_id.'" style="cursor: pointer;" class="btn btn-default btn-xs">Lanjutkan</a>';
			}else{
				
				if($temp->tes_results_to_users==1){
					if($this->cbt_tes_soal_model->get_nilai($query_test_user->tesuser_id)->row()->hasil < $query_test_nilai_pg->tes_pg){
						$st = "<span class='notification'>Belum berhasil</span>";
					}else{
						$st = "  <div class='segment__button'>Berhasil</div>";
					}
					$record[] = number_format($this->cbt_tes_soal_model->get_nilai($query_test_user->tesuser_id)->row()->hasil);
				
				}else{
					$record[] = '';
				}
				$record[] = $st;
				// mengecek apakah detail tes ditampilkan
				if($temp->tes_detail_to_users==1){
					$record[] = '<a href="'.site_url().'/tes_hasil_detail/index/'.$query_test_user->tesuser_id.'" style="cursor: pointer;" class="btn btn-default btn-xs">Lihat Detail</a>';
				}else{
					$record[] = '';
				}
			}
		}else{
			
			$record[] = '';
			$record[] = '<a class="btn btn-info btn-xs">Ujian Belum Aktif</a>';
			
		}

		$output['aaData'][] = $record;
			
	}elseif($this->cbt_tes_topik_set_model->count_by_kolom('tset_tes_id', $temp->tes_id)->row()->hasil>0){
			//	$record[] = ++$i;
	            $record[] = '<b>'.$temp->tes_jenis.' '.$temp->modul_nama.' '.$temp->tes_nama.'</b>';
	           

	            
	            if($this->cbt_tes_user_model->count_by_user_tes($user_id, $temp->tes_id)->row()->hasil>0){
					
	            	
	            	$tanggal = new DateTime();
					$query_test_user = $this->cbt_tes_user_model->get_by_user_tes($user_id, $temp->tes_id)->row();
					$query_test_nilai_pg = $this->cbt_tes_user_model->get_by_user_tes_pg($temp->tes_id)->row();
					$stat = 0;
	            	$tanggal_tes = new DateTime($query_test_user->tesuser_creation_time);
	            	$tanggal_tes->modify('+'.$temp->tes_duration_time.' minutes');
					
	            	if($tanggal<$tanggal_tes AND $query_test_user->tesuser_status!=4){
						
	            		// nilai kosong karena masih dalam pengerjaan
	            		$record[] = '';
	            		// Jika masih dalam waktu pengerjaan, maka tes dilanjutkan
	            		$record[] = '<a href="'.site_url().'unitujian/tes_kerjakan/index/'.$temp->tes_id.'" style="cursor: pointer;" class="btn btn-default btn-xs">Lanjutkan</a>';
	            	}else{
	            		
		            	if($temp->tes_results_to_users==1){
							if($this->cbt_tes_soal_model->get_nilai($query_test_user->tesuser_id)->row()->hasil < $query_test_nilai_pg->tes_pg){
								$st = "<i class='fa fa-book' style='font-size:14px'></i> Belum Berhasil
							 ";
							}else{
								$st = "<i class='fa fa-graduation-cap' style='font-size:14px'></i> Berhasil";
							}
		            		$record[] = number_format($this->cbt_tes_soal_model->get_nilai($query_test_user->tesuser_id)->row()->hasil);
						
						}else{
		            		$record[] = '';
		            	}
						$record[] = $st;
	            		// mengecek apakah detail tes ditampilkan
	            		if($temp->tes_detail_to_users==1){
	            			$record[] = '<a href="'.site_url().'/tes_hasil_detail/index/'.$query_test_user->tesuser_id.'" style="cursor: pointer;" class="btn btn-default btn-xs">Lihat Detail</a>';
	            		}else{
	            			$record[] = '';
	            		}
	            	}
	            }else{
					
	            	$record[] = '';
	            	$record[] = '<a href="'.site_url().'unitujian/'.$this->url.'/konfirmasi_test/'.$temp->tes_id.'" style="cursor: pointer;" class="btn btn-success btn-xs">Kerjakan</a>';
					
				}

				$output['aaData'][] = $record;
			}
			else{

			}
		}
		// format it to JSON, this output will be displayed in datatable
        
		echo json_encode($output);
	}

	
	
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