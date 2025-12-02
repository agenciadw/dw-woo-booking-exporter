<?php 
include(WBE_MODIFY_PLUGIN_PATH . '/vendor/mpdf60/mpdf.php');
$mpdf=new mPDF('c');
$html .= "<html>
    <head>
        <style></style>
    </head>
    <body>
        <table border='1' >
            <thead>
                <tr>";
				if(!empty($fieldArray)){
                    foreach ($fieldArray as $field) { 
                        $html .= "<th>".esc_html($field)."</th>";
                    }
				}
                $html .= "</tr>
            </thead>
            <tbody>";
			
			
			
			 
			if(!empty($dataArrays)){
			    foreach ($dataArrays as $dataArray) { 
					foreach ($dataArray as $line) { 
						$html .= "<tr>";
							foreach ($line as $col) { 
								$html .= "<td>".esc_html($col) ."</td>";
							} 
						$html .= "</tr>";
					} 
				 } 
			}	
            $html .= "</tbody>
        </table>   
    </body>
</html>";
$mpdf->WriteHTML($html);

