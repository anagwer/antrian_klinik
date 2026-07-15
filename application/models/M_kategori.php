<?php
class M_kategori extends CI_Model {
	protected $_table = 'kategori';

	public function lihat(){
		return $this->db->get($this->_table)->result();
	}

	public function lihat_id($id){
		return $this->db->get_where($this->_table, ['id' => $id])->row();
	}

	public function tambah($data){
		return $this->db->insert($this->_table, $data);
	}

	public function ubah($data, $id){
		$this->db->where('id', $id);
		return $this->db->update($this->_table, $data);
	}

	public function hapus($id){
		return $this->db->delete($this->_table, ['id' => $id]);
	}
}
