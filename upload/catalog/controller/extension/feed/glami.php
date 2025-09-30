<?php

class ControllerExtensionFeedGlami extends Controller {
    
    public function index()
    {
        
        $output = '<?xml version="1.0" encoding="UTF-8"?>';
       
        $output .= '<SHOP>';


                
                $this->load->model('catalog/product');
                $this->load->model('catalog/category');
        
       $products = array_merge(
        
          
                   $this->model_catalog_product->getProducts(array('filter_manufacturer_id' => 11)) //kozo

              
    
        );

        //$products = $this->model_catalog_product->getProducts();
        
        
        foreach ($products as $product) {


$categories = $this->model_catalog_product->getCategories($product['product_id']);


foreach ($categories as $subarray)
   
 
{
    /*echo '<pre>';
                print_r($subarray['category_id']);                        
                echo '</pre>';*/

              $category = '';

                if($subarray['category_id']=='108' || $subarray['category_id']=='100' || $subarray['category_id']=='99'){

                    $category = 'Glami.hr | Muška odjeća i obuća | Muška odjeća | Muška odjeća za spavanje | Muške pidžame';


                }

                else{

                    $category = 'Glami.hr | Muška odjeća i obuća | Muška odjeća | Muško donje rublje';

                }
}





                 $sku = $this->getOption($product);
                
               

                 if (is_array($sku)) {
                    foreach ($sku as $item) {
                       // if ( ! empty($item['variation_sku'])) {




                            $output .= '<SHOPITEM>';

                                 $output .= '<ITEM_ID>' .$item['variation_sku'] .'</ITEM_ID>';
                                 $output .= '<ITEMGROUP_ID>' . $product['model'] . '</ITEMGROUP_ID>';
                                 $output .= '<PRODUCTNAME>' . $this->wrapInCDATA($product['name']) . '</PRODUCTNAME>';
                                 $output .= '<DESCRIPTION>' . $this->wrapInCDATA($product['meta_description']) . '</DESCRIPTION>';
                                 $output .= '<URL>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</URL>';
                                 $output .= '<IMAGEURL>' . $this->wrapInCDATA('https://www.kozo-underwear.hr/image/' . $product['image']) . '</IMAGEURL>'; 



                                $additional_images = $this->model_catalog_product->getProductImages($product['product_id']);

                          

                        

                                     if($additional_images) {

                                           foreach ($additional_images as $additional_image) {

                                                  $output .= '<IMGURL_ALTERNATIVE>'. $this->model_tool_image->resize($additional_image['image'], 500, 500) .'</IMGURL_ALTERNATIVE>';

                                             }

                                     }


                                     if($product['special']!=''){

                                         $output .= '<PRICE_VAT>' . $product['special'] . '</PRICE_VAT>';

                                    }
                                    else{

                                           $output .= '<PRICE_VAT>' . $product['price'] . '</PRICE_VAT>';  


                                    }




                              
                                 $output .= '<CATEGORYTEXT>' . $category . '</CATEGORYTEXT>';  
                                 $output .= '<DELIVERY_DATE>0</DELIVERY_DATE>';  


                                 $output .= '<PARAM>'; 
                                 $output .= '<PARAM_NAME>veličine</PARAM_NAME>'; 
                                 $output .= '<VAL>'.$item['variation_key'].'</VAL>'; 
                                 $output .= '</PARAM> '; 

                                 $output .= '<PARAM>'; 
                                 $output .= '<PARAM_NAME>size_system</PARAM_NAME>'; 
                                 $output .= '<VAL>INT</VAL>'; 
                                 $output .= '</PARAM>'; 
                          
                            $output .= '</SHOPITEM>';
                         
                        //}
                    }
                }
                    
    

         





        }
        $output .= '</SHOP>';
 
        
        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($output);
        
        
    }
    
    
    private function wrapInCDATA($in)
    {
        return "<![CDATA[ " . $in . " ]]>";
        //return $in;
    }
    
    
    private function removeChar($string, $char)
    {
        return str_replace($char, '', $string);
    }


    protected function getPath($parent_id, $current_path = '') {
        $category_info = $this->model_catalog_category->getCategory($parent_id);

        if ($category_info) {
            if (!$current_path) {
                $new_path = $category_info['category_id'];
            } else {
                $new_path = $category_info['category_id'] . '_' . $current_path;
            }

            $path = $this->getPath($category_info['parent_id'], $new_path);

            if ($path) {
                return $path;
            } else {
                return $new_path;
            }
        }
    }   
    
    
    /**
     * Construct category and parent name
     * and return it
     *
     * @param $id
     *
     * @return string
     */
    public function getCategoriesName($id)
    {
        $this->load->model('catalog/category');
        $data = $this->model_catalog_product->getCategories($id);
        $name = '';
        
        foreach ($data as $item) {
            if (empty($category)) {
                $category = $this->model_catalog_category->getCategory($item['category_id']);
                $name     = $category['name'];
                
                if ($category['parent_id'] != 0) {
                    $parent = $this->model_catalog_category->getCategory($category['parent_id']);
                    $name   = $parent['name'] . ' > ' . $category['name'];
                }
            }
        }
        
        return $name;
    }


      public function getOption($product)
    {
       
            $data = $this->model_catalog_product->getProductOptions($product['product_id']);
            $response = array();

            foreach ($data[0]['product_option_value'] as $option) {
                $response[] = array(
                    'variation_name' => $data[0]['name'],
                    'variation_key' => $option['name'],
                    'variation_sku' => $option['sku']
                );
            }

            return $response;
        

        //return $product['sku'];
    }
    
}

?>