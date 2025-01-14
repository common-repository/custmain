<?php
/* * * * * * * * * * * * * * * * 
 * Styles Realtime Processing  *
 * * * * * * * * * * * * * * * *
 * Version: 0.2
 * Author: Inyh - IT & Design Solutions
 * Developers: Vladislav Sivachuk
 * Author URI: https://inyh.ru
 */
	

class SRP{
	
	private $file;
	private $vars;
	private $errors;
	private $flags;
	
	public function __construct($file, $vars){
		$this->file = $file;
		$this->vars = $vars;
		$this->flags['minify'] = false;
	}
	
	public function setFlag($flag, $value){
		if($value == false) $this->flags[$flag] = false;
		else $this->flags[$flag] = true;
	}
	
	public function setVars($vars){
		$this->vars = $vars;
	}
	
	public function getStyles(){
		$styles = file_get_contents($this->file);
		$descr = "/*\r\nStylesheet generated by SRP\r\n*/\r\n";
		if($this->flags['minify']) $styles = $this->minifyStyles($styles);
		$styles = $this->insertVars($styles);
		$styles = $descr . $styles . $this->errors;
		return $styles;
	}
	
	public function printStyles(){
		header("Content-type: text/css; charset: UTF-8");
		echo $this->getStyles();
	}
	
	public function stylesToFile($rewrite = false){
		$filename = str_replace(".srp",".css",$this->file);
		if(!file_exists($filename) || $rewrite) file_put_contents($filename, $this->getStyles());
	}
	
	private function insertVars($code){
		$this->errors = "\r\n/*\r\n";
		foreach($this->vars as $var => $value){
			if(strpos($code, $var) === false) {
				$this->errors .= "Variable ".$var." not found to make substitution.\r\n";
				continue;
			}
			if(is_array($value)) while(strpos($code, $var) !== false) $code = $this->replaceVar($code, $var, $value);
			else $code = str_replace("|".$var."|", $value, $code);
		}
		$this->errors .= "*/";
		return $code;
	}
	
	private function replaceVar($code, $var, $value){
		$pos = strpos($code, "|".$var."|");
		
		$stylestart = strrpos(substr($code, 0, $pos), "}");
		if($stylestart != 0) $stylestart++;
		$styleend = strpos($code, "}", $pos);
		$subcode = "";
		foreach($value as $val){
			$subcode .= substr($code, $stylestart, $styleend-$stylestart+1);
			$subcode = str_replace("|".$var."|", $val, $subcode);
		}
		return substr($code, 0, $stylestart) . $subcode . substr($code, $styleend+1);
	}
	
	private function minifyStyles($code){
		$search = array(	"} ", 	"}\r\n", 	"}\n", 	"}\r\n ", 	"}\n ", 	"\t", 	"    ", 	"{\r\n", 	"{\n", 	"\r\n}", 	"\n}", 	";\r\n", 	";\n", 	", ", 	",\r\n", 	",\n", 	": ",	"\r\n", 	"\n");
		$replace = array(	"}", 	"}", 		"}", 	"}", 		"}", 		"", 	"", 		"{", 		"{", 	"}", 		"}", 	";", 		";", 	",", 	",", 		",", 	":",	"", 		"");
		return str_replace($search, $replace, $code);
	}
	
}

?>