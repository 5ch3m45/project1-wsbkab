<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Arsip extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model([
			'arsip_model',
			'klasifikasi_model',
			'arsip_viewers_model',
			'lampiran_model'
		]);
		
		$this->load->helper('view');
	}

	function _lampiranParser($lampiran) {
		if(in_array($lampiran['type'], ['image/jpeg', 'image/png'])) {
			return '<img 
				data-id="'.$lampiran['id'].'" 
				data-url="'.$lampiran['url'].'" 
				class="lampiran lampiran-'.$lampiran['id'].' my-masonry-grid-item p-1" 
				style="max-width: 100%;" 
				src="'.$lampiran['url'].'">';
		} else if(in_array($lampiran['type'], ['video/mp4'])) {
			return '<img 
				data-id="'.$lampiran['id'].'" 
				data-url="'.$lampiran['url'].'" 
				class="lampiran lampiran-'.$lampiran['id'].' my-masonry-grid-item p-1" 
				style="max-width: 100%;" 
				src="'.base_url('assets/images/mp4.png').'">';
		} else if(in_array($lampiran['type'], ['application/pdf'])) {
			return '<img 
				data-id="'.$lampiran['id'].'" 
				data-url="'.$lampiran['url'].'" 
				class="lampiran lampiran-'.$lampiran['id'].' my-masonry-grid-item p-1" 
				style="max-width: 100%;" 
				src="'.base_url('assets/images/pdf.png').'">';
		}
	}
	function _lampiranParserUrl($lampiran) {
		if(in_array($lampiran['type'], ['image/jpeg', 'image/png'])) {
			return $lampiran['url'];
		} else if(in_array($lampiran['type'], ['video/mp4'])) {
			return base_url('assets/images/mp4.png');
		} else if(in_array($lampiran['type'], ['application/pdf'])) {
			return base_url('assets/images/pdf.png');
		}
	}

	public function index() {
		$this->load->view('public/arsip/index');
	}

	public function show($id) {
		// format id jadi integer
		$id = intval($id);
		// jika bukan integer, 404
		if(!$id) {
			show_404();
		}
		
		// cek apa arsip ada
		$arsip = $this->db->select('id, klasifikasi_id, tanggal, nomor, informasi, pencipta, viewers, last_viewer_update')
			->from('tbl_arsip')
			->where('id', $id)
			->limit(1)
			->get()
			->row_array();

		// jika tidak ada, 404
		if(!$arsip) {
			show_404();
		}

		// update jumlah viewer setiap 5 detik
		if(time() - $arsip['last_viewer_update'] > 5) {
// 			$this->db->where('id', $arsip['id'])
// 				->update('tbl_arsip', [
// 					'viewers' => $arsip['viewers']+1,
// 					'last_viewer_update' => time()
// 				]);
            // add total viewers
            $this->arsip_model->addTotalViewers($arsip['id'], $arsip['viewers']+1);
            // add arsip historical viewers
            $this->arsip_viewers_model->addViewer($arsip['id'], date('Y-m-d'));
		}

		// cek apa ada klasifikasi
		if($arsip['klasifikasi_id']) {
			// set data klasifikasi
			$kode_klasifikasi = $this->klasifikasi_model->getOneByID($arsip['klasifikasi_id']);
			if($kode_klasifikasi) {
				$arsip['kode_klasifikasi'] = $kode_klasifikasi;
			}
		}

		// cek apa ada tanggal
		$arsip['tanggal'] = date('d M Y', strtotime($arsip['tanggal']));

		// dapatkan lampiran
		$lampirans = $this->lampiran_model->getAllByArsipID($arsip['id']);
		$arsip['lampirans'] = [];
		foreach ($lampirans as $lampiran) {
			$lampiran['url_parsed'] = $this->_lampiranParserUrl($lampiran);
			array_push($arsip['lampirans'], $lampiran);
		}

		$this->data['arsip'] = $arsip;
		// var_dump($this->data); die();
		$this->load->view('public/arsip/detail', $this->data);
	}

	public function API_index() {
        $page = $this->input->get('p', true);
        $search = $this->input->get('q', true);
        $sort = $this->input->get('s', true);
		$start = $this->input->get('date_start', true);
		$end = $this->input->get('date_end', true);

        // validasi page start
        $page = preg_replace('/[^0-9]/i', '', $page);
        if(!$page) {
            $page = 1;
        }
        // validasi page end

        // validasi search startn
        $search = preg_replace('/[^a-zA-Z\d\s:]/i', '', $search);
        // validasi search end

        // validasi sort start
        $sort = in_array($sort, ['terbaru', 'terlama']) ? $sort : '';
        // validasi sort end
		
		$date = [
			'start' => $start,
			'end'	=> $end
		];

        // generate arsips
        $arsips = $this->arsip_model->getArsipPublic($page, $search, $date, $sort);
		$count_arsips = $this->arsip_model->countArsipPublic($search, $date);
        foreach ($arsips as $key => $value) {
            // add klasifikasi detail
            $arsips[$key]['klasifikasi'] = $this->db->select('kode, nama')
                ->from('tbl_klasifikasi')
                ->where('id', $value['klasifikasi_id'])
                ->get()
                ->row_array();
            // add lampiran detail
			$lampiran_count = $this->db->select('id')
                ->from('tbl_lampiran')
                ->where('arsip_id', $value['id'])
                ->where('is_deleted', 0)
                ->count_all_results();
            $arsips[$key]['lampirans'] = $this->db->select('type, url')
                ->from('tbl_lampiran')
                ->where('arsip_id', $value['id'])
                ->where('is_deleted', 0)
				->limit(2)
                ->get()
                ->result_array();
			if($lampiran_count > 2) {
                $lampiran_rest = $lampiran_count - count($arsips[$key]['lampirans']);
                array_push($arsips[$key]['lampirans'], [
                    'type' => 'rest',
                    'url' => $lampiran_rest
                ]);
            }
            // format tahun
            $arsips[$key]['tahun'] = date('d M Y', strtotime($value['tanggal']));
        }

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' 		=> true,
                'data' 			=> $arsips,
				'current_page'	=> (int)$page,
				'total_page'	=> ceil($count_arsips/PERPAGE)
            ], JSON_PRETTY_PRINT));
    }
}
