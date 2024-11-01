<?php
/*

Plugin name: TubEntertain

Version: 2.0

Text Domain: tub

Description: A simple shortcode to embed YouTube&trade; playlists or handpicked videos in a single YouTube player. This plugin adds the shortcode tubentertain so you can easily embed  YouTube, playlist defined by-playlist id (PL in front of the playlist id)-comma seperated video ids list-YouTube channel uploads-search query

Text Domain: tub

Author: Ade Owolabi-TubEntertain

Author URI: http://tubentertain.com

Plugin URI: http://tubentertain.com/t/

=====================================Work Start Here==================================================

*/ 


if (!defined('ABSPATH')) {
    exit;
}

//header("Access-Control-Allow-Origin: *");

//header("Content-Type: application/json; charset=UTF-8");

//=============

global $wpdb;

global $TubentertainVersion;

$TubentertainVersion = '2.0';

//CofigurationPrefix

global $TubConfigTable;

$TubConfigTable = $wpdb->prefix . '_tubconfig';

//UserPrefix

global $TubUserTable;

$TubUserTable = $wpdb->prefix . '_tubuser';

//FavoritePrefix

global $TubFavoriteTable;

$TubFavoriteTable = $wpdb->prefix . '_tubfavorite';

//ProVideoPrefix

global $TubProVideoTable;

$TubProVideoTable = $wpdb->prefix . '_tubprovideo';

//TubTwitterApiPrefix

global $TubTwitterApiTable;

$TubTwitterApiTable = $wpdb->prefix . '_tubtwitterApi';

//=====VVV=======Start Class Here=========VVV=================

class TubEntertain 

    {		 

function TubEntertainInstall()

{

global $wpdb;

global $TubentertainVersion; 

$charset_collate = $wpdb->get_charset_collate();

//===============================================VVVV

//======Create ConfigurationTable

global $TubConfigTable;

//          

if($wpdb->get_var("SHOW TABLES LIKE '$TubConfigTable'") != $TubConfigTable) {

$TubConfigSqlCreate = "CREATE TABLE $TubConfigTable (

TubId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 

TubChannelId VARCHAR(1000) NOT NULL,

TubApiKey TEXT NOT NULL,

TubStreamId VARCHAR(1000) NOT NULL,

TubOwnerId VARCHAR(1000) NOT NULL

)";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//

dbDelta( $TubConfigSqlCreate);

}

//======Create UserTable

global $TubUserTable;

//

if($wpdb->get_var("SHOW TABLES LIKE '$TubUserTable'") != $TubUserTable) {

$TubUserSqlCreate = "CREATE TABLE $TubUserTable (

TubUserId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 

TubUserName VARCHAR(10) NOT NULL,

TubUserPass VARCHAR(10) NOT NULL,

TubUserEmail TEXT NOT NULL,

TubUserOnline VARCHAR(10) NOT NULL,

TubUserIp TEXT NOT NULL

)";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//

dbDelta( $TubUserSqlCreate);

//

}

//======Create FavoriteTable

global $TubFavoriteTable;

//

if($wpdb->get_var("SHOW TABLES LIKE '$TubFavoriteTable'") != $TubFavoriteTable) {

$TubFavoriteSqlCreate = "CREATE TABLE $TubFavoriteTable (

TubFavId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 

TubVideoId VARCHAR(1000) NOT NULL,

TubFavOwnerId VARCHAR(1000) NOT NULL

)";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//

dbDelta( $TubFavoriteSqlCreate);

//

}

//======Create TubProVideoTable

global $TubProVideoTable;

//

if($wpdb->get_var("SHOW TABLES LIKE '$TubProVideoTable'") != $TubProVideoTable) {

$TubProVideoSqlCreate = "CREATE TABLE $TubProVideoTable (

TubProVideoId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 

TubChannelId VARCHAR(1000) NOT NULL,

TubProVideoUrl TEXT NOT NULL

)";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//

dbDelta( $TubProVideoSqlCreate);

}

######################====#################################

//======Create TubTwitterApiTable

global $TubTwitterApiTable;

//

if($wpdb->get_var("SHOW TABLES LIKE '$TubTwitterApiTable'") != $TubTwitterApiTable) {

$TubTwitterApiSqlCreate = "CREATE TABLE $TubTwitterApiTable (

TubTwitterApiId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,

TwitterUserName VARCHAR(1000) NOT NULL,

TwitterConsumerKey TEXT NOT NULL,

TwitterConsumerSecret TEXT NOT NULL,

TwitterAccessToken TEXT NOT NULL,

TwitterAccessTokenSecret TEXT NOT NULL

)";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//

dbDelta( $TubTwitterApiSqlCreate);

}

######################====#################################

add_option( 'TubentertainVersion', $TubentertainVersion );

//

}//End of TVschedulerInstall()

//============================================================================================

}//==============Close Tubentertain Class

//usage

$TubentertainInstallObj=new Tubentertain();

register_activation_hook( __FILE__, array($TubentertainInstallObj,'TubEntertainInstall') );

//==================Check

function tubentertain_update_db_check() {

    global $TubentertainVersion;

    if (get_site_option('TubentertainVersion') != $TubentertainVersion) {

        $TubentertainInstallObj->TubEntertainInstall();

    }

}

add_action('plugins_loaded', 'tubentertain_update_db_check');

//===============================================

//class TubEntertainPage                        #

//===============================================

class TubEntertainPage

