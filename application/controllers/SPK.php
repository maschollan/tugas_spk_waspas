<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SPK extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('WP_model');
    }

    /** List Alternatif */
    // Tampilan Nilai Alternatif
    public function index()
    {
        $data["alternatif"] = $this->WP_model->select_data("tabel_alternatif")->result();
        $data_b["bobot"] = $this->WP_model->select_data("tabel_bobot")->result();
        $this->load->view('v_alternatif', $data + $data_b);
    }

    // Tampilan Tambah Device
    public function tambah_Alternatif()
    {
        $this->load->view('t_alternatif');
    }

    // Data ditambahkan ke Database
    public function tambahAlternatif()
    {
        $nama                   = $this->input->post("nama");
        $c1                     = $this->input->post("c1");
        $c2                     = $this->input->post("c2");
        $c3                     = $this->input->post("c3");

        $data                   = array(
            'nama'              => $nama,
            'c1'                => $c1,
            'c2'                => $c2,
            'c3'                => $c3,
        );

        $hasil = $this->WP_model->simpan_data("tabel_alternatif", $data);
        if ($hasil) {
            echo "<script>alert('Data Berhasil di Tambah');</script>";
            $this->index();
        } else {
            echo "<script>alert('Data gagal di tambah');</script>";
            $this->index();
        }
    }

    // Tampilan Edit Alternatif
    public function edit_alternatif($id_alternatif)
    {
        $data['alternatif'] = $this->WP_model->select_condition('tabel_alternatif', array('id_alternatif' => $id_alternatif))->row();
        $this->load->view('e_alternatif', $data);
    }

    // Data yang di Ubah ke Database
    public function editAlternatif()
    {
        $id_alternatif          = $this->input->post("id_alternatif");
        $nama                   = $this->input->post("nama");
        $c1                     = $this->input->post("c1");
        $c2                     = $this->input->post("c2");
        $c3                     = $this->input->post("c3");

        $data                   = array(
            'nama'              => $nama,
            'c1'                => $c1,
            'c2'                => $c2,
            'c3'                => $c3,
        );

        $hasil = $this->WP_model->update("tabel_alternatif", array('id_alternatif' => $id_alternatif), $data);
        if ($hasil) {
            echo "<script>alert('Data Berhasil di Edit');</script>";
            $this->index();
        } else {
            echo "<script>alert('Data Gagal di Edit');</script>";
            $this->index();
        }
    }

    // Hapus Alternatif
    public function hapusAlternatif($id_alternatif)
    {
        $hasil = $this->WP_model->hapus("tabel_alternatif", array('id_alternatif' => $id_alternatif));
        if ($hasil) {
            echo "<script>alert('Data Berhasil di Hapus');</script>";
            $this->index();
        } else {
            echo "<script>alert('Data Gagal di Hapus');</script>";
            $this->index();
        }
    }

    // Tampilan Edit Bobot
    public function edit_bobot($id_bobot)
    {
        $data_b['bobot'] = $this->WP_model->select_condition('tabel_bobot', array('id_bobot' => $id_bobot))->row();
        $this->load->view('e_bobot', $data_b);
    }

    // Data yang di Ubah ke Database
    public function editBobot()
    {
        $id_bobot               = $this->input->post("id_bobot");
        $w1                     = $this->input->post("w1");
        $w2                     = $this->input->post("w2");
        $w3                     = $this->input->post("w3");


        $data_b                 = array(
            'w1'                => $w1,
            'w2'                => $w2,
            'w3'                => $w3,
        );

        $hasil = $this->WP_model->update("tabel_bobot", array('id_bobot' => $id_bobot), $data_b);
        if ($hasil) {
            echo "<script>alert('Data Berhasil di Edit');</script>";
            $this->index();
        } else {
            echo "<script>alert('Data Gagal di Edit');</script>";
            $this->index();
        }
    }

    // data maksimal
    public function maxdata($data)
    {
        $arr = array();
        foreach ($data as $item) {
            array_push($arr, $item->c1);
            array_push($arr, $item->c2);
            array_push($arr, $item->c3);
        }
        return max($arr);
    }

    // data minimal
    public function mindata($data)
    {
        $arr = array();
        foreach ($data as $item) {
            array_push($arr, $item->c1);
            array_push($arr, $item->c2);
            array_push($arr, $item->c3);
        }
        return min($arr);
    }

    // Tampilan Vektor S
    public function vektor_s()
    {
        $data["alternatif"] = $this->WP_model->select_data("tabel_alternatif")->result();
        $data['max'] = $this->maxdata($data["alternatif"]);
        $data['min'] = $this->mindata($data["alternatif"]);
        $data['normalisasi'] = $this->normalisasi($data["alternatif"]);
        $this->load->view('vektor_s', $data);
    }
    // tahap menghitunh nilai normalisasi
    public function normalisasi($data)
    {
        $normalisasi = array();
        $min = $this->mindata($data);
        $max = $this->maxdata($data);
        foreach ($data as $data_alter) {
            $normal = array();
            array_push($normal, $min / $data_alter->c1);
            array_push($normal, $data_alter->c2 / $max);
            array_push($normal, $data_alter->c3 / $max);
            array_push($normalisasi, $normal);
        }
        return $normalisasi;
    }
    //mengambil nilai qi
    public function qi($data)
    {
        $qi1 = array();
        $normalisasi = $this->normalisasi($data);
        $bobot = $this->WP_model->select_data("tabel_bobot")->row();
        for ($i = 0; $i < count($data); $i++) {
            array_push($qi1, (0.5 * (($normalisasi[$i][0] * $bobot->w1) + ($normalisasi[$i][1] * $bobot->w2) + ($normalisasi[$i][2] * $bobot->w3)))
                + (0.5 * (pow($normalisasi[$i][0], $bobot->w1) * pow($normalisasi[$i][1], $bobot->w2) * pow($normalisasi[$i][2], $bobot->w3))));
        }
        return $qi1;
    }

    // Tampilan Vektor V
    public function vektor_v()
    {
        $data["alternatif"] = $this->WP_model->select_data("tabel_alternatif")->result();
        $data['normalisasi'] = $this->normalisasi($data["alternatif"]);
        $data['qi'] = $this->qi($data["alternatif"]);
        $data_b["bobot"] = $this->WP_model->select_data("tabel_bobot")->row();
        $this->load->view('vektor_v', $data + $data_b);
    }

    // Tampilan Rangking
    public function rangking()
    {
        $data["alternatif"] = $this->WP_model->select_data("tabel_alternatif")->result();
        $data['qi'] = $this->qi($data["alternatif"]);
        $data_b["bobot"] = $this->WP_model->select_data("tabel_bobot")->result();
        $this->load->view('alternatif_rangking', $data + $data_b);
    }
}

// Credit:
// Nama        : Aji Yudha Perwira
// Facebook    : https://web.facebook.com/profile.php?id=100007212107087
// Twitter     : https://twitter.com/ajiyudhaperwira
// linked      : https://www.linkedin.com/in/aji-yudha-perwira-343945119/
// Instagram   : https://www.instagram.com/ajiyudhaperwira/?hl=id