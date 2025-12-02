<?php if($key == 0){  
		
	$html .= "<html>
	<head>
		<style></style>
	</head>
	<body>
		<table border='1' >
		<thead>
			<tr> ";
			
			foreach ($fieldArray as $field) {
				$html .= "<th>". esc_html($field)."</th>";
			 } 
			$html .= "</tr>
		</thead>
		<tbody>	";
		
		
		 } 
		 
		 
		
		$html .= "<tr>";
			foreach ($line as $col) {
			$html .= "<td>". esc_html($col) ." </td>";
			} 
		$html  .= "</tr>";
		
		
		
		
	
		if(($end_point - 1) == $key){
		$html .= "</tbody>
		</table>
	</body>
	</html> ";
	
	 }

			
			
	