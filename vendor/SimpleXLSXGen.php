<?php
/**
 * Class SimpleXLSXGen
 * Author: Sergey Shuchkin <sergey.shuchkin@gmail.com>
 * License: MIT
 * Version: 1.0
 */
class SimpleXLSXGen {
    
    public $rows = [];
    
    public function addRow($row) {
        $this->rows[] = $row;
    }
    
    public function saveToFile($filename) {
        $data = [];
        foreach ($this->rows as $row) {
            $data[] = $row;
        }
        
        return $this->save($filename, $data);
    }
    
    public function save($filename, $data) {
        $zip = new ZipArchive();
        
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return false;
        }
        
        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', 
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' .
            '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
            '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
            '</Types>');
        
        // _rels/.rels
        $zip->addEmptyDir('_rels');
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
            '</Relationships>');
        
        // docProps
        $zip->addEmptyDir('docProps');
        $zip->addFromString('docProps/core.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            '<dc:creator>WooCommerce Booking Exporter</dc:creator>' .
            '<cp:lastModifiedBy>WooCommerce Booking Exporter</cp:lastModifiedBy>' .
            '<dcterms:created xsi:type="dcterms:W3CDTF">' . date('Y-m-d\TH:i:s\Z') . '</dcterms:created>' .
            '<dcterms:modified xsi:type="dcterms:W3CDTF">' . date('Y-m-d\TH:i:s\Z') . '</dcterms:modified>' .
            '</cp:coreProperties>');
        
        $zip->addFromString('docProps/app.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
            '<Application>Microsoft Excel</Application>' .
            '<DocSecurity>0</DocSecurity>' .
            '<ScaleCrop>false</ScaleCrop>' .
            '<Company></Company>' .
            '<LinksUpToDate>false</LinksUpToDate>' .
            '<SharedDoc>false</SharedDoc>' .
            '<HyperlinksChanged>false</HyperlinksChanged>' .
            '<AppVersion>16.0300</AppVersion>' .
            '</Properties>');
        
        // xl
        $zip->addEmptyDir('xl');
        $zip->addEmptyDir('xl/_rels');
        $zip->addEmptyDir('xl/worksheets');
        
        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
            '</Relationships>');
        
        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<fileVersion appName="xl" lastEdited="6" lowestEdited="6" rupBuild="14420"/>' .
            '<workbookPr defaultThemeVersion="164011"/>' .
            '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="19020"/></bookViews>' .
            '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>' .
            '<calcPr calcId="162913"/>' .
            '</workbook>');
        
        // xl/styles.xml
        $zip->addFromString('xl/styles.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="1"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font></fonts>' .
            '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>' .
            '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>' .
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
            '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>' .
            '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>' .
            '<dxfs count="0"/>' .
            '<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleMedium4"/>' .
            '</styleSheet>');
        
        // Build shared strings
        $sharedStrings = [];
        $sharedStringsMap = [];
        $sharedStringIndex = 0;
        
        foreach ($data as $row) {
            foreach ($row as $cell) {
                if (!is_numeric($cell) && !isset($sharedStringsMap[$cell])) {
                    $sharedStringsMap[$cell] = $sharedStringIndex++;
                    $sharedStrings[] = $cell;
                }
            }
        }
        
        // xl/sharedStrings.xml
        $sharedStringsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        
        foreach ($sharedStrings as $str) {
            $sharedStringsXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        
        $sharedStringsXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
        
        // xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<dimension ref="A1"/>' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><selection activeCell="A1" sqref="A1"/></sheetView></sheetViews>' .
            '<sheetFormatPr defaultRowHeight="15"/>' .
            '<sheetData>';
        
        $rowIndex = 1;
        foreach ($data as $row) {
            $sheetXml .= '<row r="' . $rowIndex . '" spans="1:' . count($row) . '">';
            $colIndex = 0;
            
            foreach ($row as $cell) {
                $cellRef = $this->columnLetter($colIndex) . $rowIndex;
                
                if (is_numeric($cell)) {
                    $sheetXml .= '<c r="' . $cellRef . '"><v>' . htmlspecialchars($cell, ENT_XML1, 'UTF-8') . '</v></c>';
                } else {
                    $stringIndex = $sharedStringsMap[$cell];
                    $sheetXml .= '<c r="' . $cellRef . '" t="s"><v>' . $stringIndex . '</v></c>';
                }
                
                $colIndex++;
            }
            
            $sheetXml .= '</row>';
            $rowIndex++;
        }
        
        $sheetXml .= '</sheetData><pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        
        $zip->close();
        
        return true;
    }
    
    private function columnLetter($index) {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }
    
    public static function fromArray($data) {
        $xlsx = new self();
        $xlsx->rows = $data;
        return $xlsx;
    }
}

