<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pdf extends TCPDF
{
    /**
     * Stores font list
     * @var array
     */
    public $_fonts_list = array();
    /**
     * This is true when last page is rendered
     * @var boolean
     */
    protected $last_page_flag = false;
    /**
     * PDF Type
     * invoice,estimate,proposal,contract
     * @var string
     */
    private $pdf_type = '';

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $pdf_type = '')
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->pdf_type = $pdf_type;
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';

        $this->setLanguageArray($lg);
        $this->_fonts_list = $this->fontlist;

        do_action('pdf_construct', array('pdf_instance'=>$this, 'type'=>$this->pdf_type));
    }

    public function Close()
    {
        if (($this->pdf_type == 'invoice' &&  get_option('show_pdf_signature_invoice') == 1)
            || ($this->pdf_type == 'estimate' && get_option('show_pdf_signature_estimate') == 1)
            || ($this->pdf_type == 'credit_note') && get_option('show_pdf_signature_credit_note') == 1) {
            $signatureImage = get_option('signature_image');
            $signaturePath = FCPATH.'uploads/company/'.$signatureImage;
            $signatureExists = file_exists($signaturePath);

            $blankSignatureLine = do_action('blank_signature_line', '_________________________');

            if ($signatureImage != '' && $signatureExists) {
                $blankSignatureLine = '';
            }

            $this->ln(15);
            $this->Cell(0, 0, _l('authorized_signature_text') . ' ' . $blankSignatureLine, 0, 1, 'L', 0, '', 0);

            if ($signatureImage != '' && $signatureExists) {
                $this->ln(2);
                $this->Image($signaturePath, '', '', 0, 0, '', '', 'L');
            }
        }

        do_action('pdf_close', array('pdf_instance'=>$this, 'type'=>$this->pdf_type));

        $this->last_page_flag = true;
        parent::Close();
    }

    public function Header()
    {
        do_action('pdf_header', array('pdf_instance'=>$this, 'type'=>$this->pdf_type));
    }

    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        $font_name = get_option('pdf_font');
        $font_size = get_option('pdf_font_size');

        if ($font_size == '') {
            $font_size = 10;
        }

        $this->SetFont($font_name, '', $font_size);

        do_action('pdf_footer', array('pdf_instance'=>$this, 'type'=>$this->pdf_type));

        if (get_option('show_page_number_on_pdf') == 1) {
            $this->SetFont($font_name, 'I', 8);
            $this->SetTextColor(142, 142, 142);
            $this->Cell(0, 15, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    public function get_fonts_list()
    {
        return $this->_fonts_list;
    }
}

/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */
