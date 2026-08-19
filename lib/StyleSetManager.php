<?php

namespace UikitThemeBuilder;

/**
 * StyleSetManager für die Verwaltung von Extra-Style-Sets
 */
class StyleSetManager
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = \rex::getTable('uikit_style_sets');
    }

    /**
     * Alle Style-Sets abrufen
     */
    public function getAllStyleSets(bool $activeOnly = false): array
    {
        $sql = \rex_sql::factory();
        $query = "SELECT * FROM `{$this->tableName}`";
        
        if ($activeOnly) {
            $query .= " WHERE `is_active` = 1";
        }
        
        $query .= " ORDER BY `name` ASC";
        
        $sql->setQuery($query);
        $results = $sql->getArray();
        
        // JSON-Daten dekodieren
        foreach ($results as &$result) {
            $result['styles_data'] = json_decode($result['styles_data'], true) ?: [];
        }
        
        return $results;
    }

    /**
     * Style-Set nach ID abrufen
     */
    public function getStyleSetById(int $id): ?array
    {
        $sql = \rex_sql::factory();
        $sql->setQuery("SELECT * FROM `{$this->tableName}` WHERE `id` = :id", ['id' => $id]);
        
        if ($sql->getRows() > 0) {
            $result = $sql->getArray()[0];
            $result['styles_data'] = json_decode($result['styles_data'], true) ?: [];
            return $result;
        }
        
        return null;
    }

    /**
     * Style-Set nach Name abrufen
     */
    public function getStyleSetByName(string $name): ?array
    {
        $sql = \rex_sql::factory();
        $sql->setQuery("SELECT * FROM `{$this->tableName}` WHERE `name` = :name", ['name' => $name]);
        
        if ($sql->getRows() > 0) {
            $result = $sql->getArray()[0];
            $result['styles_data'] = json_decode($result['styles_data'], true) ?: [];
            return $result;
        }
        
        return null;
    }

    /**
     * Style-Set nach Slug abrufen
     */
    public function getStyleSetBySlug(string $slug): ?array
    {
        $sql = \rex_sql::factory();
        $sql->setQuery("SELECT * FROM `{$this->tableName}` WHERE `slug` = :slug", ['slug' => $slug]);
        
        if ($sql->getRows() > 0) {
            $result = $sql->getArray()[0];
            $result['styles_data'] = json_decode($result['styles_data'], true) ?: [];
            return $result;
        }
        
        return null;
    }

    /**
     * Neues Style-Set erstellen
     */
    public function createStyleSet(array $data): ?int
    {
        try {
            $sql = \rex_sql::factory();
            $sql->setTable($this->tableName);
            $sql->setValue('slug', $data['slug'] ?? '');
            $sql->setValue('name', $data['name'] ?? '');
            $sql->setValue('description', $data['description'] ?? '');
            $sql->setValue('styles_data', json_encode($data['styles_data'] ?? [], JSON_UNESCAPED_UNICODE));
            $sql->setValue('created', date('Y-m-d H:i:s'));
            $sql->setValue('is_active', $data['is_active'] ?? 1);
            
            $sql->insert();
            return (int)$sql->getLastId();
        } catch (\rex_sql_exception $e) {
            \rex_logger::factory()->error('StyleSetManager createStyleSet Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Style-Set aktualisieren
     */
    public function updateStyleSet(int $id, array $data): bool
    {
        try {
            $sql = \rex_sql::factory();
            $sql->setTable($this->tableName);
            $sql->setWhere(['id' => $id]);
            
            if (isset($data['slug'])) $sql->setValue('slug', $data['slug']);
            if (isset($data['name'])) $sql->setValue('name', $data['name']);
            if (isset($data['description'])) $sql->setValue('description', $data['description']);
            if (isset($data['styles_data'])) $sql->setValue('styles_data', json_encode($data['styles_data'], JSON_UNESCAPED_UNICODE));
            if (isset($data['is_active'])) $sql->setValue('is_active', $data['is_active']);
            
            return $sql->update() !== false;
        } catch (\rex_sql_exception $e) {
            \rex_logger::factory()->error('StyleSetManager updateStyleSet Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Style-Set löschen
     */
    public function deleteStyleSet(int $id): bool
    {
        try {
            $sql = \rex_sql::factory();
            $sql->setTable($this->tableName);
            $sql->setWhere(['id' => $id]);
            
            return $sql->delete() !== false;
        } catch (\rex_sql_exception $e) {
            \rex_logger::factory()->error('StyleSetManager deleteStyleSet Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Style-Set aktivieren/deaktivieren
     */
    public function toggleStyleSetStatus(int $id, bool $active): bool
    {
        try {
            $sql = \rex_sql::factory();
            $sql->setTable($this->tableName);
            $sql->setWhere(['id' => $id]);
            $sql->setValue('is_active', $active ? 1 : 0);
            $sql->setValue('updated_date', date('Y-m-d H:i:s'));
            $sql->setValue('updated_by', \rex::getUser()?->getValue('login') ?: 'system');
            
            return $sql->update() !== false;
        } catch (\rex_sql_exception $e) {
            \rex_logger::factory()->error('StyleSetManager toggleStyleSetStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * CSS für ein Style-Set generieren
     */
    public function generateCssForStyleSet(array $styleSet): string
    {
        $css = '';
        $styles = $styleSet['styles_data'] ?: [];
        
        foreach ($styles as $style) {
            if (empty($style['slug']) || empty($style['enabled'])) {
                continue;
            }
            
            $className = '.uk-' . $style['type'] . '-' . $style['slug'];
            $css .= "\n/* {$style['name']} */\n";
            $css .= "{$className} {\n";
            
            // Hintergrundfarbe
            if (!empty($style['background_color']) && $style['background_color'] !== '#ffffff') {
                $css .= "    background-color: {$style['background_color']};\n";
            }
            
            // Textfarbe
            if (!empty($style['text_color'])) {
                $css .= "    color: {$style['text_color']};\n";
            }
            
            // Rahmen
            if (!empty($style['border_color'])) {
                $css .= "    border-color: {$style['border_color']};\n";
            }
            
            if (!empty($style['border_width']) && $style['border_width'] !== '0') {
                $css .= "    border-width: {$style['border_width']}px;\n";
                $css .= "    border-style: solid;\n";
            }
            
            if (!empty($style['border_radius']) && $style['border_radius'] !== '0') {
                $css .= "    border-radius: {$style['border_radius']};\n";
            }
            
            // Backdrop Blur
            if (!empty($style['backdrop_blur']) && $style['backdrop_blur'] > 0) {
                $css .= "    backdrop-filter: blur({$style['backdrop_blur']}px);\n";
                $css .= "    -webkit-backdrop-filter: blur({$style['backdrop_blur']}px);\n";
            }
            
            $css .= "}\n";
            
            // Link-Farbe
            if (!empty($style['link_color'])) {
                $css .= "\n{$className} a,\n{$className} .uk-link {\n";
                $css .= "    color: {$style['link_color']};\n";
                $css .= "}\n";
            }
        }
        
        return $css;
    }

    /**
     * Style-Set duplizieren
     */
    public function duplicateStyleSet(int $id, string $newName): bool
    {
        $originalStyleSet = $this->getStyleSetById($id);
        
        if (!$originalStyleSet) {
            return false;
        }
        
        return $this->createStyleSet(
            $newName,
            $originalStyleSet['description'] . ' (Kopie)',
            $originalStyleSet['styles_data']
        );
    }

    /**
     * Style-Set exportieren (JSON)
     */
    public function exportStyleSet(int $id): ?string
    {
        $styleSet = $this->getStyleSetById($id);
        
        if (!$styleSet) {
            return null;
        }
        
        // Nur relevante Daten für Export
        $exportData = [
            'name' => $styleSet['name'],
            'description' => $styleSet['description'],
            'styles_data' => $styleSet['styles_data'],
            'export_date' => date('Y-m-d H:i:s'),
            'version' => '1.0'
        ];
        
        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Style-Set importieren (JSON)
     */
    public function importStyleSet(string $jsonData, bool $overwrite = false): bool
    {
        try {
            $importData = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Ungültige JSON-Daten');
            }
            
            $requiredFields = ['name', 'styles_data'];
            foreach ($requiredFields as $field) {
                if (!isset($importData[$field])) {
                    throw new \Exception("Feld '{$field}' fehlt in den Import-Daten");
                }
            }
            
            // Prüfen ob Name bereits existiert
            $existing = $this->getStyleSetByName($importData['name']);
            
            if ($existing && !$overwrite) {
                throw new \Exception("Style-Set mit Namen '{$importData['name']}' existiert bereits");
            }
            
            if ($existing && $overwrite) {
                // Aktualisieren
                return $this->updateStyleSet(
                    $existing['id'],
                    $importData['name'],
                    $importData['description'] ?? '',
                    $importData['styles_data']
                );
            } else {
                // Neu erstellen
                return $this->createStyleSet(
                    $importData['name'],
                    $importData['description'] ?? '',
                    $importData['styles_data']
                );
            }
        } catch (\Exception $e) {
            \rex_logger::factory()->error('StyleSetManager importStyleSet Error: ' . $e->getMessage());
            return false;
        }
    }
}