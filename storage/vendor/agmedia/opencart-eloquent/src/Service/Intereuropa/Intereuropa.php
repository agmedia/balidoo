<?php


namespace Agmedia\Service\Intereuropa;


use Agmedia\Helpers\Log;
use Agmedia\Models\Country;
use Agmedia\Models\Order\OrderProduct;
use Agmedia\Models\Order\OrderTotal;
use Agmedia\Models\Product\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Intereuropa
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

        $sheet->getActiveSheet()->setCellValue('A' . 1, 'VANJSKI REF. BR.');
        $sheet->getActiveSheet()->setCellValue('B' . 1, 'PARITET');
        $sheet->getActiveSheet()->setCellValue('C' . 1, 'PRIMATELJ_NAZIV**');
        $sheet->getActiveSheet()->setCellValue('D' . 1, 'PRIMATELJ_ADRESA**');
        $sheet->getActiveSheet()->setCellValue('E' . 1, 'PRIMATELJ_DRŽAVA**');
        $sheet->getActiveSheet()->setCellValue('F' . 1, 'PRIMATELJ_POŠTA**');
        $sheet->getActiveSheet()->setCellValue('G' . 1, 'PRIMATELJ_MJESTO**');
        $sheet->getActiveSheet()->setCellValue('H' . 1, 'KONTAKT_OSOBA');
        $sheet->getActiveSheet()->setCellValue('I' . 1, 'KONTAKT_TELEFON');
        $sheet->getActiveSheet()->setCellValue('J' . 1, 'KONTAKT_EMAIL');
        $sheet->getActiveSheet()->setCellValue('K' . 1, 'BROJ POVRATNOG DOKUMENTA');
        $sheet->getActiveSheet()->setCellValue('L' . 1, 'KOLIČINA_PAKETA**');
        $sheet->getActiveSheet()->setCellValue('M' . 1, 'VRSTA_PAKETA**');
        $sheet->getActiveSheet()->setCellValue('N' . 1, 'KOLIČINA_POVR_AMB.');
        $sheet->getActiveSheet()->setCellValue('O' . 1, 'BRUTO TEŽINA (KG)**');
        $sheet->getActiveSheet()->setCellValue('P' . 1, 'DUŽINA_PAKETA (CM)');
        $sheet->getActiveSheet()->setCellValue('Q' . 1, 'ŠIRINA_PAKETA (CM)');
        $sheet->getActiveSheet()->setCellValue('R' . 1, 'VISINA_PAKETA (CM)');
        $sheet->getActiveSheet()->setCellValue('S' . 1, 'VOLUMEN (M3)');
        $sheet->getActiveSheet()->setCellValue('T' . 1, 'VRIJEDNOST OTKUPNINE');
        $sheet->getActiveSheet()->setCellValue('U' . 1, 'VRIJEDNOST ROBE');
        $sheet->getActiveSheet()->setCellValue('V' . 1, 'VALUTA');
        $sheet->getActiveSheet()->setCellValue('W' . 1, 'OPIS ROBE');
        $sheet->getActiveSheet()->setCellValue('X' . 1, 'UPUTE / NAPOMENE');
        $sheet->getActiveSheet()->setCellValue('Y' . 1, 'VRIJEME_DOSTAVE (OD)');
        $sheet->getActiveSheet()->setCellValue('Z' . 1, 'VRIJEME_DOSTAVE (DO)');


        $sheet->getActiveSheet()->setCellValue('A' . 2, 'EXT_ORD_ID');
        $sheet->getActiveSheet()->setCellValue('B' . 2, 'INC_ID');
        $sheet->getActiveSheet()->setCellValue('C' . 2, 'CGN_NAME');
        $sheet->getActiveSheet()->setCellValue('D' . 2, 'CGN_ADDRESS');
        $sheet->getActiveSheet()->setCellValue('E' . 2, 'CGN_COUNTRY');
        $sheet->getActiveSheet()->setCellValue('F' . 2, 'CGN_ZIP');
        $sheet->getActiveSheet()->setCellValue('G' . 2, 'CGN_CITY');
        $sheet->getActiveSheet()->setCellValue('H' . 2, 'ARR_CONTACT_NAME');
        $sheet->getActiveSheet()->setCellValue('I' . 2, 'ARR_CONTACT_PHONE_NO');
        $sheet->getActiveSheet()->setCellValue('J' . 2, 'ARR_CONTACT_EMAIL');
        $sheet->getActiveSheet()->setCellValue('K' . 2, 'DOC_NO');
        $sheet->getActiveSheet()->setCellValue('L' . 2, 'HU_QTY');
        $sheet->getActiveSheet()->setCellValue('M' . 2, 'HU_TYPE');
        $sheet->getActiveSheet()->setCellValue('N' . 2, 'HU_RETURNABLE_QTY ');
        $sheet->getActiveSheet()->setCellValue('O' . 2, 'GRS_WGT');
        $sheet->getActiveSheet()->setCellValue('P' . 2, 'MVT_DIM_LENGTH');
        $sheet->getActiveSheet()->setCellValue('Q' . 2, 'MVT_DIM_WIDTH');
        $sheet->getActiveSheet()->setCellValue('R' . 2, 'MVT_DIM_HEIGHT');
        $sheet->getActiveSheet()->setCellValue('S' . 2, 'VOL');
        $sheet->getActiveSheet()->setCellValue('T' . 2, 'COD_GOODS_VALUE');
        $sheet->getActiveSheet()->setCellValue('U' . 2, 'GDS_VAL');
        $sheet->getActiveSheet()->setCellValue('V' . 2, 'GDS_VAL_CUR');
        $sheet->getActiveSheet()->setCellValue('W' . 2, 'GDS_DESCRIPTION');
        $sheet->getActiveSheet()->setCellValue('X' . 2, 'OPERATIONAL_INSTRUCTIONS');
        $sheet->getActiveSheet()->setCellValue('Y' . 2, 'UNLOAD_EST_TIME_START');
        $sheet->getActiveSheet()->setCellValue('Z' . 2, 'UNLOAD_EST_TIME_END');

        $i = 2;
        foreach ($data as $item) {
            $country = Country::where('country_id', $item['shipping_country_id'])->first();
            $name = $item['shipping_firstname'] . ' ' . $item['shipping_lastname'];

            $products = OrderProduct::where('order_id', $item['order_id'])->get();
            $weight = 0.00;

            foreach ($products as $product) {
                $i_weight = Product::where('product_id', $product['product_id'])->pluck('weight');
                $weight = $weight + $i_weight[0];
            }

            $shipping_cost = null;
            
            if ($item['payment_code'] == 'cod') {
                $shipping_cost = OrderTotal::where('order_id', $item['order_id'])->where('code', 'shipping')->first();
            }

            $i++;

            $sheet->getActiveSheet()->setCellValue('A' . $i, $item['order_id']);
            $sheet->getActiveSheet()->setCellValue('B' . $i, 'POŠ');
            $sheet->getActiveSheet()->setCellValue('C' . $i, $name);
            $sheet->getActiveSheet()->setCellValue('D' . $i, $item['shipping_address_1']);
            $sheet->getActiveSheet()->setCellValue('E' . $i, $country['iso_code_2']);
            $sheet->getActiveSheet()->setCellValue('F' . $i, $item['shipping_postcode']);
            $sheet->getActiveSheet()->setCellValue('G' . $i, $item['shipping_city']);
            $sheet->getActiveSheet()->setCellValue('H' . $i, $name);
            $sheet->getActiveSheet()->setCellValue('I' . $i, $item['telephone']);
            $sheet->getActiveSheet()->setCellValue('J' . $i, $item['email']);
            $sheet->getActiveSheet()->setCellValue('K' . $i, '');
            $sheet->getActiveSheet()->setCellValue('L' . $i, 1);
            $sheet->getActiveSheet()->setCellValue('M' . $i, 'PC');
            $sheet->getActiveSheet()->setCellValue('N' . $i, '');
            $sheet->getActiveSheet()->setCellValue('O' . $i, $weight);
            $sheet->getActiveSheet()->setCellValue('P' . $i, '');
            $sheet->getActiveSheet()->setCellValue('Q' . $i, '');
            $sheet->getActiveSheet()->setCellValue('R' . $i, '');
            $sheet->getActiveSheet()->setCellValue('S' . $i, '');
            $sheet->getActiveSheet()->setCellValue('T' . $i, $shipping_cost ? number_format($item['total'], 2, ',', '.') : '');
            $sheet->getActiveSheet()->setCellValue('U' . $i, $shipping_cost ? number_format($item['total'], 2, ',', '.') : '');
            $sheet->getActiveSheet()->setCellValue('V' . $i, agconf('intereuropa_currency'));
            $sheet->getActiveSheet()->setCellValue('W' . $i, '');
            $sheet->getActiveSheet()->setCellValue('X' . $i, '');
            $sheet->getActiveSheet()->setCellValue('Y' . $i, '');
            $sheet->getActiveSheet()->setCellValue('Z' . $i, '');
        }

        $sheet->getActiveSheet()->setTitle('Shipments');

        return $sheet;
    }
}
