<?php

## this will analyze word granules and get specific granule upon calls
#vowel: $parts[1] <"ྐ" and $parts[1] >"ཬ"
#sub script: $parts[1] <"྾" and $parts[1] >"ྏ"
#consonants: $parts[1] <"ཱ" and $parts[1]>"༿"
#tibetan subs: $parts[1]< "ྴ" and $parts[1]>"ྫ"

class WordGranules{
	private $granules=array();
	private $puncts="";
	
	private $wordParts=array("prefix","super_script","root","sub_script","wazhur","vowel","suffix","post_suffix");
	#when root is a sub-script, it needs to be changed as a consonant, so does this dict
	private $subToCons = array("ྐ"=>"ཀ","ྑ"=>"ཁ","ྒ"=>"ག","ྒྷ"=>"གྷ","ྔ"=>"ང","ྕ"=>"ཅ",
		"ྖ"=>"ཆ","ྗ"=>"ཇ","ྙ"=>"ཉ","ྚ"=>"ཊ","ྛ"=>"ཋ","ྜ"=>"ཌ","ྜྷ"=>"ཌྷ",
		"ྞ"=>"ཎ","ྟ"=>"ཏ","ྠ"=>"ཐ","ྡ"=>"ད","ྡྷ"=>"དྷ","ྣ"=>"ན","ྤ"=>"པ",
		"ྥ"=>"ཕ","ྦ"=>"བ","ྦྷ"=>"བྷ","ྨ"=>"མ","ྩ"=>"ཙ","ྪ"=>"ཚ","ྫ"=>"ཛ",
		"ྫྷ"=>"ཛྷ","ྮ"=>"ཞ","ྯ"=>"ཟ","ླ"=>"ལ","ྴ"=>"ཤ","ྵ"=>"ཥ","ྶ"=>"ས",
		"ྷ"=>"ཧ","ྸ"=>"ཨ","ྐྵ"=>"ཀྵ","ྺ"=>"ཝ","ྻ"=>"ཡ","ྼ"=>"ར");
	
	public function __construct() {
		$this->resetGranules();
	}

	private function resetGranules(){
		// $wordParts=array("prefix","super_script","root","sub_script","wazhur","vowel","suffix","post_suffix");

		for ($i = 0; $i <count($this->wordParts); $i++){

			$temp = $this->wordParts[$i];
			$this->granules[$temp]=0;	
		}

	}

	public function getOrderedParts($word){
		$wordLen = mb_strlen($word, 'utf8');

		#this should be either sanskrit or typo, and it will sorted to the end
		if ($wordLen > 7) {
			return "࿚࿚࿚";
		}

		$parts = array();
		for ($i=0; $i < $wordLen; $i++){
			$parts[] = mb_substr( $word, $i, 1, 'utf8');

		}
       /*		echo "char split: <br>";
		print_r($parts);
		echo "<br>";*/
		$onlyChars=$this->findPuncts($parts,$wordLen);

		#varible function for performance
		$funcName = "char" . count($onlyChars);
		return $this->$funcName($onlyChars);
	}

	private function findPuncts($parts, $wordLen){
		$noPuncChars=array();
		for ($i=0; $i < $wordLen; $i++) {
			if ($parts[$i] < "ཀ") {
				$this->puncts.=$parts[$i];
			}else{
				array_push($noPuncChars, $parts[$i]);
			}
		}
		return $noPuncChars;
	}

	private function char0($parts){
		
		return null;
	}

	private function char1($parts){
		#this does both part assignment and value returning
		$wholeWord=$parts[0].$this->puncts;
		return $this->granules["root"]=$wholeWord;
	}

