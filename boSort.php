<html>
<body>
<h2>Enter Tibetan texts separated by ','(without single quote): </h2>
<form method="post" action="">
<input type="text" name="text">
<input type="submit", value="Sort">
</form>
<?php 
include_once 'WordGranules.php';
echo "<br>";
//echo "Enter Tibetan texts separated by ','(without single quote):<br>";
$user_input=$_POST["text"]; 
$input = mb_split(",", $user_input);
//$inputSize = count($input);

for ($i=0; $i< count($input); $i++) { 
	#split key by word
	$wordArray=mb_split("་", $input[$i]);

	$reordVal="";
	foreach ($wordArray as $eachWord) {
		$wordClass=new WordGranules();
		$reordVal.= $wordClass->getOrderedParts($eachWord);
	}
	##we need indexes attached for sorting multiple occurence of the same text
	$output[$i. "-". $input[$i]] =$reordVal;

}
echo "<br>";
echo "Before sorting ....";
print_r($output);
echo "<br>";

asort($output);

echo "<br>";
echo "after sorting ....";
print_r($output);
echo "<br>";
##this is for taking out the index attached on each sorted keys 
$sortedBo=array();
foreach ($output as $key => $value) {
	array_push($sortedBo, mb_split("-", $key)[1]);
}
echo "Final sorted Tibetan: <br>";
print_r($sortedBo);
?>


</body>
</html> 
