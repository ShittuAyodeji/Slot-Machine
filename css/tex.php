<?php
include "../../database/connect.php";
include "../../includes/functions.php";
include "../../includes/session.php";

mysqli_set_charset($dbc,"utf8mb4");

    function duplicate_story($title){
		$conjuctions=array('and','or','but','if','to','when','at','also','then','during','once','after','even','before','until','so','because','therefore','then','since','still','yet','both','for','nor');
	$date=date('Y-m-d');
	$duration="- 1days";
	$previous_date=date('Y-m-d',(strtotime("$duration")));
        $c=0;
    while(count($conjuctions)<$c){
        if(in_array($conjuctions,$conjuctions[$c])){
          $title=str_replace($conjuctions[$c],"",$title);  
          $title=str_replace("  "," ",$title);  
        }
    }
    $normal_title=secure_input(inner_trim($title));
	$compare_title=str_replace(" ","|",$title);
	$title_chunck=explode("|",$compare_title);
	$title_count=count($title_chunck);
	$title_clause="";
	$part_title="";
	
	$t=0;
	$j=0;
	$s=1;
	while($j<$title_count){
		$part_title=$title_chunck[$j];  
		$title_clause.=" title LIKE '%".$part_title."%' AND "; 
		$j++;
	}
	$title_clause=rtrim($title_clause," AND ");
	$token="";
	$sql_matched=$dbc->prepare("SELECT * FROM stories WHERE date_posted='$date' OR date_posted='$previous_date' AND (".$title_clause.")  ORDER BY id DESC LIMIT 20");
	//echo "SELECT * FROM stories WHERE ".$title_clause."  ORDER BY id DESC LIMIT 20";
	$sql_matched->execute();
	$result_matched=$sql_matched->get_result();
 	$matched_rows=$result_matched->num_rows;
	$match_count=0;
	$token="";
	$score=0;
	if($matched_rows>0){
	while($match=$result_matched->fetch_assoc()){
		$matched=reverse_secure_input($match['title']);
		$matched_array=explode(" ",$matched);
		$title_array=explode(" ",$title);
		$title_len=count($title_array);
		$matched_len=count($matched_array);
		if($title_len>$matched_len){
		$similarity_score=similarity(strtolower($matched),strtolower(reverse_secure_input($title)));
		}else{
		$similarity_score=similarity(strtolower(reverse_secure_input($title)),strtolower($matched));    
		}
		if($similarity_score>=50){
		  if($similarity_score>$score){
			  $token=$match['token'];
			  $score=$similarity_score;
		  }
		}
	}
 }
 return $token;
    }