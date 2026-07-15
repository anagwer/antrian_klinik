<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index() {
        $html_file = FCPATH . 'index.html';
        if (file_exists($html_file)) {
            echo file_get_contents($html_file);
        } else {
            echo "React build frontend not found at root directory.";
        }
    }
}
