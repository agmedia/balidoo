<?php
/**
 * User: fj.agmedia.hr
 * Date: 16/06/2017
 * Time: 14:36
 */

namespace Agmedia\Helpers;


class Log
{
    
    /**
     * @param        $message
     * @param string $filename
     */
    public static function write($message, $filename = 'error')
    {
        $handle = fopen(DIR_LOGS . $filename . '.log', 'a');
        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        fclose($handle);
    }
    
    
    /**
     * Logs the data with month folders and
     * day concat to file name.
     *
     * @param        $message
     * @param string $filename
     */
    public static function info($message, $filename = 'agmedia')
    {
        $month = date('m');
        $day   = date('d');
        
        $path = DIR_LOGS . 'info/';
        
        if ( ! is_dir($path . $month)) {
            mkdir($path . $month, 0755, true);
        }
        
        $handle = fopen($path . $month . '/' . $filename . '_' . $day . '.log', 'a');
        
        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        fclose($handle);
    }
    
    
    /**
     * Log messages.
     *
     * @param        $message
     * @param string $filename
     */
    public static function store($message, $filename)
    {
        $year  = date('Y');
        $month = date('m');
        $day   = date('d');
        
        $path = DIR_LOGS/* . 'store/'*/;
        
        if ( ! is_dir($path . $year)) {
            mkdir($path . $year, 0755, true);
            
            if ( ! is_dir($path . $year . '/' . $month)) {
                mkdir($path . $year . '/' . $month, 0755, true);
                
                if ( ! is_dir($path . $year . '/' . $month . '/' . $day)) {
                    mkdir($path . $year . '/' . $month . '/' . $day, 0755, true);
                }
            }
        }
        
        $handle = fopen($path . $year . '/' . $month . '/' . $day . '/' . $filename . '.log', 'a');
        
        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        fclose($handle);
    }
    
    
    private function timer()
    {
        /*Log::write('.........', 'timer');
        Log::write('Total proces time : ' . ($updated2_time - $start_time), 'timer');
        Log::write('Loaded from Kipos in : ' . ($loaded_time - $start_time), 'timer');
        Log::write('Set in PHP for update in : ' . ($set_time - $loaded_time), 'timer');
        Log::write('Inserted to temp_DB in : ' . ($inserted_time - $set_time), 'timer');
        Log::write('Updated products to DB in : ' . ($updated_time - $inserted_time), 'timer');
        Log::write('Updated options to DB in : ' . ($updated2_time - $updated_time), 'timer');*/
    }
}