	private function char2($parts){

		if ($parts[1] <"྾" and $parts[1] >"ྏ") {
			if ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
				$this->assignParts(array("root","sub_script"), $parts);
				return $this->printParts(array("sub_script"));
			} else{
				$this->assignParts(array("super_script","root"), $parts);
				return $this->printParts(array("super_script"));
			}

		} elseif ($parts[1] <"ྐ" and $parts[1] >"ཬ") {
			$this->assignParts(array("root","vowel"), $parts);
			return $this->printParts(array("vowel"));
		} else {
			$this->assignParts(array("root","suffix"), $parts);
			return $this->printParts(array("suffix"));
		}
	}

	private function char3($parts){
		#check second is consnant, split 6-6
		if ($parts[1] <"ཱ" and $parts[1]>"༿") {
			#check last is consnant, split 2 to 4
			if ($parts[2] <"ཱ" and $parts[2]>"༿") {
				#dealing with post
				if ($parts[2] == "ས") {
					if ($this->post_sa($parts)) {
						$this->assignParts(array("root","suffix","post_suffix"), $parts);
						return $this->printParts(array("post_suffix","suffix"));
					}else{
						$this->assignParts(array("prefix","root","suffix"), $parts);
						return $this->printParts(array("prefix","suffix"));
					}
				}else{
					$this->assignParts(array("prefix","root","suffix"), $parts);
					return $this->printParts(array("prefix","suffix"));
				}
			#check last is a vowel, split 2-2
			}elseif ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
				if ($parts[1] == "འ") {
					$this->assignColParts(array("root","suffix"), $parts);
					return $this->printParts(array("suffix"));
				}else{
					$this->assignParts(array("prefix","root","vowel"), $parts);
					return $this->printParts(array("prefix","vowel"));
				}
			}elseif ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
				$this->assignParts(array("prefix","root","sub_script"), $parts);
				return $this->printParts(array("sub_script","prefix",));
			}else{
				$this->assignParts(array("prefix","super_script","root"), $parts);
				return $this->printParts(array("super_script","prefix"));
			}
		#check suffix, 3-3
		}elseif ($parts[2] <"ཱ" and $parts[2]>"༿") {
			if ($parts[1] <"ྐ" and $parts[1] >"ཬ") {
				$this->assignParts(array("root","vowel","suffix"), $parts);
				return $this->printParts(array("vowel","suffix"));
			}elseif ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
				$this->assignParts(array("root","sub_script","suffix"), $parts);
				return $this->printParts(array("sub_script","suffix"));
			}else{
				$this->assignParts(array("super_script","root","suffix"), $parts);
				return $this->printParts(array("super_script","suffix"));
			}
		}elseif ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
			if ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
				$this->assignParts(array("root","sub_script","vowel"), $parts);
				return $this->printParts(array("sub_script","vowel"));
			}else{
				$this->assignParts(array("super_script","root","vowel"), $parts);
				return $this->printParts(array("super_script","vowel"));
			}
		}elseif ($parts[2] == "ྭ") {
			if ($parts[1]< "ྴ" and $parts[1]>"ྫ"){
				$this->assignParts(array("root","sub_script","wazhur"), $parts);
				return $this->printParts(array("sub_script","wazhur"));
			}else{
				$this->assignParts(array("super_script","root","wazhur"), $parts);
				return $this->printParts(array("super_script","wazhur"));
			}
			
		}else{
			$this->assignParts(array("super_script","root","sub_script"), $parts);
			return $this->printParts(array("super_script","sub_script"));
		}
	}

	private function char4($parts){
		#check prefix, split 7+1 to 7+3
		if ($parts[1] <"ཱ" and $parts[1]>"༿") {
			#check suffix, split 4 to 3
			if ($parts[3] <"ཱ" and $parts[3]>"༿") {
				#third letter if it is a sub
				if ($parts[2] <"྾" and $parts[2] >"ྏ") {
					# check third letter is tibetan sub
					if ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
						$this->assignParts(array("prefix","root","sub_script","suffix"), $parts);
						return $this->printParts(array("sub_script","prefix","suffix"));
					}else{
						$this->assignParts(array("prefix","super_script","root","suffix"), $parts);
						return $this->printParts(array("super_script","prefix","suffix"));
					}
					
					
				}elseif ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
					$this->assignParts(array("prefix","root","vowel","suffix"), $parts);	
					return $this->printParts(array("prefix","vowel","suffix"));
				}else {
					$this->assignParts(array("prefix","root","suffix","post_suffix"), $parts);
					return $this->printParts(array("prefix","post_suffix","suffix"));
				}
			#check if last is vowel
			}elseif ($parts[3] <"ྐ" and $parts[3] >"ཬ") {
				#check if third is sub
				if ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
					$this->assignParts(array("prefix","root","sub_script","vowel"), $parts);
					return $this->printParts(array("sub_script","prefix","vowel"));
				#dealing with wa 
				}elseif ($parts[2] == "འ") {
					$this->assignColParts(array("prefix","root","suffix"), $parts);
					return $this->printParts(array("prefix","suffix"));
				}else{
					$this->assignParts(array("prefix","super_script","root","vowel"), $parts);	
					return $this->printParts(array("super_script","prefix","vowel"));
				}
			}else{
				$this->assignParts(array("prefix","super_script","root","sub_script"), $parts);
				return $this->printParts(array("super_script","sub_script","prefix"));
				
			}
		##check post, split 3+3 to 4
		}elseif ($parts[2] <"ཱ" and $parts[2]>"༿") {
			#check last is vowel, split 3 to 3, dealing with wa 
			if ($parts[3] <"ྐ" and $parts[3] >"ཬ") {
				if ($parts[1] <"ྐ" and $parts[1] >"ཬ") {
					$this->assignColParts(array("root","vowel","suffix"), $parts);
					return $this->printParts(array("vowel","suffix"));
				}elseif ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
					$this->assignColParts(array("root","sub_script","suffix"), $parts);
					return $this->printParts(array("sub_script","suffix"));
				}else{
					$this->assignColParts(array("super_script","root","suffix"), $parts);
					return $this->printParts(array("super_script","suffix"));
				}
			
			}elseif ($parts[1] <"ྐ" and $parts[1] >"ཬ") {
				$this->assignParts(array("root","vowel","suffix","post_suffix"), $parts);
				return $this->printParts(array("vowel","post_suffix","suffix"));
			}elseif ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
				if ($parts[1] == "ྭ") {
					$this->assignParts(array("root","wazhur","suffix","post_suffix"), $parts);
					return $this->printParts(array("wazhur","post_suffix","suffix"));
				}else{
					$this->assignParts(array("root","sub_script","suffix","post_suffix"), $parts);
					return $this->printParts(array("sub_script","post_suffix","suffix"));
				}
			}else{
				$this->assignParts(array("super_script","root","suffix","post_suffix"), $parts);
				return $this->printParts(array("super_script","post_suffix","suffix"));
			}

		#check the third is vowel, split 2 to 2
		}elseif($parts[2] <"ྐ" and $parts[2] >"ཬ") {
			if ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
				$this->assignParts(array("root","sub_script","vowel","post_suffix"), $parts);
				return $this->printParts(array("sub_script","vowel","post_suffix"));
			
			} else{
				$this->assignParts(array("super_script","root","vowel","suffix"), $parts);
				return $this->printParts(array("super_script","vowel","suffix"));
			
			}
		##check if last letter is vowel, split 1 to 1
		}elseif ($parts[3] <"ྐ" and $parts[3] >"ཬ") {
			$this->assignParts(array("super_script","root","sub_script","vowel"), $parts);
			return $this->printParts(array("super_script","sub_script","vowel"));
		}else{
			$this->assignParts(array("super_script","root","sub_script","suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","suffix"));
			
		}
	}

	private function char5($parts){
		##check post, split with size of 11 to 5
		if ($parts[3] <"ཱ" and $parts[3]>"༿") {
			#check vowel for wa, split 6-5
			if ($parts[4] <"ྐ" and $parts[4] >"ཬ") {
				#check prefix, split 3-3
				if ($parts[1] <"ཱ" and $parts[1]>"༿") {
					#check vowel
					if ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
						$this->assignColParts(array("prefix","root","vowel","suffix"), $parts);
						return $this->printParts(array("prefix","vowel","suffix"));
					}elseif ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
						$this->assignColParts(array("prefix","root","sub_script","suffix"), $parts);
						return $this->printParts(array("sub_script","prefix","suffix"));
					}else{
						$this->assignColParts(array("prefix","super_script","root","suffix"), $parts);
						return $this->printParts(array("super_script","prefix","suffix"));
					}
				}elseif ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
					if ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
						$this->assignColParts(array("root","sub_script","vowel","suffix"), $parts);
						return $this->printParts(array("sub_script","vowel","suffix"));
					}else{
						$this->assignColParts(array("super_script","root","vowel","suffix"), $parts);
						return $this->printParts(array("super_script","vowel","suffix"));
					}
				}else{
					$this->assignColParts(array("super_script","root","sub_script","suffix"), $parts);
					return $this->printParts(array("super_script","sub_script","suffix"));
				}
			}
			##check prefix, split again with 2 and 3
			elseif($parts[1] <"ཱ" and $parts[1]>"༿") {
				##if third is a vowel
				if ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
					$this->assignParts(array("prefix","root","vowel","suffix", "post_suffix"), $parts);
					return $this->printParts(array("prefix","vowel","post_suffix","suffix"));
				}else{
					$this->assignParts(array("prefix","root","sub_script","suffix","post_suffix"), $parts);
					return $this->printParts(array("sub_script","prefix","post_suffix","suffix"));
				}
			}elseif ($parts[2] <"ྐ" and $parts[2] >"ཬ") {
				if ($parts[1]< "ྴ" and $parts[1]>"ྫ") {
					$this->assignParts(array("root","sub_script","vowel","suffix","post_suffix"), $parts);
					return $this->printParts(array("sub_script","vowel","post_suffix","suffix"));
				}else{
					$this->assignParts(array("super_script","root","vowel","suffix","post_suffix"), $parts);
					return $this->printParts(array("super_script","vowel","post_suffix","suffix"));
				}
			}else{
				$this->assignParts(array("super_script","root","sub_script","suffix","post_suffix"), $parts);
				return $this->printParts(array("super_script","sub_script","post_suffix","suffix"));
			}
		#check vowel, split 2 and 3
		}elseif ($parts[3] <"ྐ" and $parts[3] >"ཬ") {
			#check prefix, split 2 to 1
			if ($parts[1] <"ཱ" and $parts[1]>"༿") {
				#check sub, split 2 and 2
				if ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
					$this->assignParts(array("prefix","root","sub_script","vowel","suffix"), $parts);
					return $this->printParts(array("sub_script","prefix","vowel","suffix"));
				}else{
					$this->assignParts(array("prefix","super_script","root","vowel","suffix"), $parts);
					return $this->printParts(array("super_script","prefix","vowel","suffix"));
				}	
			}else{
				$this->assignParts(array("super_script","root","sub_script","vowel","suffix"), $parts);
				return $this->printParts(array("super_script","sub_script","vowel","suffix"));
			}
			
		#check last is vowel, split 1 to 1
		}elseif ($parts[4] <"ྐ" and $parts[4] >"ཬ") {
			$this->assignParts(array("prefix","super_script","root","sub_script","vowel"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","vowel"));
		}else{
			$this->assignParts(array("prefix","super_script","root","sub_script","suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","suffix"));
		}
	}

	private function char6($parts){
		if ($parts[3] <"ྐ" and $parts[3] >"ཬ") {
			#check last is vowel for wa, 3-2
			if ($parts[5] <"ྐ" and $parts[5] >"ཬ") {
				if ($parts[1] <"ཱ" and $parts[1]>"༿") {
					$this->assignColParts(array("prefix","root","sub_script","vowel","suffix"), $parts);	
					return $this->printParts(array("sub_script","prefix","vowel","suffix"));
				}else{
					$this->assignColParts(array("super_script","root","sub_script","vowel","suffix"), $parts);	
					return $this->printParts(array("super_script","sub_script","vowel","suffix"));
				}
			#check pref, 2,1
			}elseif ($parts[1] <"ཱ" and $parts[1]>"༿") {
				if ($parts[2]< "ྴ" and $parts[2]>"ྫ") {
					$this->assignParts(array("prefix","root","sub_script","vowel","suffix", "post_suffix"), $parts);
					return $this->printParts(array("sub_script","prefix","vowel","post_suffix","suffix"));
				}else{
					$this->assignParts(array("prefix","super_script","root","vowel","suffix", "post_suffix"), $parts);
					return $this->printParts(array("super_script","prefix","vowel","post_suffix","suffix"));
				}
			}else{
				$this->assignParts(array("super_script","root","sub_script","vowel","suffix", "post_suffix"), $parts);
				return $this->printParts(array("super_script","sub_script","vowel","post_suffix","suffix"));
			}
		#
		}elseif ($parts[4] <"ཱ" and $parts[4]>"༿") {
			$this->assignParts(array("prefix","super_script","root","sub_script","suffix", "post_suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","post_suffix","suffix"));
		#another wa
		}elseif ($parts[4] == "འ") {
			$this->assignColParts(array("prefix","super_script","root","sub_script","suffix"), $parts);	
			return $this->printParts(array("super_script","sub_script","prefix","suffix"));
		}else{
			$this->assignParts(array("prefix","super_script","root","sub_script","vowel","suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","vowel","suffix"));
		}

	}

	private function char7($parts){
		#dealing with wi
		if ($parts[5] == "འ") {
			$this->assignColParts(array("prefix","super_script","root","sub_script","vowel","suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","vowel","suffix"));
		}else{
			$this->assignParts(array("prefix","super_script","root","sub_script","vowel","suffix","post_suffix"), $parts);
			return $this->printParts(array("super_script","sub_script","prefix","vowel","post_suffix","suffix"));
		}
	}

	private function post_da($parts){
		$partSize=count($parts);
		$theSuf=$parts[$partSize-2];

		$narala = array("ན","ར","ལ");
		foreach ($narala as $each) {
			if ($theSuf == $each) {
				return true;
				break;
			}
		}
		return false;
	}
	
	private function post_sa($parts,$partSize){
		$partSize=count($parts);
		$theSuf=$parts[$partSize-2];
		$gnbm = array("ག","ང","བ","མ");
		foreach ($gnbm as $each) {
			if ($theSuf == $each) {
				return true;
				break;
			}
		}
		return false;
	}	

	private function printParts($partsList){

		$partDict = $this->granules;
		$partList = $this->wordParts;
		// print_r($partList);
		#every word presents a root
		$reordWord=$partDict["root"];
		#each will be attached priority number
		#for performance, change space for time 
		$workedParts=array("super_script"=>"࿚".$partDict["super_script"],"sub_script"=>"࿙".$partDict["sub_script"],
			"wazhur"=>"࿘".$partDict["wazhur"],"prefix"=>"࿗".$partDict["prefix"],
			"vowel"=>"࿖".$partDict["vowel"],"post_suffix"=>"࿕".$partDict["post_suffix"],"suffix"=>"࿔".$partDict["suffix"]);
		foreach ($partsList as $part) {
			$reordWord.=$workedParts[$part];
			# code...
		}
		##appending the punctuations is neccesary as it applies
		return $reordWord.$this->puncts;

	}

	private function assignParts($partKeys,$partVals){
		for ($i = 0; $i<count($partKeys); $i++){
			$this->granules[$partKeys[$i]]=$partVals[$i];

		}
		##if super_script, change the root from sub to consnant
		$partDict = $this->granules;
		if ($partDict["super_script"] != "0") {
			//todo: check if this root exist
			$this->granules["root"]=$this->subToCons[$partDict["root"]];
		};

	}

	private function assignColParts($partKeys,$partVals){
		for ($i = 0; $i<count($partKeys); $i++){
			$this->granules[$partKeys[$i]]=$partVals[$i];

		}
		#this will collate the vowel along with wi 
		$this->granules[$partKeys[$i-1]].=$partVals[$i];
				##if super_script, change the root from sub to consnant
		$partDict = $this->granules;
		if ($partDict["super_script"] != "0") {
			//todo: check if this root exist
			$this->granules["root"]=$this->subToCons[$partDict["root"]];
		};
	}

}

?>