{ 

private $querywpdb;

public function __construct()

{

//Inject Prevention if we choose to use form submission for our MiniBlog

//magic quotes logic 

if (get_magic_quotes_gpc())

{

function stripslashes_deep($value)

{

$value = is_array($value) ?

array_map('stripslashes_deep', $value) :

stripslashes($value);

return $value;

}

$_POST = array_map('stripslashes_deep', $_POST);

$_GET = array_map('stripslashes_deep', $_GET);

$_COOKIE = array_map('stripslashes_deep', $_COOKIE);

$_REQUEST = array_map('stripslashes_deep', $_REQUEST);

}//magic quotes logic

}//construct

//======Table (1)===============

public static function TubSaveConfig(){

global $wpdb;

global $TubConfigTable;

$channelId = sanitize_text_field($_POST['channelId']); //sanitize the input

$apiKeyData = sanitize_text_field($_POST['apiKeyData']); 

$StreamVideoId= sanitize_text_field($_POST['StreamVideoId']); 

//Generate Random Id

$rand6 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 4);

$randnum6 = substr(str_shuffle("1234567890"), 0, 4);

$randmix = substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz"), 0, 4);

$TubOwnerId= strtoupper($rand6."-".$randnum6."-".$randmix);

//Validate Data

if($channelId==""){return new WP_Error('reading_error', 'Enter valid  YouTube Channel Id'); }

else if($apiKeyData==""){return new WP_Error('reading_error', 'Enter valid Api Key'); }

else if($StreamVideoId==""){return new WP_Error('reading_error', 'Enter valid YouTube Live-Stream VideoId or other Valid VideoId'); }

$sql = "SELECT * FROM ".$TubConfigTable." WHERE TubChannelId ='$channelId'";

if($query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! Same Channel Id  Already Added ');

}

//if everything is okay

elseif(!$querywpdb=$wpdb->insert( $TubConfigTable, array( 'TubId' => NULL, 'TubChannelId' => $channelId, 'TubApiKey' => $apiKeyData, 'TubStreamId' => $StreamVideoId, 'TubOwnerId' =>$TubOwnerId) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be save to database');	

}

return "Successful Well Done!";

//End of Save Data

}

//End of Save Data Class

//======Table (1B)===============

//=======TubGetConfigData=======>>

public static function TubGetConfigData($level){

global $wpdb;

global $TubConfigTable;

$TubId=sanitize_text_field($_POST['TubId']);

if($level==1){

$sqlTubId = "SELECT MAX(cast(TubId as unsigned)) FROM ".$TubConfigTable;

$TubIdQuery=$wpdb->get_var($sqlTubId);	

$TubId=$TubIdQuery;

}

$sql = "SELECT * FROM ".$TubConfigTable." WHERE TubId ='$TubId'";

if(!$querywpdb=$wpdb->get_results($sql,  ARRAY_A ))

{

return new WP_Error('error',  'Error! No Data Found Add Record ');

}

//===

return $querywpdb;

//End of TubGetConfigData

}

//End of TubGetConfigData Class

//======Table (1C)===============

//=======TubGetConfigData=======>>

public static function TubGetAllConfigData(){

global $wpdb;

global $TubConfigTable;

$sql = "SELECT * FROM ".$TubConfigTable;

if(!$querywpdb=$wpdb->get_results($sql,  ARRAY_A ))

{

return new WP_Error('error',  'Error! No Data Found  ');

}

//===

return $querywpdb;

//End of TubGetAllConfigData

}

//End of TubGetAllConfigData

//======Table (1D)===============

public static function TubUpdateConfig(){

global $wpdb;

global $TubConfigTable;

$channelId = sanitize_text_field($_POST['channelId']); //sanitize the input

$apiKeyData = sanitize_text_field($_POST['apiKeyData']); 

$StreamVideoId= sanitize_text_field($_POST['StreamVideoId']); 

$TubId=sanitize_text_field($_POST['TubId']);

$sql = "SELECT * FROM ".$TubConfigTable." WHERE TubChannelId ='$channelId'";

$sqlArray= array('TubChannelId' => $channelId, 'TubApiKey' => $apiKeyData, 'TubStreamId' => $StreamVideoId);

$sqlWhere=array('TubId'=>$TubId);

$SetIs=array('%s','%s','%s','%s');

//Validate Data

if($channelId==""){return new WP_Error('reading_error', 'Enter valid  YouTube Channel Id'); }

else if($apiKeyData==""){return new WP_Error('reading_error', 'Enter valid Api Key'); }

else if($StreamVideoId==""){return new WP_Error('reading_error', 'Enter valid YouTube Live-Stream VideoId or other Valid VideoId'); }

else if($TubId==""){return new WP_Error('reading_error', 'Please Select Channel To Update !'); }

elseif(!$query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! No such record found to be update ');

}

//if everything is okay

//===========================

if(!$query=$wpdb->update($TubConfigTable,$sqlArray,$sqlWhere,$SetIs,array( '%d' ) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be save to database');	

}

return "Successful Updated Well Done!";

//End of uPDATE Data

}

//End of UPDATE Data Class

//======Table (1E)===============

public static function TubDeletConfigData(){

global $wpdb;

global $TubConfigTable;

$TubId=sanitize_text_field($_POST['TubId']);

$sql = "SELECT TubOwnerId FROM ".$TubConfigTable." WHERE TubId ='$TubId'";

if(!$query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! No such record found to be deleted ');

}

elseif($query=$wpdb->get_results($sql , ARRAY_A))

{

$TubOwnerId=$query['TubOwnerId'];

//=========Show delected refrence==================

if(!$query=$wpdb->delete($TubConfigTable, array( 'TubId' => $TubId ), array( '%d' ) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be deleted from database');	

}

}

//if everything is okay

return "Data   ".$TubOwnerId. "Successful deleted !";

//End of uPDATE Data

}

//End of UPDATE Data Class

//======Table (1F)===============

public static function TubTwitterConfig(){

global $wpdb;

global $TubTwitterApiTable;

$TwitterUserName = sanitize_text_field($_POST['TwitterUsername']);

$TwitterConsumerKey = sanitize_text_field($_POST['ConsumerKey']); //sanitize the input

$TwitterConsumerSecret = sanitize_text_field($_POST['ConsumerSecret']); 

$TwitterAccessToken= sanitize_text_field($_POST['AccessToken']); 

$TwitterAccessTokenSecret= sanitize_text_field($_POST['AccessTokenSecret']); 

//Validate Data

if($TwitterUserName==""){return new WP_Error('reading_error', 'Enter valid  Twitter UserName'); }

else if($TwitterConsumerKey==""){return new WP_Error('reading_error', 'Enter valid  Twitter ConsumerKey'); }

else if($TwitterConsumerSecret==""){return new WP_Error('reading_error', 'Enter valid Twitter Consumer Secret'); }

else if($TwitterAccessToken==""){return new WP_Error('reading_error', 'Enter valid Twitter Access Token'); }

else if($TwitterAccessTokenSecret==""){return new WP_Error('reading_error', 'Enter valid Twitter Access Token Secret'); }



$sql = "SELECT * FROM ".$TubTwitterApiTable." WHERE TwitterConsumerKey ='$TwitterConsumerKey'";

if($query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! Twitter Api Already Set You can Update  ');

}

//if everything is okay

elseif(!$querywpdb=$wpdb->insert( $TubTwitterApiTable, array( 'TubTwitterApiId' => NULL, 'TwitterUserName' => $TwitterUserName, 'TwitterConsumerKey' => $TwitterConsumerKey, 'TwitterConsumerSecret' => $TwitterConsumerSecret, 'TwitterAccessToken' => $TwitterAccessToken, 'TwitterAccessTokenSecret' =>$TwitterAccessTokenSecret) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be save to database');	

}

return "Successful Well Done!";

//End of TubTwitterConfig Data

}

//End of TubTwitterConfig Data Class

//======Table (1G)===============

public static function TubTwitterUpdateConfig(){

global $wpdb;

global $TubTwitterApiTable;



$TubTwitterApiId = sanitize_text_field($_POST['TwitterApiId']);

$TwitterUserName = sanitize_text_field($_POST['TwitterUsername']);

$TwitterConsumerKey = sanitize_text_field($_POST['ConsumerKey']); //sanitize the input

$TwitterConsumerSecret = sanitize_text_field($_POST['ConsumerSecret']); 

$TwitterAccessToken= sanitize_text_field($_POST['AccessToken']); 

$TwitterAccessTokenSecret= sanitize_text_field($_POST['AccessTokenSecret']); 



$sql = "SELECT * FROM ".$TubTwitterApiTable." WHERE TubTwitterApiId ='$TubTwitterApiId'";

$sqlArray= array( 'TubTwitterApiId' => $TubTwitterApiId, 'TwitterUserName' => $TwitterUserName, 'TwitterConsumerKey' => $TwitterConsumerKey, 'TwitterConsumerSecret' => $TwitterConsumerSecret, 'TwitterAccessToken' => $TwitterAccessToken, 'TwitterAccessTokenSecret' =>$TwitterAccessTokenSecret);

$sqlWhere=array('TubTwitterApiId'=>$TubTwitterApiId);

$SetIs=array('%s','%s','%s','%s');

//Validate Data

if($TwitterUserName==""){return new WP_Error('reading_error', 'Enter valid  Twitter UserName'); }

else if($TwitterConsumerKey==""){return new WP_Error('reading_error', 'Enter valid  Twitter ConsumerKey'); }

else if($TwitterConsumerSecret==""){return new WP_Error('reading_error', 'Enter valid Twitter Consumer Secret'); }

else if($TwitterAccessToken==""){return new WP_Error('reading_error', 'Enter valid Twitter Access Token'); }

else if($TwitterAccessTokenSecret==""){return new WP_Error('reading_error', 'Enter valid Twitter Access Token Secret'); }



elseif(!$query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! No such record found to be update ');

}

//if everything is okay

//===========================

if(!$query=$wpdb->update($TubTwitterApiTable,$sqlArray,$sqlWhere,$SetIs,array( '%d' ) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be save to database');	

}

return "Successful Updated Well Done!";

//End of TubTwitter uPDATE Data

}

//End of TubTwitter UPDATE Data Class

//======Table (1C)===============

//=======TubGetConfigData=======>>

public static function TubTwitterGetAllConfigData(){

global $wpdb;

global $TubTwitterApiTable;

$sql = "SELECT * FROM ".$TubTwitterApiTable;

if(!$querywpdb=$wpdb->get_results($sql,  ARRAY_A ))

{

return new WP_Error('error',  'Error! Twitter Api Needs Configuration ');

}

//===

return $querywpdb;

//End of TubTwitterGetAllConfigData

}

//End of TubTwitterGetAllConfigData

//======Table (2)===============

//=======TubGetProVideo=======>>

public function TubGetProVideo(){

	return "To Be Completed In Version 2.1";

	

//End of TubGetProVideo

}

//End of TubGetProVideo Class

//======Table (2B)===============

//=======TubSaveProVideo=======>>

public  function TubSaveProVideo(){

global $wpdb;

global $TubProVideoTable;

$ProVideoUrl = sanitize_text_field($_POST['ProVideo']); //sanitize the input

//Generate Random Id

$rand6 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 4);

$randnum6 = substr(str_shuffle("1234567890"), 0, 4);

$randmix = substr(str_shuffle("1234567890abcdefghijklmnopqrstuvwxyz"), 0, 4);

$TubProVideoId= strtoupper($rand6."-".$randnum6."-".$randmix);

//Validate Data

if($ProVideoUrl==""){return new WP_Error('reading_error', 'Enter valid  YouTube Channel Id'); }

$sql = "SELECT * FROM ".$TubProVideoTable." WHERE TubProVideoUrl ='$ProVideoUrl'";

if($query=$wpdb->get_results($sql , ARRAY_A))

{

return new WP_Error('error', 'Error! Same Url  Already Added ');

}

//if everything is okay

elseif(!$query=$wpdb->insert( $TubProVideoTable, array( 'TubProVideoId' => NULL, 'TubChannelId' => $TubProVideoId, 'TubProVideoUrl' =>$ProVideoUrl) ))

{

//if everything not okay	

return new WP_Error('error', 'Error! Data Can not be save to database');	

}

return "Successful Video Url Well Done!";

//End of Video Url Save Data

	

//End of Video Url TubSaveProVideo

}

//End of Video Url TubSaveProVideo Class

//======Table (3)===============

//=======TubGetFavorite=======>>

public function TubGetFavorite(){

	return "To Be Completed In Version 2.1";	

//End of TubGetFavorite

}

//End of TubGetFavorite Class

//======Table (3B)===============

//=======TubSaveFavorite=======>>

public  function TubSaveFavorite(){

	return "To Be Completed In Version 2.1";	

//End of TubSaveFavorite

}

//End of TubSaveFavorite Class

//======Table (4)===============

//=======TubAddUser=======>>

public function TubAddUser(){

	return "To Be Completed In Version 2.1";	

//End of TubAddUser

}

//End of TubAddUser Class

//======Table (4B)===============

//=======TubGetUser=======>>

public  function TubGetUser(){

	return "To Be Completed In Version 2.1";	

//End of TubGetUser

}

//End of TubGetUser Class

public function SayUser(){	

global $wpdb;

if(!empty($_POST['GetUser'])){

$tuser=$wpdb->prefix."users";	

$whoUser=$_POST['GetUser']; 

$sql = "SELECT * FROM ".$tuser." WHERE user_login ='$whoUser'";	

if(!$query=$wpdb->get_results($sql , ARRAY_A)){

return new WP_Error('error', 'Error! No such User found');

}

return $query;

}//========

else

{

return new WP_Error('error', 'Select User To Add');

}

//$userData=array('UserName'=> $UniqueUser->user_login,'UserEmail'=> $UniqueUser->user_email );

}//function Say User

//======================================

public function PrintVideoData($videoId,$apiKey){

$leepVideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$videoId."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($leepVideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$leepVidoId = json_decode($return, true);

$leepTitle=$leepVidoId['items'][0]['snippet']['title'];

$videoId=$leepVidoId['items'][0]['snippet']['resourceId']['videoId'];

$dataArry=array("videoId" =>$videoId,"videoTitle" =>$leepTitle);

return $dataArry;

//End of PrintVideoData

}

//End of PrintVideoData Class

public static function PrintPlistTitle($playlistsId,$apiKey){

$playlistsData ="https://www.googleapis.com/youtube/v3/playlists?part=id,snippet,contentDetails,status&id=".$playlistsId."&key=".$apiKey;

$curl = curl_init($playlistsData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$playlistsIdItems= json_decode($return, true);

//===============================

if($playlistsIdItems['items']){

$Thumb=$playlistsIdItems['items'][0]["snippet"]["thumbnails"]["high"]["url"];

$dTitle=$playlistsIdItems['items'][0]['snippet']['title'];

$description=$playlistsIdItems['items'][0]['snippet']['description']; 

$itemCount =$playlistsIdItems['items'][0]['contentDetails']['itemCount'];

//===================================================================

if($description){

$description=str_replace(array('’','"',  '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), '', $description);

$description= preg_replace('/(https?:\/\/[^\s"<>]+)/','<a  href="#" title="'.$dTitle.'" target="_blank"><i class="fa fa-external-link"></i></a>', $description);

}else{$description="Description Not Available!";}

//

}

//===============================================

//======Send First Vdeo Id===================

$ChannelPlayListItems= "https://www.googleapis.com/youtube/v3/playlistItems?part=id,snippet,contentDetails,status&pageToken=&maxResults=30&playlistId=".$playlistsId."&key=".$apiKey;

$curl = curl_init($ChannelPlayListItems);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$PlayListData = json_decode($return, true);

if(!$PlayListData)

{

echo "Error3-";

}

else

{

$featureVideo=$PlayListData['items'][0]['snippet']['resourceId']['videoId'];

}

//===============================================

$PlistT=array("featureVideo" =>$featureVideo,"Thumb" =>$Thumb,"dTitle" =>$dTitle,"itemCount" =>$itemCount,"description" =>$description);

return $PlistT;

//

}///=============

//

public static function CovtFrontNumToKilo($n) {

// first strip any formatting;

 $n = (0+str_replace(",","",$n));

// is this a number?

if(!is_numeric($n)) return false;        

        // now filter it;

if($n>1000000000000) return round(($n/1000000000000),3).'T';

else if($n>1000000000) return round(($n/1000000000),3).'B';

else if($n>1000000) return round(($n/1000000),3).'M';

else if($n>1000) return round(($n/1000),3).'K';        

return number_format($n);

    }

//============CovtFrontNumToKilo class Ends=============

//

//=============================

public  function SiteStatViews($videoToCheck,$apiKey){

$VideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$videoToCheck."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($VideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$thenViews = json_decode($return, true);

if(!$thenViews['items']){

$thenViewsCount =  0;

$liveBroadcastContent=$thenViews['items'][0]["snippet"]['liveBroadcastContent'];

$statArray=array('alltime'=>$thenViewsCount,'ison'=>$liveBroadcastContent);

}

else{

$thenViewsCount =  $thenViews["items"][0]["statistics"]["viewCount"];

$liveBroadcastContent=$thenViews['items'][0]["snippet"]['liveBroadcastContent'];

$statArray=array('alltime'=> $this->CovtFrontNumToKilo($thenViewsCount),'ison'=>$liveBroadcastContent);

}

return $statArray;

}

//=======SiteSat class Ends Here=========

//===

public  function liveStats($videoToCheck,$apiKey){

$reSiteSat=$this->SiteStatViews($videoToCheck,$apiKey);	

$ison=$reSiteSat['ison'];

$satData ="https://www.youtube.com/live_stats?v=".$videoToCheck;

$curl = curl_init($satData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$ViewersNow= json_decode($return, true);

if($ison=="none"){

$arrStat = array("view" => "<span id=offline >OFFLINE Stream</span>", "alltime" => "AllTime Viewers: ".$reSiteSat['alltime'], "ison" => $ison);

$liveStats= json_encode($arrStat);

return $liveStats;

}

else{

$arrStat = array("view" => "Viewers Now: ". number_format($ViewersNow), "alltime" => "AllTime Viewers: ".$reSiteSat['alltime'], "ison" => $ison);

$liveStats= json_encode($arrStat);

return $liveStats;

}

//=============

}

//============Convert Youtube To MP4===========================

public function TubPlay($dvideo){  

$output = file_get_contents('http://www.youtube.com/get_video_info?&video_id='.$dvideo);

 // Parse data to eg.(&id=var)

 parse_str($output);

 //echo '<p><img src="'. $thumbnail_url .'" border="0" hspace="2" vspace="2"></p>';

 $my_title = $title;

 if(isset($url_encoded_fmt_stream_map)) {

    /* Now get the url_encoded_fmt_stream_map, and explode on comma */

    $my_formats_array = explode(',',$url_encoded_fmt_stream_map);

 } 

 elseif (count($my_formats_array) == 0) {

    return "Error";

    exit;

 }

/* create an array of available download formats */

 $avail_formats[] = '';

 $i = 0;

 $ipbits = $ip = $itag = $sig = $quality = '';

 $expire = time(); 

 foreach($my_formats_array as $format) {

    parse_str($format);

    $avail_formats[$i]['itag'] = $itag;

    $avail_formats[$i]['quality'] = $quality;

    $type = explode(';',$type);

    $avail_formats[$i]['type'] = $type[0];

    $avail_formats[$i]['url'] = urldecode($url) . '&signature=' .$sig;

    parse_str(urldecode($url));

    $avail_formats[$i]['expire'] = date("G:i:s T", $expire);

    $avail_formats[$i]['ipbits'] = $ipbits;

    $avail_formats[$i]['ip'] = $ip;

    $i++;

 } 

 return $avail_formats;

//$arrSource = array("TubSrc" => $durl, "TubType" => $dtype);

//$CovertedVideo= json_encode($arrSource);

//return $CovertedVideo;

}// fUNCTION Tubplay

//=======================================

//

}//=======>>>TubEntertainPage ends here

//

//======================================

//========>>SetupPage

function SetupPage(){		

//===========================================

function jquery_TubEntertain() 

{

wp_enqueue_script("jquery");

}

add_action("wp_enqueue_scripts", "jquery_TubEntertain");

//=============

function TubEntertainScripts() {

wp_register_style('TubEntertainCss', plugins_url('css/tubplay.css', __FILE__));    

//================================================

wp_enqueue_style( 'TubEntertainCss' );

}

add_action("wp_enqueue_scripts", "TubEntertainScripts");

//==================

function tubAdminStylesNScripts() {

wp_register_style( 'TubEntertainAdminCss', plugins_url('css/tubadmin.css', __FILE__) );

wp_enqueue_style( 'TubEntertainAdminCss' );

}

//===================  

function TubEntertainAdminPage() { 

$tubAdminPage=add_menu_page( 'TubEntertain', 'TubEntertain', 'manage_options', 'TubEn','TubEntertainSettings', plugins_url( 'logo.jpg' , __FILE__), 13 );  

add_action( 'admin_print_styles-' . $tubAdminPage, 'tubAdminStylesNScripts' );

}

add_action('admin_menu', 'TubEntertainAdminPage');

//===============================

function TubAjaxJs()

{

wp_enqueue_script("TubEntertainJS",plugins_url( "js/wptubplay.js", __FILE__) ,array("jquery"),  false,  true);

wp_localize_script( "TubEntertainJS", "TubEntertainJSajax", array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}

add_action('wp_enqueue_scripts', 'TubAjaxJs');
//::#######:::::::::::

add_filter('wp_headers', 'Tub_nocache');

function Tub_nocache($headers){

    unset($headers['Cache-Control']);

    return $headers;

}

}//all engine loade---==>

SetupPage();

//============TubEntertainSettings=============== 

function TubEntertainSettings() {

$GetStart=new TubEntertainPage();	

//::::::::::::Monitor Start Here::::::::::

$output = '';

$error = '';

$channelId=$_POST['channelId'];

$apiKeyData=$_POST['apiKeyData'];

$StreamVideoId=$_POST['StreamVideoId'];

$TubId=$_POST['TubId'];

$UnikUser="";

$UnikUserEmail="";

$AllData=$GetStart->TubGetAllConfigData();

//=========================

if(!is_wp_error($AllData)){    

//=============

$channelId=$AllData[0]['TubChannelId'];

$apiKeyData=$AllData[0]['TubApiKey'];

$StreamVideoId=$AllData[0]['TubStreamId'];

$TubId=$AllData[0]['TubId'];

$output = "The System Is Working Fine";

$error ='';

}

elseif(is_wp_error($AllData)){

$error = $AllData->get_error_message();

$output = '';

}

//=======================

if(isset($_POST['createTub'])){    

$PostData = $GetStart->TubSaveConfig();

if(!is_wp_error($PostData)){

$output=$PostData;

$error ='';	

}

elseif(is_wp_error($PostData)){

$error = $PostData->get_error_message();

$output = '';

}

} 



elseif(isset($_POST['updateTub'])){    

$UpdateData = $GetStart->TubUpdateConfig();

if(!is_wp_error($UpdateData)){

$output=$UpdateData;

$error ='';	

}

elseif(is_wp_error($UpdateData)){

$error = $UpdateData->get_error_message();

$output = '';

}

} 

//=================

//=======================

elseif(isset($_POST['getTub']) && !$_POST['TubId']==""){  

$getTubData = $GetStart->TubGetConfigData(2);

if(!is_wp_error($getTubData)){    

//=============

$channelId=$getTubData[0]['TubChannelId'];

$apiKeyData=$getTubData[0]['TubApiKey'];

$StreamVideoId=$getTubData[0]['TubStreamId'];

$TubId=$getTubData[0]['TubId'];

$Ref=$getTubData[0]['TubOwnerId'];

$output = "Channel Reference <span class='ref'>" .$Ref." </span>Displayed";

$error ='';

}

elseif(is_wp_error($getTubData)){

$error = $getTubData->get_error_message();

$output = '';

}

}

//=======================

elseif(isset($_POST['deletTub']) && !$_POST['TubId']==""){  

$deletTubData = $GetStart->TubDeletConfigData();

if(!is_wp_error($deletTubData)){    

//=============

$output =$deletTubData;

$error ='';

}

elseif(is_wp_error($getTubData)){

$error = $getTubData->get_error_message();

$output = '';

}

}

elseif(isset($_POST['getTub']) || isset($_POST['deletTub']) && $_POST['TubId']==""){

$error = "Please Select Channel To Maintain";

$output = '';	

}

//=================

//===========

$output = $output;

//::::::::::::Monitor Ends Here::::::::::

?>

<div id="adPanel">

<?php

if(!empty($error)) {

?>

<div class="ReporterS showError"><?php echo $error; ?></div>

<?php 

} 

else

{

?>

<div class="ReporterS showDone"  ><?php echo $output; ?></div>

<?php 

}

?>

<form name="TubEntertain"  method="post">

<fieldset id="configBox">

<legend>Configure Your TubEntertain</legend>

<p class="shelConfig">

<label for="channelId">YouTube Channel Id</label>

<img src="<?php echo plugins_url(  'css/images/YouTube-icon.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="channelId" id="channelId" value="<?php echo $channelId ?>" />

</p>

<p class="shelConfig">

<label for="apiKeyData">Google Api Key</label>

<img src="<?php echo plugins_url(  'css/images/ApiKey.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="apiKeyData" id="apiKeyData" value="<?php echo $apiKeyData ?>" />

</p>

<p class="shelConfig">

<label for="apiKeyData">Live StreamVideoId (Feature VideoId)</label>

<img src="<?php echo plugins_url(  'css/images/tv.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="StreamVideoId" id="StreamVideoId" value="<?php echo $StreamVideoId ?>" />

<input type="hidden"   value="<?php echo $TubId?>" />

</p>

<p class="shelConfig">

<label for="thisData">Listed YouTube Channels</label>

<img src="<?php echo plugins_url(  'css/images/YouTube-icon.png', __FILE__)?>" title="Image" width="32"  />

<select type="text" class="Lput" name="TubId" id="TubId">

<option value="">--Select From <?php echo count($AllData);?> Listed Channels--</option>

<?php

foreach($AllData as $TubKey => $ChanneItems) {

?>

<option value="<?php echo $ChanneItems['TubId'];?>"><?php echo "Channel-". $ChanneItems['TubId'];?></option>

<?php 

} 

?>

</select>

</p>

<p class="submiters">

<?php $createTub=array('id' => 'createTub');submit_button('Add Channel', 'primary', 'createTub', false, $createTub);?>

<?php $getTub=array('id' => 'getTub');submit_button('Get Channel Data', 'primary', 'getTub', false, $getTub);?> 

<?php $updateTub=array('id' => 'EditTvSlot'); submit_button( 'Update Channel Data', 'primary', 'updateTub', false,$updateTub);?> 

<?php $deletTub=array('id' => 'deletTub'); submit_button( 'Delete Channel Data', 'primary', 'deletTub', false,$deletTub);?> 

</p>

</fieldset>

</form>

<form name="TubVideoAdvert"  method="post">

<fieldset id="VideoUrlBox">

<legend> HTML5 Video Advert (e.g. .mp4) (Coming Soon)</legend>

<p class="shelConfig">

<label for="VideoUrl">Add 30seconds Video Urls</label>

<img src="<?php echo plugins_url(  'css/images/YouTube-icon.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="VideoUrl" id="VideoUrl" value="<?php echo $VideoUrl ?>" />

</p>



<p >

<?php $TubVideoUrl=array('id' => 'TubVideoUrl');submit_button('Add Video Url', 'primary', 'TubVideoUrl', false, $TubVideoUrl);?>

</p>

</fieldset>

</form>

<form name="TubTwitterApi"  method="post">

<?php 

$TwitterUsername=""; 

$ConsumerKey=""; 

$ConsumerSecret=""; 

$AccessToken=""; 

$AccessTokenSecret="";

$TwitterApiId="";

//=============================

$TwitterGetAll=$GetStart->TubTwitterGetAllConfigData();

//==========================

//=========================

if(!is_wp_error($TwitterGetAll)){    

//=============

$TwitterUsername=$TwitterGetAll[0]['TwitterUserName'];

$ConsumerKey=$TwitterGetAll[0]['TwitterConsumerKey'];

$ConsumerSecret=$TwitterGetAll[0]['TwitterConsumerSecret'];

$AccessToken=$TwitterGetAll[0]['TwitterAccessToken'];

$AccessTokenSecret=$TwitterGetAll[0]['TwitterAccessTokenSecret'];

$TubTwitterApiIdt=$TwitterGetAll[0]['TubTwitterApiId'];

$outputTwitter = "Twitter System Is Working Fine";

$errorTwitter ='';

}

elseif(is_wp_error($TwitterGetAll)){

$errorTwitter = $TwitterGetAll->get_error_message();

$outputTwitter = '';

}

//===========================

if(isset($_POST['TubTwitterApi']) || isset($_POST['TubTwitterApiUpdate']))

{

$TwitterApiId=$_POST['TwitterApiId']; 

$TwitterUsername=$_POST['TwitterUsername']; 

$ConsumerKey=$_POST['ConsumerKey']; 

$ConsumerSecret=$_POST['ConsumerSecret'];  

$AccessToken=$_POST['AccessToken']; 

$AccessTokenSecret=$_POST['AccessTokenSecret']; 	

}

//===============================

if(isset($_POST['TubTwitterApiUpdate']))

{

$TubTwitterUpdateConfig=$GetStart->TubTwitterUpdateConfig();

if(!is_wp_error($TubTwitterUpdateConfig)){    

//=============

$outputTwitter = "Twitter System Is Working Fine";

$errorTwitter ='';

}

elseif(is_wp_error($TubTwitterUpdateConfig)){

$errorTwitter = $TubTwitterUpdateConfig->get_error_message();

$outputTwitter = '';

}	

}

//==================

if(isset($_POST['TubTwitterApi']))

{

$TubTwitterConfig=$GetStart->TubTwitterConfig();	

if(!is_wp_error($TubTwitterConfig)){    

//=============

$outputTwitter = "Twitter System Is Working Fine";

$errorTwitter ='';

}

elseif(is_wp_error($TubTwitterConfig)){

$errorTwitter = $TubTwitterConfig->get_error_message();

$outputTwitter = '';

}

}

//=========================

//=================

if(isset($_POST['TubUserData'])){ 

$GetUser=$GetStart->SayUser();

if(!is_wp_error($GetUser)){

$outputTwitter="User data must have been displayed";

$errorTwitter ='';	

$UnikUser=$GetUser[0]['user_login'];

$UnikUserEmail=$GetUser[0]['user_email'];

}

elseif(is_wp_error($GetUser)){

$errorTwitter = $GetUser->get_error_message();

$outputTwitter = '';

}

//isset

}

//=======================

?>



<fieldset  id="TwitterApiBox">



<div class="divideus">

<?php

if(!empty($errorTwitter)) {

?>

<div class="ReporterS showError"><?php echo $errorTwitter ?></div>

<?php 

} 

else

{

?>

<div class="ReporterS showDone"  ><?php echo $outputTwitter; ?></div>

<?php 

}

?>

<legend>Twitter Api Key For Sharing Tool</legend>

<p class="shelConfig">

<label for="TwitterUsername">Twitter Username</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="TwitterUsername" id="TwitterUsername" value="<?php echo $TwitterUsername ?>" />

</p>

<p class="shelConfig">

<label for="ConsumerKey">Twitter(API) Consumer Key</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="ConsumerKey" id="ConsumerKey" value="<?php echo $ConsumerKey ?>" />

</p>

<p class="shelConfig">

<label for="ConsumerSecret">Twitter(API) Consumer Secret</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="ConsumerSecret" id="ConsumerSecret" value="<?php echo $ConsumerSecret ?>" />

</p>

<p class="shelConfig">

<label for="AccessToken">Twitter(API) Twitter Access Token</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="AccessToken" id="AccessToken" value="<?php echo $AccessToken ?>" />

</p>



<p class="shelConfig">

<label for="AccessTokenSecret">Twitter(API) Twitter Access Token Secret</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="AccessTokenSecret" id="AccessTokenSecret" value="<?php echo $AccessTokenSecret ?>" />

</p>

<input type="hidden"  name="TwitterApiId" id="TwitterApiId" value="<?php echo $TwitterApiId ?>" />

<p >

<?php 

if($ConsumerSecret=="")

{

$TubTwitterApi=array('id' => 'TubTwitterApi');submit_button('Add Twitter Api Keys', 'primary', 'TubTwitterApi', false, $TubTwitterApi);

}

else

{

$TubTwitterApiUpdate=array('id' => 'TubTwitterApiUpdate');submit_button('Update Twitter Api Keys', 'primary', 'TubTwitterApiUpdate', false, $TubTwitterApiUpdate);

}

?>

</p>

</div>

<!--Add user-->

<div class="divideus">

<legend>Add Authorised User </legend>

<p class="shelConfig">

<label for="AutorUsername"> Username</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="AutorUsername" id="AutorUsername" value="<?php echo $UnikUser ?>" />

</p>

<p class="shelConfig">

<label for="AutorUserEmail">User Email</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<input type="text" class="Lput" name="AutorUserEmail" id="AutorUserEmail" value="<?php echo $UnikUserEmail ?>" />

</p>

<p class="shelConfig">

<label for="AutorUserPiority">User Piority</label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<select type="text" class="Lput" name="AutorUserPiority" id="AutorUserPiority">

<option value="">--Select User Piority--</option>

<?php

$piorities=array("Can Use Sharing Tool","Can Upload Video", "Full Authorisation", "None");

foreach($piorities as $TubKey => $pioritiesItems) {

?>

<option value="<?php echo $TubKey;?>"><?php echo $pioritiesItems;?></option>

<?php 

} 

?>

</select>

</p>

<p class="shelConfig">

<label for="GetUser">Select User To Update </label>

<img src="<?php echo plugins_url(  'css/images/twitter.png', __FILE__)?>" title="Image" width="32"  />

<select type="text" class="Lput" name="GetUser" id="GetUser">

<option value="">--Select User To Add--</option>

<?php

$blogusers = get_users( array( 'fields' => array( 'user_login' ) ) );

foreach($blogusers as $UserKey => $UserItems) {

?>

<option value="<?php echo esc_html( $UserItems->user_login );?>"><?php echo esc_html( $UserItems->user_login );?></option>

<?php 

} 

?>

</select>

</p>

<p class="submiters" >

<?php $AutorUser=array('id' => '$AutorUser');submit_button('Add User', 'primary', 'AutorUser', false, $AutorUser);?>

<?php $TubUserData=array('id' => '$TubUserData');submit_button('Get User', 'primary', 'TubUserData', false, $TubUserData);?>

<?php $UpdateUser=array('id' => '$UpdateUser');submit_button('Update User', 'primary', 'UpdateUser', false, $UpdateUser);?>

</p>

</div>

</fieldset>

</form>

<!-- Geting Started ! -->

<div id="forIntro">

<div id="weltext">

<legend>What Is It !</legend>

<strong>TubEntertain</strong> is a web base application, kind of a device such as a television or multimedia player that has access to contents residing at various social and multimedia content hosting platforms.

It enables you to double market your videos, both on YouTube, Twitter, FaceBook page, your site and almost anywhere on the World Wide Web. (<em>your parkage might not include sharing tool you will need to reqest this separate</em>).

Tubentertain is a responsive player in which it can be integrated with mobile applications. It has been cross platform compatible tested. <a href="http://tubentertain.com/t">See Demo Here</a>

TubEntertain is dynamically engineered using Youtube Api V3, and Twitter Api V1.1

</div>

<legend>Geting Started !</legend>

<p> <em><a href="http://tubentertain.com/documentation">See Documentation Here</a></em> </p> 

<p>For further customsation and additional functionalities contact <em>entertainer@tubentertain.com</em> In all communication please refrence <strong>#TubEntertain</strong>. 

Visit us at  <em><a href="http://tubentertain.com">TubEntertain.Com</a></em> </p>

</div>

</div>

<?php

}//:::::TubEntertainSettings:::::Admin Page End Here:::::::::::::::::::

//============SetupPage Class End===================

//========vvv===Switch on all engine vvvv===================

//========================THAT IS ALL ADMIN SECTION DONE AT THE TOP==============================//

if ( !function_exists("tubentertain") ){ 

//=======User View=================

function tubentertain_tub( $atts ) {

//Some Required Images

$imgRUrl=plugins_url(  'developios.jpg', __FILE__);

$img0Url=plugins_url(  'images/0bg.jpg', __FILE__);

$isRuningImg=plugins_url(  'images/', __FILE__);

$tubposter=plugins_url(  'images/tubentertain.jpg', __FILE__);

$tubequalizer=plugins_url(  'images/tub-equalizer.gif', __FILE__);

//============================================

	extract( shortcode_atts( array(

	    'vid' => '',			// int (single video Id or multiple video Ids separated with comma ',')

		'pid' => '',			// string (Youtube playlist ID) 

		'randompid' => '',	// string (randompid Youtube playlist ID separated with comma)

        'loop' => '1',			// [0|1]		

		'controls' => '0',		// [0|1|2]

		'autoplay' => '1',		// [0|1]

		'search' => '',		//string (Search Term or KeyWord)

		'livestream' => '0',	//int		

		'altstreamid' => ''		//string (youtube video ids that will play when offline)

		), $atts ) );

//========================================

$getClass=new TubEntertainPage();

$channelData=$getClass->TubGetConfigData(1);

$channelId=$channelData[0]['TubChannelId'];

$apiKeyData=$channelData[0]['TubApiKey'];

$StreamVideoId=$channelData[0]['TubStreamId'];

// check if Api is key valid

if(strlen($apiKeyData)>0){

$apiKey=$apiKeyData;

$runApi=true;

}

else

{

$runApi=false;

}

//this line is more than important to check if is referer video

if(isset($_GET['tsv'])){

$isShared="true";

}

else

{

$isShared="false";

}

//PUTTING ALL EFFORTS INTO USE=================

$controls=(int)$controls;

$loop=(int)$loop;

$livestream=(int)$livestream;

$pid=esc_attr($pid);

$vid=esc_attr($vid);

$randompid=esc_attr($randompid);

$search=esc_attr($search);

$altstreamid=esc_attr($altstreamid);

$standalone="NO";

//Clean Up any unwanted spaces

$vid=str_replace(" ","",$vid);

$pid = str_replace(" ","",$pid);

$randompid = str_replace(" ","",$randompid);

$livestreamid=str_replace(" ","",$livestreamid);

$altstreamid=str_replace(" ","",$altstreamid);

$displayMonitor="";

$ToDisplayPid="";

//

$ThePtitle="Recent Uploaded Video";

//Action To Take a message to Js

$ChioceAct="";

//rplay label

$replay="";

//single playlist Id

if(strlen($pid)>0 && $runApi!==false){

$ToDisplayPid=$pid;

$Ptitle=$getClass->PrintPlistTitle($ToDisplayPid,$apiKeyData);

$ThePtitle=$Ptitle['dTitle'];

}

//if is random playlist

elseif(strlen($randompid)>0 && $runApi!==false){

if(count(strlen($randompid)>1)){

$random_array=explode(",",$randompid);

$random_index=rand(0,count($random_array)-1);

if(isset($random_array[$random_index])){

$ToDisplayPid=$random_array[$random_index];

$Ptitle=$getClass->PrintPlistTitle($ToDisplayPid,$apiKeyData);

$ThePtitle=$Ptitle['dTitle'];

}//==============

}//countable is true

else

{

$ToDisplayPid=$randompid;

$Ptitle=$getClass->PrintPlistTitle($ToDisplayPid,$apiKeyData);

$ThePtitle=$Ptitle['dTitle'];

}

}

//========================================

//==SEARCH   

elseif(strlen($search)>0 && $runApi!==false){

$ChioceAct="SEARCH";

}

//========================================

//==live Stream

elseif(strlen($livestream)>0 && strlen($altstreamid)>0 && $runApi!==false)

{

$channelData=$getClass->TubGetConfigData(1);

$StreamVideoId=$channelData[0]['TubStreamId'];	

// - more than 1 video id

$standalone="YES";

$livestreamid=$StreamVideoId;

$listedVid=explode(",",$altstreamid);

$ALTVid=$listedVid[0];

if(!$ALTVid){$ALTVid=$altstreamid;}

array_shift($listedVid);

$setToArray=implode(",",$listedVid);

$displayMonitor="<div id='StandB' style='display:none' class='curveBoth'><span id='LiveSat' ></span><span id='AllSat' ></span></div>";

$titleMe=$livestreamid;

//initial if we live

$runSiteStats=$getClass->liveStats($livestreamid,$apiKeyData);

$runSite=json_decode($runSiteStats, true);

if($runSite['ison']=='live'){

$ison='true';

$firstVid=$livestreamid;

$replay="<span id='replay' class='leftAbs-0-top-mid' > Live</span>";	

}

else{

$replay="<span id='replay' class='leftAbs-0-top-mid' > Replay</span>";	

$ison='false';

$firstVid=$ALTVid;

}

}

//=======================

//Normal video Id

elseif(strlen($vid)>0 && $runApi!==false){
$standalone="INPOST";
// - more than 1 video id

$listedVid=explode(",",$vid);

$firstVid=$listedVid[0];

$titleMe=$firstVid;

array_shift($listedVid);

$setToArray=implode(",",$listedVid);

}

//=======================



//if($listedVid){

$vidTitle=$getClass->PrintVideoData($titleMe,$apiKeyData);

$vidTitle=$vidTitle['videoTitle'];

//}

//finally we can generate stand alone frame

$src="<iframe id='tubStandAlone' width='1920' height='1080' src='http://www.youtube.com/embed/{$firstVid}?playlist={$setToArray}&modestbranding=0&version=3&showinfo=0&theme=light&controls={$controls}&color=red&rel=0&start=0&loop={$loop}&iv_load_policy=3&fs=1&disablekb=0&autohide=1&autoplay={$autoplay}&vq=hd1080' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";

$url='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

//===========Start==========================

// init (standalone or Ondemand)



//==========PRINTING HTML ELEMENTS==========

if($standalone=="NO"){

$output = <<<EOT



<div id="TubPlayFrame"

data-equalizerSrc="$tubequalizer"



 data-runing-img="$isRuningImg"

 

data-share-now-title="" 



 data-share-played="$isShared" 



 data-list-id="$ToDisplayPid" 



 data-auto-play-next="true" 



 data-stand-alone="$standalone" 



 data-search-vid="$search" 



 data-action-vid="$ChioceAct"

 

data-pltitle-tub="$ThePtitle"



data-vid-now=""



data-mode-play-now="true"



data-advmode-now="ON"

  >

<div id="TubPlayplayer" class="TubPlayplayer">

<div id="toTubPlay" class="dark">

<span class="left  showMenu"><i class="fa fa-bars"></i></span>

<span class="right  showMenu"><i class="fa fa-search"></i></span>

</div>

<div id="TubBoard" class="dashBoard hideMe">

<div class="dashMe">

<form action="#">

<span id="searchwaiter" class="spining hideMe"><i class="fa fa-cog fa-spin"></i>working.....</span>

<input id=TubPlay placeholder="Type Here n Search" value=""/>

<button id=submitTubPlay>th</button>

</form>

</div>

<div class="dashMe">

<span class="LinkOut" title="Visit Our Website">

<i class="fa fa-cubes"></i>

<span>Home</span>

</span>

<span class="LinkOut" title="Subscribe To Our YouTube Channel">

<i class="fa fa-rss-square"></i>

<span>Subscribe</span></span>

<span title="Upload New Video" id="upload" class="LinkOut">

<i class="fa fa-upload"></i>

<span>Upload</span>

</span>

</div>

<div class="dashMe">

<span id="SlideIn1" title="Set Of PlayList" class="SlideIn curveBoth suade current">Playlist</span>

<span id="SlideIn2" title="More Channels" class="SlideIn curveBoth suade">Channels</span>

<span id="SlideIn3" title="Favorite List" class="SlideIn curveBoth suade">Favorites</span>

<div id="SlidePlaylist" >

<span id="TokenRun" class="hideMe totalAbs-0 curveBoth suade"><i class="fa fa-spinner fa-pulse"></i>please hold on.....</span>

<span id="TokenRun" class="totalAbs-0 curveBoth suade"><i class="fa fa-spinner fa-pulse"></i></span>

<a id="TubRightNP" title="Scroll Next" class="contRoler curveLeft rightAbs-0 "><i class="fa fa-angle-right"></i></a>

<a id="TubLeftNP" title="Scroll Previous" class="contRoler curveRight leftAbs-0"><i class="fa fa-angle-left"></i></a>

<div id="bulkyPlaylist" class="SlidersHolder">

<ul id="MorePlaylist">

<li class="waiters"><span class="spining"><i class="fa fa-cog fa-spin"></i> Getting data please hold on.....</span></li>

</ul>

</div>

</div>

<div id="SlideChannel" class="SlidersHolder hideMe">

<ul id="MoreChannel">

<li>

<span>

Function Not Included In This Version

</span>

</li>

</ul>

</div>

<div id="SlideFavoriteList" class="SlidersHolder hideMe">

<ul id="FavoriteList">

<li>

<span>

Function Not Included In This Version

</span>

</li>

</ul>

</div>

<div class="tokenNav">

<a data-token="BulkyNextToken" data-holder="MorePlaylist" title="Load next set of playlist" class="rightAbs  curveBoth moreData">next set<i class="fa fa-angle-double-right"></i></a>



<a data-token="BulkyPrevToken" data-holder="MorePlaylist" title="Load previous set of playlist" class="leftAbs  curveBoth moreData"> <i class="fa fa-angle-double-left"></i>prev set</a></div>

</div>

<a class="closePanel" data-hide-dat="TubBoard"> <i class="fa fa-chevron-up"></i></a>

</div>



<div id="playerHolder" class="player">

<div id="Tubshare" class="noshare">

<a id="sFavour" class="TubPlaySocial" title="Add To Favorite List" target="_blank"> <i  class="fa fa-heart"></i></a>

<a id="sTwitter" class="TubPlaySocial" title="share selected video on twitter" target="_blank">

<i class="fa fa-twitter"></i></a>

<a id="sGooglePlus" class="TubPlaySocial" title="share selected video on google" target="_blank">

<i class="fa fa-google-plus"></i></a>

<a id="sFaceBook" class="TubPlaySocial" title="share selected video on facebook" target="_blank">

<i class="fa fa-facebook-official"></i></a>

</div>

<span id="interwaiter" class="totalAbs-0 curveBoth hideMe"></span>

<div id="TubVideoFrame" >

<video autoplay id="TubVideo" name="media" poster="$tubposter"><source id="TubSource" src="" type=""></video>

</div>

<div id="TubFrame" class="hideMe">Chechk your browser</div>

<div id="rayLead" class="hideMe rightAbs-0 curveLeft"></div>

<!--Do controls Here -->

<div id="controls" class="controls nChildLays noshow">



<div id="PlayerRayContent">

  <div id="PlayerRay"></div>

  </div>

<div id="otherContents">

<span class="timeNow">00:00:00</span>

<span class="exitFull hideMe" title="exit fullscreen"><i class="fa fa-compress"></i></span>

<span class="full" title="fullscreen"><i class="fa fa-expand"></i></span>

<span class="rew" title="previous"><i class="fa fa-step-backward"></i></span>

<span id="play" class="play hideMe" title="play"><i class="fa fa-play"></i></span>

<span id="pause" class="pause" title="pause"><i class="fa fa-pause"></i></span>

<span class="fwd" title="next"><i class="fa fa-step-forward"></i></span>



<span class="volumeDown" >

<i id="mute" class="fa fa-volume-up" title="Mute"></i>

<i id="unmute" class="hideMe fa fa-volume-up" title="UnMute"></i>

<span id="Vslider">

<span id="VsliderVolume"></span>

</span>

</span>



<span class="duraTion">00:00:00</span>

</div>

</div>

<!--Do controls Here -->

</div>

<!--Do Player EndsHere -->

<div id="TopScroll" class="curveBoth midBelt dark">

<a id="autOff" title="Turn Off Auto Play Next" class="rightAbs-0  curveBoth">Auto<i class="fa fa-toggle-off"></i></a>

<a id="autOn" title="Turn On Auto Play Next" class="rightAbs-0 hideMe   curveBoth">Auto<i class="fa fa-toggle-on"></i></a>

<a id="List" title="Play Listed Video Mode" class="leftAbs-0  curveBoth hideMe">List<i class="fa fa-dedent"></i></a>

<a id="Live" title="Live View Mode" class="leftAbs-0   curveBoth">Live<i class="fa fa-wifi"></i></a>

<div id="marqueeHolder">

<div id="MarqueeTop" class="marquee suade curveBoth">

Welcome, This is Tubentertain A Dynamic Player 

For Live Stream And YouTube Videos

<span class="name">..Find Out More At www.tubentertain.com</span>

</span>

</div>

</div>

</div>

<div id="Listhoder">

<div id="TubPopContent" class="suade TubPopContent  hideMe curveBoth">

<div id="closPop" class="closeMe"><i class="fa fa-angle-double-left"></i></div>

<div  class="PopContent">

<span class="isRuning"><i class="fa fa-cog fa-spin"></i>....working</span>

</div>

</div>

<a id="TubRightN" title="Scroll Next" class="contRoler curveLeft rightAbs-0 "><i class="fa fa-angle-right"></i></a>

<a id="TubLeftN" title="Scroll Previous" class="contRoler curveRight leftAbs-0"><i class="fa fa-angle-left"></i></a>

<div id="RunerWrapper">

<div id="RunerPlaylist">

<ul id="MidPlaylist" class="playlist">

<li class="waiters">

<i class="fa fa-refresh fa-spin"></i>

<span class="spining">

Getting data please hold on.....

</span>

<img src="$imgRUrl" width=100% alt="developios">

</li>

</ul>

</div>

</div>

</div>

<div class="curveBoth midBelt" id="LisTitleHolder">

<a data-token="MainNextToken" title="Load next  videos from playlist" class="rightAbs-0 curveLeft">next <i class="fa fa-angle-double-right"></i></a>

<a data-token="MainPrevToken" title="Load previous videos from playlist" class="leftAbs-0 curveRight"><i class="fa fa-angle-double-left"></i> prev </a>

<div id="LisTitle">

<span id="LisTokenRun" class="spining hideMe"><i class="fa fa-spinner fa-pulse"></i> ...loading more videos hold on</span>

<div id="marqueeMid" class="marquee suade curveBoth">

<span class="now">Working...</span> Processing Data <span class="name"> This may take few minutes </span>Depends On Your Internet Connection <span class="name">Please hold on while data loading ...</span> </span>

</div>

</div>

</div>

</div>

<div id="Tubfooter" class="curveBoth">

<a href="http://tubentertain.com" target="_blank">Powered by TubEntertain&trade;</a>

<a id="aboutTubPlay" title="All about TubPlay" target="_blank">About</a>

</div>



<!--Do More Here -->

<div id="TubPlayOver"></div>

<!--Do More Here -->

</div>

EOT;

    return $output;

}

elseif($standalone=="YES"){

//=============STANDALONE HTML PRINT===========================

$output = <<<EOT

<div id="TubPlayFrame"



data-platform="WordPress"



data-vid-now="$livestreamid"



data-share-played="$isShared" 



data-list-id="$ToDisplayPid" 



data-ison-now="" 



data-stand-alone="$standalone" 



data-search-vid="$search" 



data-action-vid="$ChioceAct"



data-altstreamid-vid="$ALTVid"

  >

<div id="playerHolder" class="player  curveBoth">

<span id="interwaiter" class="totalAbs-0 curveBoth hideMe"></span>

 <div id="TubVideoFrame" class="hideMe" >

<video autoplay id="TubVideo"  name="media"><source id="TubSource" src="" type=""></video>

</div>

<div id="TubFrame" >$src</div>

<div id="rayLead" class="hideMe rightAbs-0 curveLeft">

<img id="leadImg" src="" width=50 alt="ProVideo">

<span class="skipBnt"><span class="hideMe curveBoth">Skip Video Now</span></span>

<span class="ProNote">Will Play Next  Video in <em>...00</em></span>

</div>



</div>

<div id="StandA" class="marquee curveBoth">

<span class="standAloneSpan">$vidTitle</span>

</div>

$displayMonitor

<div class='tub-share-buttons tub-row'>

<div class='tub-col-1-3 suade curveBoth tub-googleplus'>

<a  class='TubSocial' id='sGooglePlus' href='https://plusone.google.com/_/+1/confirm?hl=en-US&url=$url' title='Share on Google+' target='_blank'><span style='color:#fff' class='tub-share-button'><i class='fa fa-google-plus fa-2x' ></i>SHARE</span></a>

</div>

<div class='tub-col-1-3 suade curveBoth tub-facebook'>

<a id='sFaceBook' class='TubSocial' href='http://www.facebook.com/sharer.php?u=$url&t=$vidTitle'  title='Share on Facebook'><span style='color:#fff' class='tub-share-button'><i class='fa fa-facebook fa-2x'></i>SHARE</span></a></div>

<div class='tub-col-1-3 suade curveBoth tub-twitter'>

<a  class='TubSocial' id='sTwitter' href='http://twitter.com/share?text=$vidTitle&url=$url' title='Tweet This Post'><span style='color:#fff' class='tub-share-button'><i class='fa fa-twitter fa-2x'></i>TWEET</span></a></div>

</div>

</div>

EOT;

}
elseif($standalone=="INPOST"){

//=============STANDALONE INPOST PRINT===========================

$output = <<<EOT

<div id="TubPlayFrame"

data-stand-alone="$standalone"

data-search-vid="$search" 

data-action-vid="$ChioceAct"

  >

<div id="playerHolder" class="player  curveBoth">

<div id="TubFrame" >$src</div>

</div>

<div class='tub-share-buttons tub-row'>

<div class='tub-col-1-3 suade curveBoth tub-googleplus'>

<a  class='TubSocial' id='sGooglePlus' href='https://plusone.google.com/_/+1/confirm?hl=en-US&url=$url' title='Share on Google+' target='_blank'><span style='color:#fff' class='tub-share-button'><i class='fa fa-google-plus fa-2x' ></i>SHARE</span></a>

</div>

<div class='tub-col-1-3 suade curveBoth tub-facebook'>

<a id='sFaceBook' class='TubSocial' href='http://www.facebook.com/sharer.php?u=$url&t=$vidTitle'  title='Share on Facebook'><span style='color:#fff' class='tub-share-button'><i class='fa fa-facebook fa-2x'></i>SHARE</span></a></div>

<div class='tub-col-1-3 suade curveBoth tub-twitter'>

<a  class='TubSocial' id='sTwitter' href='http://twitter.com/share?text=$vidTitle&url=$url' title='Tweet This Post'><span style='color:#fff' class='tub-share-button'><i class='fa fa-twitter fa-2x'></i>TWEET</span></a></div>

</div>

</div>

EOT;

}

//=============End Of Function Inner===========================

return $output;

}//tubentertain_func ends here

//===============================================

//If exsit

}// We should be live in 6, 5, 4, 3, 2, 1, zero seconds

add_shortcode( 'tubentertain', 'tubentertain_tub' );

 //allows shortcode execution in the widget, excerpt and content

            add_filter('widget_text', 'do_shortcode');

            add_filter('the_excerpt', 'do_shortcode', 11);

            add_filter('the_content', 'do_shortcode', 11);

//We are live! yahp

//===========================================================================================

//========AJAX REQUEST RESPONSES==================

function TubReceiver_Send() {

//::::::::::::::::::::::::::::::::::::

ob_clean();

//:::::::::::getDuration Data::::::::::::

function Kovtime($t){ 

$t;

$date = new DateTime('1970-01-01');

$date->add(new DateInterval($t));

return $date->format('H:i:s');

}//get duration

//=====================

//:::::::::Time Ago::::::::

function timeAgo($datetime, $full = false) {

    $now = new DateTime;

    $ago = new DateTime($datetime);

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);

    $diff->d -= $diff->w * 7;

    $string = array(

        'y' => 'year',

        'm' => 'month',

        'w' => 'week',

		'd' => 'day',

        'h' => 'hour',

        'i' => 'minute',

        's' => 'second',

		    );

    foreach ($string as $k => &$v) {

        if ($diff->$k) {

            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');

        } else {

            unset($string[$k]);

        }

		}

    if (!$full) $string = array_slice($string, 0, 1);

    return $string ? implode(', ', $string) . ' ago' : 'just now';

}

function ShrinkContent($content,$maxracter){

$Title=str_replace(array('[',']','{','}','»','»','|','Â»','Â»','Â¿',':',';','"', "'",'-', '.', '(', ')', '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), '', $content);	

$cuk=strlen($Title);

if ($cuk > $maxracter){

$dTitle=substr($Title,0,$maxracter)." ...";

}

else

{

$dTitle=$Title;

} 

return $dTitle; 

}

//============DEFINE===================

//=============================

//Configuration DATA

//==============

// 

$getClass=new TubEntertainPage();

$GetConfigData=$getClass->TubGetConfigData(1);

$channel=$GetConfigData[0]['TubChannelId'];

$apiKey=$GetConfigData[0]['TubApiKey'];

$StreamVideoId=$GetConfigData[0]['TubStreamId'];

$LiveTv=$StreamVideoId;

//Configuration DATA ends



//========================================

function PrintTitle($videoId,$apiKey){

$leepTitleVideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$videoId."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($leepTitleVideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$leepTitleVidoId = json_decode($return, true);

$leepTitle=$leepTitleVidoId['items'][0]['snippet']['title'];

return $leepTitle;

}//=======================

if(isset($_REQUEST['exVido'])){

$exVido=PrintTitle($_REQUEST['exVido'],$apiKey);

echo ucwords(strtolower(ShrinkContent($exVido,67)));

}

//========================

$LiveTvTitle=PrintTitle($LiveTv,$apiKey);

//if(isset($_GET['tsv'])){$LiveTvTitle=PrintTitle($_GET['tsv'],$apiKey);}

//=====================

if(isset($_REQUEST['VideoNew'])){

//::::::::::::::::::::::::::::::::::::

// Advertisments                    ::

//                                  ::

//::::::::::::::::::::::::::::::::::::

//$VideoNew=$getClass->TubPlay($_REQUEST['VideoNew']);

echo "3TZwZo5fWG4";

}//======ISSET=======ENDS ADVERT RENDERING=====================

//===>>>>>>

if(isset($_REQUEST['TubVideo'])){

//::::::::::::::::::::::::::::::::::::

// //Render new converted video     ::

//                                  ::

//::::::::::::::::::::::::::::::::::::

$ActinVideoId=$_REQUEST['TubVideo'];

$TubVideo=$getClass->TubPlay($ActinVideoId);



if($TubVideo!=="Error" && $TubVideo[0]['type']!==""){

for ($i = 0; $i < count($TubVideo); $i++) {

    echo '<source src="'. $TubVideo[$i]['url'] .'" type="'. $TubVideo[$i]['type'] .'">';

 }

}//Downlodable is true

else

{ //$NoDownload ="NoDownload";// array("noview" => "NoDownload");

 echo "NoDownload";//$liveTub= json_encode($NoDownload);

}



}//======ISSET=======ENDS COVERTED RENDERING=====================TubVideo

//::::::::::::::::::::::::::::::::::

//    get Channel Info START Here ::

//                                :: 

//::::::::::::::::::::::::::::::::::

if(isset($_REQUEST['dChannel'])){$channelId=$_REQUEST['dChannel'];}else{$channelId=$channel;}

$youtubeChannel = "https://www.googleapis.com/youtube/v3/channels?part=brandingSettings,snippet,contentDetails&id=".$channelId."&key=".$apiKey;

$curl = curl_init($youtubeChannel);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$resultChannel = json_decode($return, true); 

//var_dump($result);

if(!$resultChannel)

{

echo "<span class='errorShow'><span class='error'>Error!</span> Code:001-Call-1-CONFIGURATION Issues</span>";

}

$ChannelTitle=$Channeldescription=$ChannelLogo=$PromoVidTitle=$ChannelBanner=$PromoVid=$playlistId=$googlePlusUserId="";

if(isset($resultChannel['items'][0]['snippet']['title'])){

$ChannelTitle=$resultChannel['items'][0]['snippet']['title'];

}

if(isset($resultChannel['items'][0]['snippet']['description'])){

$Channeldescription=$resultChannel['items'][0]['snippet']['description']; 

}

if(isset($resultChannel['items'][0]['snippet']['thumbnails']['medium']['url'])){

$ChannelLogo=$resultChannel['items'][0]['snippet']['thumbnails']['medium']['url'];

}

if(isset($resultChannel['items'][0]['brandingSettings']['image']['bannerImageUrl'])){

$ChannelBanner=$resultChannel['items'][0]['brandingSettings']['image']['bannerImageUrl'];

}

if(isset($resultChannel['items'][0]['brandingSettings']['channel']['unsubscribedTrailer'])){

$PromoVid=$resultChannel['items'][0]['brandingSettings']['channel']['unsubscribedTrailer'];



$PromoVidTitle=PrintTitle($PromoVid,$apiKey);

}

if(isset($resultChannel['items'][0]['contentDetails']['relatedPlaylists']['uploads'])){

$uploadsPlaylistId=$resultChannel['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

}

if(isset($resultChannel['items'][0]['contentDetails']['googlePlusUserId'])){

$googlePlusUserId=$resultChannel['items'][0]['contentDetails']['googlePlusUserId'];

}

//===========Request Ini Video

//==========================

//::::::::::::::::::::::::::::::::::

//    get Channel Info ENDS Here ::

//                                :: 

//::::::::::::::::::::::::::::::::::

if(isset($_REQUEST['BulkyList'])){

//==========Is set Blucky Playlist================

$tubBlukyToken=$_REQUEST['BulkyList'];

//=========================

$youtubeChannelPlayListData ="https://www.googleapis.com/youtube/v3/playlists?part=id,status,snippet,contentDetails&channelId=".$channelId."&pageToken=".$tubBlukyToken."&maxResults=30&key=".$apiKey;

$curl = curl_init( $youtubeChannelPlayListData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$ChannelPlayListData = json_decode($return, true);

if(!$ChannelPlayListData)

{

echo "<span class='errorShow'><span class='error'>Error!</span> Code:002-Call-2-CONFIGURATION Issues</span>";

}

else{

$prevPageToken='';$nextPageToken='';

if(isset($ChannelPlayListData['nextPageToken'])) {

$nextPageToken=$ChannelPlayListData['nextPageToken'];

}

//

if(isset($ChannelPlayListData['prevPageToken'])) {

$prevPageToken=$ChannelPlayListData['prevPageToken'];

}

?>

<input data-feature-vid="<?php echo $PromoVid; ?>" data-feature-title="<?php echo ucwords(strtolower(ShrinkContent($PromoVidTitle,67))) ?>" data-live-title="<?php echo ucwords(strtolower(ShrinkContent($LiveTvTitle,67))) ?>" data-live-vid="<?php echo $LiveTv ?>" id="BulkyPrevToken" style="display:none;" class="hideMe" data-page-token="<?php echo $prevPageToken ?>" type="hidden" >

<input id="BulkyNextToken" style="display:none;" class="hideMe" data-page-token="<?php echo $nextPageToken ?>" type="hidden">

<?php

foreach($ChannelPlayListData['items'] as $key => $item){

$privacyStatus=$item['status']['privacyStatus'];

$itemCount=$item['contentDetails']['itemCount'];

$Thumb=$item['snippet']['thumbnails']['medium']['url'];

if($privacyStatus !== 'public'||$Thumb=="https://i.ytimg.com/vi/default.jpg"||$itemCount < 1){continue;}

$ListId=$item['id'];

$playListTitle=$item['snippet']['title'];

$playListDescription=$item['snippet']['description'];

if(!$playListDescription){$playListDescription="Description Not Available";}

//===============   

//

?>

<li>

<span class="hideMe listWaiter"><i class="fa fa-spin fa-circle-o-notch"></i></span>

<img  title="<?php echo ucwords(strtolower(ShrinkContent($playListTitle,67) )) ?>" src="<?php echo $Thumb ?>" height="50" alt="<?php echo ShrinkContent($playListTitle,67)  ?>">

<span class=titleHolder>

<a title="Play All Set Of <?php echo $itemCount ?> Videos" class="PlayAll" data-pid="<?php echo $ListId ?>" data-pid-title="<?php echo $playListTitle ?>">

<i class="fa fa-play"></i>...Play all

</a>

<a title="More Details About The PlayList" class="MoreDetail"><i  class="fa fa-info-circle"></i> <?php echo $itemCount ?> Videos</a>

<span class="liTitler"><?php echo ShrinkContent($playListTitle,67)  ?> </span>

</li>

<?php

}//FOREACH

//======================

//Isset

}

//:::::::::::::::::::::::::::::::::::::

// Bulky Playlist Requets Ends Here  ::

}//Data Available or Not 

//:::::::::::::::::::::::::::::::::::::

if(isset($_REQUEST['MainListToken'])){

//==========Is set MainList Playlist================

$tubMainToken="";

$playlistId="";

if($_REQUEST['MainListToken']!==""){

$tubMainToken=$_REQUEST['MainListToken'];

}

if($_REQUEST['playlistId']!==""){

$playlistId=$_REQUEST['playlistId'];

}

// some channel may has no uploded video

else

{

$playlistId=$uploadsPlaylistId;

}//ELSE

//=========================

$ChannelPlayListItems= "https://www.googleapis.com/youtube/v3/playlistItems?part=id,snippet,contentDetails,status&pageToken=".$tubMainToken."&maxResults=30&playlistId=".$playlistId."&key=".$apiKey;

$curl = curl_init($ChannelPlayListItems);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$PlayListData = json_decode($return, true);

if(!$PlayListData)

{

echo "<span class='errorShow'><span class='error'>Error!</span> Code:003-Call-1-CONFIGURATION Issues</span>";

}

else{

$totalVideo=$PlayListData['pageInfo']['totalResults'];

$resultsPerPage=$PlayListData['pageInfo']['resultsPerPage'];

$playListTitle=$PlayListData['items'][0]['snippet']['title'];

$featureVideo=$PlayListData['items'][0]['snippet']['resourceId']['videoId'];

$prevPageToken='';$nextPageToken='';

if(isset($PlayListData['nextPageToken'])) {

$nextPageToken=$PlayListData['nextPageToken'];

}

//

if(isset($PlayListData['prevPageToken'])) {

$prevPageToken=$PlayListData['prevPageToken'];

}

//================================================

?>

<input id="MainPrevToken" style="display:none;" class="hideMe"  data-page-token="<?php echo $prevPageToken ?>"  data-total-vids="<?php echo $getClass->CovtFrontNumToKilo($totalVideo); ?>" data-fvid-title="<?php echo $playListTitle ?>" data-fvid-id="<?php echo $featureVideo ?>" type="hidden" data-call-type="Main">

<input id="MainNextToken" style="display:none;" class="hideMe" data-page-token="<?php echo $nextPageToken ?>" type="hidden" data-call-type="Main" data-query="<?php echo $dataQuery ?>">

<?php

foreach($PlayListData['items'] as $key => $item){ 

$privacyStatus=$item['status']['privacyStatus'];	  

$videoId=$item['snippet']['resourceId']['videoId'];//========= 

//$position=($item['snippet']['position']+1);

$position=($key+1);

$PlayListVideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$videoId."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($PlayListVideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$featureVidoId = json_decode($return, true);

//===============================

if($featureVidoId['items']){

$viewCount =  $featureVidoId['items'][0]['statistics']['viewCount'];

$commentCount =  $featureVidoId['items'][0]['statistics']['commentCount'];

$duration =  $featureVidoId['items'][0]['contentDetails']['duration'];

$Thumb=$featureVidoId['items'][0]["snippet"]["thumbnails"]["high"]["url"];

$duration = Kovtime($duration);

$uploadedDate=$featureVidoId['items'][0]["snippet"]["publishedAt"];

$upd =explode("T", (string)$uploadedDate);

$upLdate = $upd[0];

$uplt= explode(".", (string)$upd[1]);

$upLtime=$uplt[0];

$dTitle=$featureVidoId['items'][0]['snippet']['title'];

$description=$featureVidoId['items'][0]['snippet']['description']; 

if($description){

$description=str_replace(array('’','"',  '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), '', $description);

$description= preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="http://tubentertain.com/t/?tsv='.$videoId.'" title="'.$dTitle.'" target="_blank"><i class="fa fa-external-link"></i></a>', $description);

}

else

{

$description="Description Not Available!";

}

//CHECK IF EMBBEDABLE OTHERWISE THROW IT OUT

if($privacyStatus !== 'public'){continue;}

 //Create List Here

//===============   

//

?>

<li class="playSome" data-position="<?php echo $position ?>" id="GOROYE<?php echo $videoId ?>" data-video-title="<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>">



<span class="iamNext hideMe  rightAbs-0" title="Up Next"> <i class="fa fa-angle-double-right"></i> Next</span>

<img  src="<?php echo $Thumb; ?>" width=120 alt="">

<div class="caption" ></div>

<span class="playThis" data-video-id="ADE247<?php echo $videoId ?>" data-video-title="<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>" title="play"><i class="fa fa-play"></i></span>

<div class=content>

<span class=titleHolder>

<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>

</span>

</div>

<span class="theLenght"><?php echo $duration ?></span>

<span class="uploadDate" title="Uploaded  <?php  echo timeAgo($upLdate); ?>"><i class="fa fa-upload"></i> <?php  echo timeAgo($upLdate); ?></span>

<a data-vid-info="<?php echo $videoId ?>" class="MoreDetail" title="More Details"><i  class="fa fa-info-circle"></i> ...info</a>

</li>

<?php

}//FOREACH

//======================

}

//Isset

}

//:::::::::::::::::::::::::::::::::::::

// MainListPlaylist Requets Ends Here  ::

}//Data Available or Not 

//:::::::::::::::::::::::::::::::::::::

//queryToSearch

if(isset($_REQUEST['queryToSearch'])){

$queryToSearch=$_REQUEST['queryToSearch'];

$ToSearchToken=$_REQUEST['ToSearchToken'];

$queryToSearch=str_replace(' ','+',$queryToSearch);

//:::::::::::Grab Data Search Results ::::::::::::

$queryToSearchData ="https://www.googleapis.com/youtube/v3/search?q=".$queryToSearch."&part=snippet&key=".$apiKey."&pageToken=".$ToSearchToken."&maxResults=30";

$curl = curl_init($queryToSearchData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$SearchDataRes = json_decode($return, true);



if(!$SearchDataRes)

{

echo "<span class='errorShow'><span class='error'>Error!</span> Code:003-Call-3-CONFIGURATION Issues</span>";

}

else{

$prevPageToken='';

$nextPageToken='';

$featureVideo='';

//===============================================

$totalVideo=$SearchDataRes['pageInfo']['totalResults'];

$resultsPerPage=$SearchDataRes['pageInfo']['resultsPerPage'];

//===============================================

if(isset($SearchDataRes['items'][0]['id']['videoId']) && !$featureVideo) {

$featureVideo=$SearchDataRes['items'][0]['id']['videoId'];

}

//

else if(isset($SearchDataRes['items'][1]['id']['videoId']) && !$featureVideo) {

$featureVideo=$SearchDataRes['items'][1]['id']['videoId'];

}

//

else{

$featureVideo=$SearchDataRes['items'][2]['id']['videoId'];

}

//=========================

$playListTitle=$SearchDataRes['items'][0]['snippet']['title'];

//===================================

if(isset($SearchDataRes['nextPageToken'])) {

$nextPageToken=$SearchDataRes['nextPageToken'];

}

//

if(isset($SearchDataRes['prevPageToken'])) {

$prevPageToken=$SearchDataRes['prevPageToken'];

}

$dataQuery=$_REQUEST['queryToSearch'];

?>

<input id="MainPrevToken" style="display:none;" class="hideMe"  data-page-token="<?php echo $prevPageToken ?>"  data-total-vids="<?php echo $getClass->CovtFrontNumToKilo($totalVideo); ?>" data-fvid-title="<?php echo $playListTitle ?>" data-fvid-id="<?php echo $featureVideo ?>" type="hidden" data-call-type="Search" >

<input id="MainNextToken" style="display:none;" class="hideMe" data-page-token="<?php echo $nextPageToken ?>" type="hidden" data-call-type="Search" data-query="<?php echo $dataQuery ?>">

<?php 

foreach($SearchDataRes['items'] as $key => $item){

$kind=$item['id']['kind']; 

if($kind !== 'youtube#video'){continue;}

$SearchListId=$item['id']['videoId'];

$PlayListVideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$SearchListId."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($PlayListVideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$featureVidoId = json_decode($return, true);

//===============================

if($featureVidoId['items']){

$viewCount =  $featureVidoId['items'][0]['statistics']['viewCount'];

$commentCount =  $featureVidoId['items'][0]['statistics']['commentCount'];

$duration =  $featureVidoId['items'][0]['contentDetails']['duration'];

$Thumb=$featureVidoId['items'][0]["snippet"]["thumbnails"]["high"]["url"];

$duration = Kovtime($duration);

$uploadedDate=$featureVidoId['items'][0]["snippet"]["publishedAt"];

$upd =explode("T", (string)$uploadedDate);

$upLdate = $upd[0];

$uplt= explode(".", (string)$upd[1]);

$upLtime=$uplt[0];

$dTitle=$featureVidoId['items'][0]['snippet']['title'];

$description=$featureVidoId['items'][0]['snippet']['description']; 

if($description){

$description=str_replace(array('’','"',  '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), '', $description);

$description= preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="http://tubentertain.com/t/?tsv='.$SearchListId.'" title="'.$dTitle.'" target="_blank"><i class="fa fa-external-link"></i></a>', $description);

}

else

{

$description="Description Not Available!";

}

$position=$key + 1;

//===============

//==============================================

?>

<li class="playSome" data-position="<?php echo $position ?>" id="GOROYE<?php echo $SearchListId ?>" data-video-title="<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>">



<span class="iamNext hideMe  rightAbs-0" title="Up Next"> <i class="fa fa-angle-double-right"></i> Next</span>

<img  src="<?php echo $Thumb; ?>" width=120 alt="">

<div class="caption" ></div>

<span class="playThis" data-video-id="ADE247<?php echo $SearchListId ?>" data-video-title="<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>" title="play"><i class="fa fa-play"></i></span>

<div class=content>

<span class=titleHolder>

<?php echo ucwords(strtolower(ShrinkContent($dTitle,67))) ?>

</span>

</div>

<span class="theLenght"><?php echo $duration ?></span>

<span class="uploadDate" title="Uploaded  <?php  echo timeAgo($upLdate); ?>"><i class="fa fa-upload"></i> <?php  echo timeAgo($upLdate); ?></span>

<a data-vid-info="<?php echo $SearchListId ?>" class="MoreDetail" title="More Details"><i  class="fa fa-info-circle"></i> ...info</a>

</li>

<?php

}//FOREACH RESUL

}//else no data

}//else no items

}//queryToSearch isset

//==============================================================================

if(isset($_REQUEST['theVidId'])){

$_REQUEST['theVidId'];

//============

$videoId=$_REQUEST['theVidId'];

$VideoData ="https://www.googleapis.com/youtube/v3/videos?id=".$videoId."&part=id,statistics,snippet,contentDetails,status&key=".$apiKey;

$curl = curl_init($VideoData);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$return = curl_exec($curl);

curl_close($curl); 

$featureVidoId = json_decode($return, true);

//===============================

if($featureVidoId['items']){

$viewCount =  $featureVidoId['items'][0]['statistics']['viewCount'];

$commentCount =  $featureVidoId['items'][0]['statistics']['commentCount'];

$duration =  $featureVidoId['items'][0]['contentDetails']['duration'];

$Thumb=$featureVidoId['items'][0]["snippet"]["thumbnails"]["high"]["url"];

$duration = Kovtime($duration);

$uploadedDate=$featureVidoId['items'][0]["snippet"]["publishedAt"];

$upd =explode("T", (string)$uploadedDate);

$upLdate = $upd[0];

$uplt= explode(".", (string)$upd[1]);

$upLtime=$uplt[0];

$dTitle=$featureVidoId['items'][0]['snippet']['title'];

$description=$featureVidoId['items'][0]['snippet']['description']; 

if($description){

$description=str_replace(array('’','"',  '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), '', $description);

$description= preg_replace('/(https?:\/\/[^\s"<>]+)/','<a  href="http://tubentertain.com/t/?tsv='.$videoId.'" title="'.$dTitle.'" target="_blank"><i class="fa fa-external-link"></i></a>', $description);

}else{$description="Description Not Available!";}

//=====================

?>

<div class="TubIframe">

<div id="InfoheadDiv"><?php echo ShrinkContent($dTitle,100); ?></div>

<span class="dViews">

 <i class="fa fa-eye"></i>Views: <?php echo $getClass->CovtFrontNumToKilo($viewCount); ?>

</span>



<img width=280 height=210  src="https://i.ytimg.com/vi/<?php echo $videoId; ?>/hqdefault.jpg" /> 



<div id="MoreInfoDiv">

<div id="InfoDiv">

<span class="ds">

<?php echo ucwords(strtolower($description)); ?>

</span>

</div>

</div>

<div id="TubBnt">

<a id="FavourBnt" class="TubPlayBnt" title="Add To Favorite List" target="_blank"> <i  class="fa fa-heart"></i>AddFavorite</a>

<a id="downloadBnt" class="TubPlayBnt" title="Download MP4" target="_blank"><i class="fa fa-download"></i> DownloadMP4</a>

</div>

</div>

<?php	

}//============if data available

else

{

echo "<span class='errorShow'><span class='error'>Error!</span> Code:004-Call-1-CONFIGURATION Issues</span>";

}//============if no-data available

}//===========Is set ends here

//

//

//

//viewr counter

if(isset($_REQUEST['ViewersNow'])){

$reSiteSat=$getClass->liveStats($_REQUEST['ViewersNow'],$apiKey);

echo $reSiteSat;

}

//=========================	

//===========================================

wp_die();

}

//TubReceiver_Send

add_action( 'wp_ajax_nopriv_TubReceiver_Send', 'TubReceiver_Send' );

add_action( 'wp_ajax_TubReceiver_Send', 'TubReceiver_Send' );

//We are communicating! yahp



?>