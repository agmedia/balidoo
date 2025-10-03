<?php
class ModelExtensionModuleProductImportManager extends Model
{
    /**
     * Drop & replace vrijednosti za JEDNU opciju "Veličina" na proizvodu.
     * - koristi postojeći option_id (erp.size_option_id)
     * - briše SAMO tu opciju za proizvod
     * - umeće nove POV retke i upisuje SKU ako kolona postoji
     *
     * @param int   $product_id
     * @param array $items [ ['name','quantity','price','price_prefix','weight','weight_prefix','subtract','sort_order','sku'], ... ]
     * @return array ['product_option' => int, 'product_option_value' => int, 'total_qty' => int]
     */
    public function replaceOptionsWithSize(int $product_id, array $items): array
    {
        $language_id = (int)$this->config->get('config_language_id');

        // 1) option_id iz konfiguracije
        $option_id = (int)(agconf('erp.size_option_id') ?? 0);
        if (!$option_id) {
            $option_id = $this->findOptionIdByNameAnyLang(['Veličina','Velicina','Size']);
            if (!$option_id) {
                throw new \RuntimeException('Missing size_option_id (erp.size_option_id).');
            }
        }

        // 2) obriši SAMO tu opciju za ovaj proizvod
        $this->deleteOneProductOption($product_id, $option_id);

        // 3) kreiraj product_option
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option 
                          SET product_id = " . (int)$product_id . ", 
                              option_id  = " . (int)$option_id . ",
                              value = '',
                              required = 1");
        $product_option_id = $this->db->getLastId();

        // 4) POV insert + SKU + total qty
        $inserted_pov = 0;
        $total_qty    = 0;

        foreach ($items as $i => $row) {
            $name          = $this->db->escape($row['name']);
            $quantity      = (int)($row['quantity'] ?? 0);
            $subtract      = (int)($row['subtract'] ?? 1);
            $price         = (float)($row['price'] ?? 0);
            $price_prefix  = $this->db->escape($row['price_prefix'] ?? '+');
            $points        = 0;
            $points_prefix = '+';
            $weight        = (float)($row['weight'] ?? 0);
            $weight_prefix = $this->db->escape($row['weight_prefix'] ?? '+');
            $sort_order    = (int)($row['sort_order'] ?? $i);
            $sku           = $this->db->escape($row['sku'] ?? '');

            $total_qty += $quantity;

            // osiguraj option_value po imenu
            $option_value_id = $this->getOrCreateOptionValue($option_id, $name, $language_id, $sort_order);

            // insert POV (sa SKU kolonom ako postoji)
            $sql = "INSERT INTO " . DB_PREFIX . "product_option_value
                    SET product_option_id = " . (int)$product_option_id . ",
                        product_id        = " . (int)$product_id . ",
                        option_id         = " . (int)$option_id . ",
                        option_value_id   = " . (int)$option_value_id . ",
                        quantity          = " . (int)$quantity . ",
                        subtract          = " . (int)$subtract . ",
                        price             = '" . $price . "',
                        price_prefix      = '" . $price_prefix . "',
                        points            = " . (int)$points . ",
                        points_prefix     = '" . $points_prefix . "',
                        weight            = '" . $weight . "',
                        weight_prefix     = '" . $weight_prefix . "'";
            if ($this->povHasSkuColumn()) {
                $sql .= ", sku = '" . $sku . "'";
            }
            $this->db->query($sql);
            $inserted_pov++;
        }

        return ['product_option' => 1, 'product_option_value' => $inserted_pov, 'total_qty' => $total_qty];
    }

    /* =================== HELPERS =================== */

    private function deleteOneProductOption(int $product_id, int $option_id): void
    {
        // prvo value pa option (FK)
        $this->db->query("DELETE pov FROM " . DB_PREFIX . "product_option_value pov
                          JOIN " . DB_PREFIX . "product_option po 
                            ON po.product_option_id = pov.product_option_id
                         WHERE po.product_id = " . (int)$product_id . "
                           AND po.option_id  = " . (int)$option_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option 
                          WHERE product_id = " . (int)$product_id . "
                            AND option_id  = " . (int)$option_id);
    }

    private function findOptionIdByNameAnyLang(array $names): ?int
    {
        $names_esc = array_map(fn($n) => "'" . $this->db->escape($n) . "'", $names);
        $q = $this->db->query("SELECT o.option_id
                                 FROM " . DB_PREFIX . "option o
                                 JOIN " . DB_PREFIX . "option_description od
                                   ON od.option_id = o.option_id
                                WHERE od.name IN (" . implode(',', $names_esc) . ")
                                  AND o.type = 'select'
                                LIMIT 1");
        return $q->num_rows ? (int)$q->row['option_id'] : null;
    }

    private function getOrCreateOptionValue(int $option_id, string $name, int $language_id, int $sort_order = 0): int
    {
        $q = $this->db->query("SELECT ov.option_value_id 
                                 FROM " . DB_PREFIX . "option_value ov
                                 JOIN " . DB_PREFIX . "option_value_description ovd 
                                   ON ovd.option_value_id = ov.option_value_id
                                WHERE ov.option_id = " . (int)$option_id . "
                                  AND ovd.language_id = " . (int)$language_id . "
                                  AND ovd.name = '" . $this->db->escape($name) . "'
                                LIMIT 1");
        if ($q->num_rows) return (int)$q->row['option_value_id'];

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value 
                          SET option_id = " . (int)$option_id . ",
                              image = '',
                              sort_order = " . (int)$sort_order);
        $option_value_id = (int)$this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description 
                          SET option_value_id = " . (int)$option_value_id . ",
                              language_id = " . (int)$language_id . ",
                              option_id = " . (int)$option_id . ",
                              name = '" . $this->db->escape($name) . "'");

        return $option_value_id;
    }

    public function removeSizeOptionForProduct(int $product_id): void
    {
        // koristi postojeći option_id iz konfiguracije
        $option_id = (int)(agconf('erp.size_option_id') ?? 0);
        if (!$option_id) {
            $option_id = $this->findOptionIdByNameAnyLang(['Veličina','Velicina','Size']);
            if (!$option_id) {
                // ako nema ni po imenu, nema što brisati
                return;
            }
        }
        // pobriši SAMO tu opciju s artikla
        $this->deleteOneProductOption($product_id, $option_id);
    }


    private function povHasSkuColumn(): bool
    {
        static $has = null;
        if ($has === null) {
            $q = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product_option_value LIKE 'sku'");
            $has = (bool)$q->num_rows;
        }
        return $has;
    }
}
