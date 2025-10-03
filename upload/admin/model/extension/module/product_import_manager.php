<?php
class ModelExtensionModuleProductImportManager extends Model
{
    // === PUBLIC API ===

    /**
     * Drop & replace svih opcija za product_id s jednom "Veličina" (select) opcijom
     * @param int   $product_id
     * @param array $items [ ['name'=>..., 'quantity'=>..., 'price'=>..., 'price_prefix'=>...], ... ]
     * @return array counts
     */
    public function replaceOptionsWithSize(int $product_id, array $items): array
    {
        $language_id = (int)$this->config->get('config_language_id');

        // (A) UZMI POSTOJEĆI option_id IZ CONFIGA
        $option_id = (int)(agconf('erp.size_option_id') ?? 0);

        if (!$option_id) {
            // fallback: pokušaj naći po nazivu u BILO kojem jeziku
            $option_id = $this->findOptionIdByNameAnyLang(['Veličina','Velicina','Size']);
            if (!$option_id) {
                // ako NIŠTA ne postoji, tek onda kreiraj (ali to je iznimka)
                $option_id = $this->createOptionWithNames([
                    $language_id => 'Veličina'
                ], 'select', ['en-gb' => 'Size']);
            }
        }

        // (B) BRIŠI SAMO “Veličina” ZA TAJ PROIZVOD (ne sve opcije!)
        $this->deleteOneProductOption($product_id, $option_id);

        // (C) INSERT product_option za taj option_id
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option 
                      SET product_id = " . (int)$product_id . ", 
                          option_id = " . (int)$option_id . ",
                          value = '',
                          required = 1");
        $product_option_id = $this->db->getLastId();

        // (D) Ubaci vrijednosti + SKU
        $inserted_pov = 0;
        foreach ($items as $i => $row) {
            $name         = $this->db->escape($row['name']);
            $quantity     = (int)($row['quantity'] ?? 0);
            $subtract     = (int)($row['subtract'] ?? 1);
            $price        = (float)($row['price'] ?? 0);
            $price_prefix = $this->db->escape($row['price_prefix'] ?? '+');
            $points       = 0;
            $points_prefix= '+';
            $weight       = (float)($row['weight'] ?? 0);
            $weight_prefix= $this->db->escape($row['weight_prefix'] ?? '+');
            $sort_order   = (int)($row['sort_order'] ?? $i);
            $sku          = $this->db->escape($row['sku'] ?? '');   // ← OBAVEZNO

            $option_value_id = $this->getOrCreateOptionValue($option_id, $name, $language_id, $sort_order);

            // Ako nisi 100% da stupac postoji, možeš koristiti guard (vidi dolje)
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

        return ['product_option' => 1, 'product_option_value' => $inserted_pov];
    }


    // === HELPERS ===

    private function deleteProductOptions(int $product_id): void
    {
        // prvo value pa option (zbog FK)
        $this->db->query("DELETE pov FROM " . DB_PREFIX . "product_option_value pov
                          JOIN " . DB_PREFIX . "product_option po 
                            ON po.product_option_id = pov.product_option_id
                         WHERE po.product_id = " . (int)$product_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option 
                          WHERE product_id = " . (int)$product_id);
    }

    /**
     * Vrati option_id za ime opcije; ako ne postoji, kreiraj
     */
    private function getOrCreateOption(string $name_hr, string $type, int $language_id, array $extra_langs = []): int
    {
        $q = $this->db->query("SELECT o.option_id 
                                 FROM " . DB_PREFIX . "option o
                                 JOIN " . DB_PREFIX . "option_description od 
                                   ON od.option_id = o.option_id
                                WHERE od.name = '" . $this->db->escape($name_hr) . "'
                                  AND od.language_id = " . (int)$language_id . "
                                  AND o.type = '" . $this->db->escape($type) . "'
                                LIMIT 1");
        if ($q->num_rows) return (int)$q->row['option_id'];

        // create
        $this->db->query("INSERT INTO " . DB_PREFIX . "option 
                          SET type = '" . $this->db->escape($type) . "', sort_order = 0");
        $option_id = (int)$this->db->getLastId();

        // HR
        $this->db->query("INSERT INTO " . DB_PREFIX . "option_description 
                          SET option_id = " . $option_id . ",
                              language_id = " . (int)$language_id . ",
                              name = '" . $this->db->escape($name_hr) . "'");

        // dodatni jezici (po želji)
        foreach ($extra_langs as $code => $label) {
            $lang_id = $this->getLanguageIdByCode($code);
            if ($lang_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "option_description 
                                  SET option_id = " . $option_id . ",
                                      language_id = " . (int)$lang_id . ",
                                      name = '" . $this->db->escape($label) . "'");
            }
        }

        return $option_id;
    }

    /**
     * Option value (po imenu) – ako ne postoji, kreiraj
     */
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

        // create
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

    private function getLanguageIdByCode(string $code): ?int
    {
        $q = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language 
                               WHERE code = '" . $this->db->escape($code) . "' LIMIT 1");
        return $q->num_rows ? (int)$q->row['language_id'] : null;
    }
    private function deleteOneProductOption(int $product_id, int $option_id): void
    {
        // pobriši vrijednosti za baš taj option_id
        $this->db->query("DELETE pov FROM " . DB_PREFIX . "product_option_value pov
                      JOIN " . DB_PREFIX . "product_option po 
                        ON po.product_option_id = pov.product_option_id
                     WHERE po.product_id = " . (int)$product_id . "
                       AND po.option_id  = " . (int)$option_id);

        // pobriši i sam product_option za taj option_id
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option 
                      WHERE product_id = " . (int)$product_id . "
                        AND option_id  = " . (int)$option_id);
    }

    private function findOptionIdByNameAnyLang(array $names): ?int
    {
        $names_esc = array_map(function($n){ return "'" . $this->db->escape($n) . "'"; }, $names);
        $q = $this->db->query("SELECT o.option_id
                             FROM " . DB_PREFIX . "option o
                             JOIN " . DB_PREFIX . "option_description od
                               ON od.option_id = o.option_id
                            WHERE od.name IN (" . implode(',', $names_esc) . ")
                              AND o.type = 'select'
                            LIMIT 1");
        return $q->num_rows ? (int)$q->row['option_id'] : null;
    }

    private function createOptionWithNames(array $names_by_lang_id, string $type, array $extra_by_code = []): int
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "option 
                      SET type = '" . $this->db->escape($type) . "', sort_order = 0");
        $option_id = (int)$this->db->getLastId();

        foreach ($names_by_lang_id as $lang_id => $label) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description 
                          SET option_id = " . (int)$option_id . ",
                              language_id = " . (int)$lang_id . ",
                              name = '" . $this->db->escape($label) . "'");
        }

        foreach ($extra_by_code as $code => $label) {
            $lang_id = $this->getLanguageIdByCode($code);
            if ($lang_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "option_description 
                              SET option_id = " . (int)$option_id . ",
                                  language_id = " . (int)$lang_id . ",
                                  name = '" . $this->db->escape($label) . "'");
            }
        }

        return $option_id;
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
