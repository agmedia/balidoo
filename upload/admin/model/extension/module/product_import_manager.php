<?php
class ModelExtensionModuleProductImportManager extends Model
{
    public function replaceProductOptions(int $product_id, array $ag_options): array
    {
        // 1) Pobriši postojeće vrijednosti (product_option_value) pa onda product_option
        $this->db->query("DELETE pov FROM " . DB_PREFIX . "product_option_value pov 
                          JOIN " . DB_PREFIX . "product_option po ON po.product_option_id = pov.product_option_id
                          WHERE po.product_id = " . (int)$product_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = " . (int)$product_id);

        // 2) Ubaci nove
        $inserted_po  = 0;
        $inserted_pov = 0;

        foreach ($ag_options as $po) {
            // Očekujemo ključeve: option_id, required, value (za text/textarea), type, product_option_value (array)
            $option_id = (int)$po['option_id'];
            $required  = isset($po['required']) ? (int)$po['required'] : 0;
            $value     = isset($po['value']) ? $this->db->escape($po['value']) : '';

            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option 
                              SET product_id = " . (int)$product_id . ",
                                  option_id = " . $option_id . ",
                                  required = " . $required . ",
                                  value = '" . $value . "'");

            $product_option_id = $this->db->getLastId();
            $inserted_po++;

            if (!empty($po['product_option_value']) && is_array($po['product_option_value'])) {
                foreach ($po['product_option_value'] as $pov) {
                    // Očekujemo: option_value_id, quantity, subtract, price, price_prefix, points, points_prefix, weight, weight_prefix
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value
                                      SET product_option_id = " . (int)$product_option_id . ",
                                          product_id = " . (int)$product_id . ",
                                          option_id = " . $option_id . ",
                                          option_value_id = " . (int)$pov['option_value_id'] . ",
                                          quantity = " . (int)($pov['quantity'] ?? 0) . ",
                                          subtract = " . (int)($pov['subtract'] ?? 0) . ",
                                          price = '" . (float)($pov['price'] ?? 0) . "',
                                          price_prefix = '" . $this->db->escape($pov['price_prefix'] ?? '+') . "',
                                          points = " . (int)($pov['points'] ?? 0) . ",
                                          points_prefix = '" . $this->db->escape($pov['points_prefix'] ?? '+') . "',
                                          weight = '" . (float)($pov['weight'] ?? 0) . "',
                                          weight_prefix = '" . $this->db->escape($pov['weight_prefix'] ?? '+') . "'");
                    $inserted_pov++;
                }
            }
        }

        return ['product_option' => $inserted_po, 'product_option_value' => $inserted_pov];
    }
}
