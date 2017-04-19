<html>
<body>
<h2>Enter Tibetan text and insert it into the ordered list.</h2>
<form method="post" action="">
<input type="text" name="text">
<input type="submit" value="Insert">
<input type="submit" name="reset" value="Reset my list">

</form>
<p><a href="http://sort.000webhostapp.com/demo.html">དཔེ་བརྗོད་ལ་ལྟ་བ Check demo</a></p>

<?php
##this happens when a user starts session
$tmpfname = sys_get_temp_dir()."/BODinsertList.ini";

if (isset($_POST["reset"])){
        resetList($tmpfname);
        exit();
}


include_once 'WordGranules.php';

if (isset($_POST["text"])) {
        $user_input=$_POST["text"];
}else{
        $user_input="";
}

if (file_exists($tmpfname)) {
     /*   $file_array = parse_ini_file($tmpfname);
        print_r($file_array);*/
        ##this happens when a new item is inserted.             
        $orderedList=array();
        $insertPos=-1;

        if (!$user_input=="") {
                
                #split key by word
                $wordArray=mb_split("་", $user_input);

                $insertVal="";
                foreach ($wordArray as $eachWord) {
                        $wordClass=new WordGranules();
                        $insertVal.= $wordClass->getOrderedParts($eachWord);
                }
                ##we need indexes attached for sorting multiple occurence, since the index counting is not known, put x to avoid key conflict.
                $insertArray[time(). "-". $user_input] =$insertVal;

                if (filesize($tmpfname) == 0){
                        ##write it in the tmp file
                        // echo "user input is good, file size is zero.<br>";
                        write_ini_file($insertArray, $tmpfname);
                        $orderedList=$insertArray;
                }elseif ($file_array = parse_ini_file($tmpfname)) {
                         ##let's go binary insert
                        // echo "file array size: ". count($file_array)."<br>";

                        $listAndPos=bi_insert($file_array,$insertArray,$insertVal);
                        $orderedList=$listAndPos["result"];
/*                        echo "orderedList in the above <br>";
                        print_r($orderedList);*/
                        $insertPos=$listAndPos["position"];
                        ##write it in the tmp file
                        write_ini_file($orderedList, $tmpfname);

                }else{
                        exit("user input was not inserted due to file handling error.");
                }

        }elseif($file_array = parse_ini_file($tmpfname)){
                $orderedList=$file_array;

        }else{
                exit("Both your input and current list are empty.");
        }



        ##this block will display the ordered list on form
        showInForm($orderedList,$insertPos);

}else{
        if (!$myfilehandler = fopen($tmpfname, "w")){
             exit("can't creat temp file");   
        } 
        fclose($myfilehandler);

}



function resetList($tmpfname){
        ##this happens when a user starts session      
        if (!$myfilehandler = fopen($tmpfname, "w")){
             exit("can't creat temp file");   
        } 
        fclose($myfilehandler);
        showInForm(array(),-1);
        echo "Now, your list has no element.<br>";
}

function bi_insert($file_array, $insertArray, $insertVal){
        $arr_size=count($file_array);
        $val_array=array_values($file_array);
        $insert_pos=bi_search($val_array,$insertVal,0,$arr_size-1);
        ##slice the original array at the position
        $forePart=array_slice($file_array, 0, $insert_pos);
        $hindPart=array_slice($file_array,$insert_pos);
        ##insert the new item and put the arrays back in order
        $arrayResult=array_merge($forePart,$insertArray);
        $arrayResult=array_merge($arrayResult,$hindPart);
        return array("result"=>$arrayResult,"position"=>$insert_pos);
}

function bi_search($val_array,$insertVal,$left,$right){
        #binary search to find the position of insert item
        if ($left > $right){
                return $right+1;
        }
        $middle = intval(($left+$right)/2);
        if ($val_array[$middle] == $insertVal){
                return $middle+1;
        }elseif ($val_array[$middle]>$insertVal) {
                return bi_search($val_array,$insertVal,$left,$middle-1);
        }else{
                return bi_search($val_array,$insertVal,$middle+1,$right);
        }
}

function write_ini_file($assoc_arr, $path) { 
    $content = ""; 
    // echo "write ini file <br>";
    foreach ($assoc_arr as $key=>$val) { 
        $content .= "$key = $val\n";    
    } 

    if (!$handle = fopen($path, 'w')) { 
        exit("failed to open the file."); 
    }

    $success = fwrite($handle, $content);
    fclose($handle); 

    return $success; 
}


function showInForm($orderedList,$position){
        ##triming all the ids in the heading part
        $finalList=array();
        // print_r($orderedList);
        foreach ($orderedList as $key => $value) {
                $finalList[]=mb_split("-", $key)[1];
        }

        $columns = 5;
        // echo "show in file is working <br>";

        for ($p=0; $p<count($finalList); $p++) {

                // Start of table or line?
                if ($p==0) { // Start of table
                        print "<table border=1><tr>";
                } elseif ($p%$columns == 0) { // Start of row
                        print "<tr>";
                }
                if ($p == $position) {
                        print "<td style='background-color: #00FF00;'>".htmlspecialchars($p." ".$finalList[$p])."</td>";
                }else{
                        print "<td>".htmlspecialchars($p." ".$finalList[$p])."</td>";
                }

                // print "<td>".htmlspecialchars($p." ".$finalList[$p])."</td>";

                // End of table or line?
                if (($p+1)%$columns == 0) { // End of row
                        print "</tr>";
                }
                if ($p==count($finalList)-1) { // End of table
                        $empty = $columns - (count($finalList)%$columns) ;
                        if ($empty != $columns) {
                                print "<td colspan=$empty>&nbsp;</td>";
                                }
                        print "</tr></table>";
                }
        }     
}

?>
</body>
</html>