<?php

/* Simple numeric captcha plugin for Mara cms 2+
Copyright, IWR Consultancy, Mozilla Public License free software. 
Requires: core.php loaded, and active session cookie. 

To use, invoke the plugin with 
<php include(plugin('captcha'))"?>

Then do... 
$captcha = new captcha();
$captcha->set();

... and then obtain the captcha data from the session as: 

$captcha_data=gets("captcha");
$captcha_data["plain"] = plaintext challenge
$captcha_data[1] = first section of obfuscated challenge
$captcha_data[2] = second section of obfuscated challenge
$captcha_data[3] = third section of obfuscated challenge
$captcha_data["hash"] = hash of plaintext challenge

The obfuscated challenge has al ter edword spa cingto make robotic word recognition more difficult. 
It is supplied in three sections to permit display in separate table/div fields, if desired. 
The sections can be joined into a single line if preferred. 

Note that unlike many commercial captchas, nothing is written to the webpage by the plugin.
It's left up to you to format the returned data as you want it. A little more work, but more flexible. 

To test a response (usually on a new page instance unless Ajax is used) invoke the plugin and do:
<php include(plugin('captcha'))"?>
$captcha = new captcha();
$arewehuman=$captcha->validate();
-which should return 1 for human, 0 for robot, false for no captcha data. 

There is a provision for validating a response prior to form submission, 
in order to avoid the frustration of data being rejected through a user mistake.  
This operates by providing a fractional response to javascript, 
such that the full correct response is not exposed to the browser (or robot!) 
$captcha->key2js() sets a javascript variable, captcha_partial_key. 
The javascript function captcha_jsvalidate(user_reponse) can be used to check the user response against this, 
returning true if a match is found, returning false and popping a warning if the response is incorrect.  
*/

class captcha {

public function validate($response) {
  $cdata= gets("captcha");
  $response=md5(clientip().$response);
//  echo $cdata['hash']."|".$response;
  if (!is_array($cdata)) return false;
  if (strlen($cdata['hash'])==false) return false;
  if ($cdata['hash']==$response) return 1;
  return 0;    
}

public function cksum2js() {
  $cdata= gets("captcha");
  if (!is_array($cdata)) return false;
  if (strlen($cdata['hash'])==false) return false;
  $cksum=$cdata['cksum'];
  echo "<script>captcha_cksum='$cksum';</script>";      
}

public function set($cname="captcha", $rlo=100, $rhi=100000) {
if ($rhi>1000000) $rhi=1000000; 
$num=rand($rlo,$rhi);
if (($rlo<100)&&(rand(0,9)>5)&&($num>100)) $num=$num/100;
$number=$this->convertNumber($num);
$number=str_replace("point",",point",$number);
$anumber=explode(",",$number);
$spcc[" "]="&nbsp;"." " ;   
for($ct=1;$ct<5;$ct++){
  $thischr=chr(rand(97,117));
  $spcc[$thischr]=$thischr." ";
}
$spcc["-"]=" - " ;   
$nospc=chr(rand(97,117));
$spcc[$nospc." "]=$nospc;
$captcha_data["plain"]="";
$captcha_data[1]="";
$captcha_data[2]="";
$captcha_data[3]="";
$captcha_data["hash"]=md5(clientip().$num);
$cksum=substr(hash("sha256",$num),1,2);
$captcha_data["cksum"]=$cksum;
$ct=0;
foreach($anumber as $numsection){
     $ct++;
     $captcha_data["plain"].=$numsection;
     $captcha_data[$ct]=strtr($numsection,$spcc);
}
sets($cname, $captcha_data);
return; 
}


private function convertNumber($num)
{
   @list($num, $dec) = explode(".", $num);

   $output = "";

   if($num{0} == "-")
   {
      $output = "negative ";
      $num = ltrim($num, "-");
   }
   else if($num{0} == "+")
   {
      $output = "positive ";
      $num = ltrim($num, "+");
   }
   
   if($num{0} == "0")
   {
      $output .= "zero";
   }
   else
   {
      $num = str_pad($num, 36, "0", STR_PAD_LEFT);
      $group = rtrim(chunk_split($num, 3, " "), " ");
      $groups = explode(" ", $group);

      $groups2 = array();
      foreach($groups as $g) $groups2[] = $this->convertThreeDigit($g{0}, $g{1}, $g{2});

      for($z = 0; $z < count($groups2); $z++)
      {
         if($groups2[$z] != "")
         {
            $output .= $groups2[$z].$this->convertGroup(11 - $z).($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1))
             && $groups2[11] != '' && $groups[11]{0} == '0' ? " and " : ", ");
         }
      }

      $output = rtrim($output, ", ");
   }

   if($dec > 0)
   {
      $output .= " point";
      for($i = 0; $i < strlen($dec); $i++) $output .= " ".$this->convertDigit($dec{$i});
   }

   return $output;
}

private function convertGroup($index)
{
   switch($index)
   {
      case 11: return " decillion";
      case 10: return " nonillion";
      case 9: return " octillion";
      case 8: return " septillion";
      case 7: return " sextillion";
      case 6: return " quintrillion";
      case 5: return " quadrillion";
      case 4: return " trillion";
      case 3: return " billion";
      case 2: return " million";
      case 1: return " thousand";
      case 0: return "";
   }
}

private function convertThreeDigit($dig1, $dig2, $dig3)
{
   $output = "";

   if($dig1 == "0" && $dig2 == "0" && $dig3 == "0") return "";

   if($dig1 != "0")
   {
      $output .= $this->convertDigit($dig1)." hundred";
      if($dig2 != "0" || $dig3 != "0") $output .= " and ";
   }

   if($dig2 != "0") $output .= $this->convertTwoDigit($dig2, $dig3);
   else if($dig3 != "0") $output .= $this->convertDigit($dig3);

   return $output;
}

private function convertTwoDigit($dig1, $dig2)
{
   if($dig2 == "0")
   {
      switch($dig1)
      {
         case "1": return "ten";
         case "2": return "twenty";
         case "3": return "thirty";
         case "4": return "forty";
         case "5": return "fifty";
         case "6": return "sixty";
         case "7": return "seventy";
         case "8": return "eighty";
         case "9": return "ninety";
      }
   }
   else if($dig1 == "1")
   {
      switch($dig2)
      {
         case "1": return "eleven";
         case "2": return "twelve";
         case "3": return "thirteen";
         case "4": return "fourteen";
         case "5": return "fifteen";
         case "6": return "sixteen";
         case "7": return "seventeen";
         case "8": return "eighteen";
         case "9": return "nineteen";
      }
   }
   else
   {
      $temp = $this->convertDigit($dig2);
      switch($dig1)
      {
         case "2": return "twenty-$temp";
         case "3": return "thirty-$temp";
         case "4": return "forty-$temp";
         case "5": return "fifty-$temp";
         case "6": return "sixty-$temp";
         case "7": return "seventy-$temp";
         case "8": return "eighty-$temp";
         case "9": return "ninety-$temp";
      }
   }
}
      
private function convertDigit($digit)
{
   switch($digit)
   {
      case "0": return "zero";
      case "1": return "one";
      case "2": return "two";
      case "3": return "three";
      case "4": return "four";
      case "5": return "five";
      case "6": return "six";
      case "7": return "seven";
      case "8": return "eight";
      case "9": return "nine";
   }
}

}

?>
