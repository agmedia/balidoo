<?php


namespace Agmedia\Helpers\File;


use Agmedia\Helpers\Log;
use Agmedia\Service\Intereuropa\Intereuropa;
use Agmedia\Service\Overseas\Overseas;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Excel
{
    
    /**
     * @var array
     */
    protected $data;
    
    /**
     * @var Spreadsheet
     */
    private $sheet;
    
    /**
     * @var string
     */
    private $type;
    
    
    /**
     * Excel constructor.
     *
     * @param array $data
     */
    public function __construct($data = [], $type = 'overseas')
    {
        $this->data  = $data;
        $this->sheet = new Spreadsheet();
        $this->type = $type;
        
        //Log::write($data, 'excel_data');
        
        $this->sheet->getProperties()->setCreator('Balidoo')->setTitle($this->type);
    }
    
    
    /**
     * @return bool
     */
    public function isValidData()
    {
        $valid = true;
        
        foreach ($this->data as $i => $item) {
            if (empty($item['payment_lastname']) ||
                empty($item['payment_postcode']) ||
                empty($item['payment_country_id'])) {
                
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    
    /**
     * @param $type
     *
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function make($type)
    {
        if ($type == 'overseas') {
            $this->sheet = Overseas::makeParcelExcel($this->sheet, $this->data);
        }
        
        if ($type == 'intereuropa') {
            $this->sheet = Intereuropa::makeParcelExcel($this->sheet, $this->data);
        }
        
        return $this;
    }
    
    
    /**
     * @param      $type
     * @param null $name
     *
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function response($type, $name = null)
    {
        if ( ! $name) {
            $name = $this->type . '_labels_' . time();
        }
        
        if ($type == 'stream') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . basename($name) . '.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
        }
        
        $writer = IOFactory::createWriter($this->sheet, 'Xlsx');
        
        return $writer->save('php://output');
    }
    
}