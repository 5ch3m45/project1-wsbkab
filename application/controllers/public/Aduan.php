<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Gregwar\Captcha\CaptchaBuilder;
class Aduan extends CI_Controller {
	public function _construct() {
		parent::__construct();
		
		$this->load->library('form_validation');
        $this->load->helper('formatter_helper');
	}
	public function show() {
		$captcha = new CaptchaBuilder();
		
		$_SESSION['phrase'] = $captcha->getPhrase();
		$data['captcha'] = $captcha->build()->inline();
		$this->load->view('aduan_detail', $data);
	}

	// ===== API START =====
	function API_store() {
        // Checking that the posted phrase match the phrase stored in the session
        $input = $this->input->post(null, true);

        $captcha_is_valid = false;
        if (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $input['phrase'])) {
            $captcha_is_valid = true;
        }
        // generate captcha baru
        unset($_SESSION['phrase']);
        $captcha = new CaptchaBuilder();
		
		$_SESSION['phrase'] = $captcha->getPhrase();
		$captcha_baru = $captcha->build()->inline();

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email', [
            'required' => 'Email tidak boleh kosong',
            'valid_email' => 'Email tidak valid',
        ]);
        $this->form_validation->set_rules('nama', 'Nama', 'required|min_length[3]', [
            'required' => 'Nama tidak boleh kosong',
            'min_length' => 'Nama tidak valid',
        ]);

        $this->form_validation->set_rules('aduan', 'Aduan', 'required|min_length[100]', [
            'required' => 'Aduan tidak boleh kosong',
            'min_length' => 'Aduan minimal 100 karakter',
        ]);

        if ($this->form_validation->run() == FALSE  || !$captcha_is_valid) {
            $error = $this->form_validation->error_array();
            if(!$captcha_is_valid) {
                $error['captcha'] = 'Captcha tidak valid';
            }
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => $error,
                    'captcha' => $captcha_baru,
                    'csrf' => $this->security->get_csrf_hash()
                ]));
        }

        $data = [
            'kode' => random_int(1111111111111, 9999999999999),
            'email' => $input['email'],
            'nama' => $input['nama'],
            'aduan' => $input['aduan'],
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];

        $aduanID = $this->aduan_model->create($data);
        $this->aduan_timeline_model->create([
            'aduan_id' => $aduanID,
            'status' => 1,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ]);

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => true,
                'data' => $data,
                'captcha' => $captcha_baru,
                'csrf' => $this->security->get_csrf_hash()
            ]));
    }

	function API_find() {
        // Checking that the posted phrase match the phrase stored in the session
        $input = $this->input->post(null, true);

        $captcha_is_valid = false;
        if (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $input['phrase'])) {
            $captcha_is_valid = true;
        }
        // generate captcha baru
        unset($_SESSION['phrase']);
        $captcha = new CaptchaBuilder();
		
		$_SESSION['phrase'] = $captcha->getPhrase();
		$captcha_baru = $captcha->build()->inline();

        if (!$captcha_is_valid) {
            $error['captcha'] = 'Captcha tidak valid';
            
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => $error,
                    'captcha' => $captcha_baru,
                    'csrf' => $this->security->get_csrf_hash()
                ]));
        }

        $aduan = $this->aduan_model->getOneByKode($input['kode']);
        if(!$aduan) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Aduan tidak ditemukan',
                    'captcha' => $captcha_baru,
                    'csrf' => $this->security->get_csrf_hash()
                ]));
        }
        $aduan['email'] = sensorEmail($aduan['email']);
        $aduan['nama'] = sensorNama($aduan['nama']);

        $aduanStatus = $this->aduan_timeline_model->getAllByAduanID($aduan['id']);
        foreach ($aduanStatus as $key => $status) {
            $aduanStatus[$key]['created_at_formatted'] = date('d M Y', strtotime($status['created_at']));
        }
        $aduan['status'] = $aduanStatus;

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => true,
                'data' => $aduan,
                'captcha' => $captcha_baru,
                'csrf' => $this->security->get_csrf_hash()
            ]));
    }
}
