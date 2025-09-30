<?php


namespace Agmedia\Service\Overseas;


use Agmedia\Models\Country;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Overseas
{
    
    /**
     * @param Spreadsheet $sheet
     * @param             $data
     *
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function makeParcelExcel(Spreadsheet $sheet, $data)
    {
        $sheet->setActiveSheetIndex(0);
    
        $sheet->getActiveSheet()->setCellValue('A' . 1, 'Sender Name');
        $sheet->getActiveSheet()->setCellValue('B' . 1, 'Tel.');
        $sheet->getActiveSheet()->setCellValue('C' . 1, 'Mob.');
        $sheet->getActiveSheet()->setCellValue('D' . 1, 'email');
    
        $sheet->getActiveSheet()->setCellValue('A' . 2, 'BALI D.O.O.');
        $sheet->getActiveSheet()->setCellValue('B' . 2, '38540688511');
        $sheet->getActiveSheet()->setCellValue('C' . 2, '38598312143');
        $sheet->getActiveSheet()->setCellValue('D' . 2, 'infobalidoo@gmail.com');
        
        /*$i = 1;
        foreach ($data as $item) {
            $i++;
            $sheet->getActiveSheet()->setCellValue('A' . $i, 'BALI D.O.O.');
            $sheet->getActiveSheet()->setCellValue('B' . $i, '38540688511');
            $sheet->getActiveSheet()->setCellValue('C' . $i, '38598312143');
            $sheet->getActiveSheet()->setCellValue('D' . $i, 'infobalidoo@gmail.com');
        }*/
        
        $sheet->getActiveSheet()->setTitle('Sender info');
        
        $sheet->createSheet();
        $sheet->setActiveSheetIndex(1);
    
        $sheet->getActiveSheet()->setCellValue('A' . 1, 'Name');
        $sheet->getActiveSheet()->setCellValue('B' . 1, 'Address');
        $sheet->getActiveSheet()->setCellValue('C' . 1, 'City');
        $sheet->getActiveSheet()->setCellValue('D' . 1, 'Postcode');
        $sheet->getActiveSheet()->setCellValue('E' . 1, 'Country');
        $sheet->getActiveSheet()->setCellValue('F' . 1, 'Email');
        $sheet->getActiveSheet()->setCellValue('G' . 1, '');
        $sheet->getActiveSheet()->setCellValue('H' . 1, 'Reference');
        $sheet->getActiveSheet()->setCellValue('I' . 1, 'Collies');
        $sheet->getActiveSheet()->setCellValue('J' . 1, '');
        $sheet->getActiveSheet()->setCellValue('K' . 1, '');
        $sheet->getActiveSheet()->setCellValue('L' . 1, 'Express');
        $sheet->getActiveSheet()->setCellValue('M' . 1, '');
        $sheet->getActiveSheet()->setCellValue('N' . 1, '');
        $sheet->getActiveSheet()->setCellValue('O' . 1, 'Comment');
        $sheet->getActiveSheet()->setCellValue('P' . 1, '');
        $sheet->getActiveSheet()->setCellValue('Q' . 1, '');
    
        $i = 1;
        foreach ($data as $item) {
            $country = Country::where('country_id', $item['payment_country_id'])->first();
            
            $i++;
            $sheet->getActiveSheet()->setCellValue('A' . $i,
                $item['payment_firstname'] . ' ' . $item['payment_lastname']);
            $sheet->getActiveSheet()->setCellValue('B' . $i, $item['payment_address_1']);
            $sheet->getActiveSheet()->setCellValue('C' . $i, $item['payment_city']);
            $sheet->getActiveSheet()->setCellValue('D' . $i, $item['payment_postcode']);
            $sheet->getActiveSheet()->setCellValue('E' . $i, $country['iso_code_2']);
            $sheet->getActiveSheet()->setCellValue('F' . $i, $item['email']);
            $sheet->getActiveSheet()->setCellValue('G' . $i, '');
            $sheet->getActiveSheet()->setCellValue('H' . $i, $item['order_id']);
            $sheet->getActiveSheet()->setCellValue('I' . $i, 1);
            $sheet->getActiveSheet()->setCellValue('J' . $i, '');
            $sheet->getActiveSheet()->setCellValue('K' . $i, '');
            $sheet->getActiveSheet()->setCellValue('L' . $i, 0);
            $sheet->getActiveSheet()->setCellValue('M' . $i, '');
            $sheet->getActiveSheet()->setCellValue('N' . $i, '');
            $sheet->getActiveSheet()->setCellValue('O' . $i, $country['name']);
            $sheet->getActiveSheet()->setCellValue('P' . $i, '');
            $sheet->getActiveSheet()->setCellValue('Q' . $i, '');
        }
        
        $sheet->getActiveSheet()->setTitle('Shipments');
        
        return $sheet;
    }
}