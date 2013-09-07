<?php
/*-----------�ޤJ�ɮװ�--------------*/
$xoopsOption['template_main'] = "tad_uploader_adm_main.html";
include_once "header.php";
include_once "../function.php";

/*-----------function��--------------*/


//�C�X�Ҧ�catalog���
function list_catalog($the_cat_sn=""){
	global $xoopsDB,$xoopsTpl;
	catalog_form($the_cat_sn);
	
	$sql = "select * from ".$xoopsDB->prefix("tad_uploader")."";

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, _MA_TADUP_DB_ERROR1);

  $jquery_path = get_jquery(true);

  if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/treetable.php")){
   redirect_header("index.php",3, _MA_NEED_TADTOOLS);
  }
  include_once XOOPS_ROOT_PATH."/modules/tadtools/treetable.php";
	$treetable=new treetable(false,"cat_sn","of_cat_sn","#tbl","save_drag.php",".folder","#save_msg",false,".sort","save_sort.php","#save_msg2");
	$treetable_code=$treetable->render();
		
	$title=sprintf(_MA_TADUP_LIST_ALL_FILES,$total);
	
	$xoopsTpl->assign('title',$title);
	$xoopsTpl->assign('jquery_path',$jquery_path);
	$xoopsTpl->assign('treetable_code',$treetable_code);
	//$xoopsTpl->assign('catalog_form',$catalog_form);
  $get_cata_data=get_cata_data();
	$xoopsTpl->assign('get_cata_data',$get_cata_data['data']);
}


//���o�Ҧ���Ƨ��C��
function get_cata_data($of_cat_sn=0,$level=0,$i="0"){
	global $xoopsDB,$xoopsTpl;
  $old_level=$level;
  $left=$level*20;
  $level++;  
  
	$sql = "select * from ".$xoopsDB->prefix("tad_uploader")." where of_cat_sn='$of_cat_sn' order by `cat_sort`";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, _MA_TADUP_DB_ERROR1);

	while(list($cat_sn,$cat_title,$cat_desc,$cat_enable,$uid,$of_cat_sn,$cat_share,$cat_sort,$cat_count)=$xoopsDB->fetchRow($result)){
  
		$cat_desc=nl2br($cat_desc);
		$uid_name=XoopsUser::getUnameFromId($uid,1);
    $uid_name=(empty($uid_name))?XoopsUser::getUnameFromId($uid,0):$uid_name;
    
		$enable=($cat_enable=='1')?"<img src='../images/button_ok.png'>":"<img src='../images/button_cancel.png'>";
		$share=($cat_share=='1')?"<img src='../images/button_ok.png'>":"<img src='../images/encrypted.gif'>";
		$cat=get_catalog($of_cat_sn);


		$class=(empty($of_cat_sn))?"":"class='child-of-node-_{$of_cat_sn}'";

	
    $data[$i]['class']=$class;
    $data[$i]['cat_sort']=$cat_sort;
    $data[$i]['cat_title']=$cat_title;
    $data[$i]['cat_desc']=$cat_desc;
    $data[$i]['uid_name']=$uid_name;
    $data[$i]['enable']=$enable;
    $data[$i]['share']=$share;
    $data[$i]['cat_count']=$cat_count;
    $data[$i]['cat_sn']=$cat_sn;
    $i++;
    
    $new_data=get_cata_data($cat_sn,$level,$i);
		if(!empty($new_data['data'])){
      $data=array_merge($data,$new_data['data']);
      $i=$new_data['i'];
    }
	}

  $all['data']=$data;
  $all['i']=$i;
  return $all;
}

//catalog�s�����
function catalog_form($cat_sn=""){
	global $xoopsDB,$xoopsModule,$xoopsTpl;
	include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");
	
	//����w�]��
	if(!empty($cat_sn)){
		$DBV=get_catalog($cat_sn);
	}else{
		$DBV=array();
	}

	//�w�]�ȳ]�w
	$cat_sn=(!isset($DBV['cat_sn']))?"":$DBV['cat_sn'];
	$cat_title=(!isset($DBV['cat_title']))?"":$DBV['cat_title'];
	$cat_desc=(!isset($DBV['cat_desc']))?"":$DBV['cat_desc'];
	$cat_enable=(!isset($DBV['cat_enable']))?"1":$DBV['cat_enable'];
	$uid=(!isset($DBV['uid']))?"":$DBV['uid'];
	$of_cat_sn=(!isset($DBV['of_cat_sn']))?"":$DBV['of_cat_sn'];
	$cata_select=get_cata_select(array(),$of_cat_sn);
	$cat_share=(!isset($DBV['cat_share']))?"1":$DBV['cat_share'];
	$cat_count=(!isset($DBV['cat_count']))?"":$DBV['cat_count'];

	$cat_max_sort=get_cat_max_sort();
	$cat_sort=(!isset($DBV['cat_sort']))?$cat_max_sort:$DBV['cat_sort'];

  $mod_id=$xoopsModule->getVar('mid');
  $moduleperm_handler =& xoops_gethandler('groupperm');
  $read_group=$moduleperm_handler->getGroupIds("catalog", $cat_sn, $mod_id);
  $post_group=$moduleperm_handler->getGroupIds("catalog_up", $cat_sn, $mod_id);
  
  if(empty($read_group))$read_group=array(1,2,3);
  if(empty($post_group))$post_group=array(1);

  //�i���s��
	$SelectGroup_name = new XoopsFormSelectGroup("", "catalog", true,$read_group, 6, true);
	$SelectGroup_name->setExtra("class='span12'");
	$enable_group = $SelectGroup_name->render();

  //�i�W�Ǹs��
  $SelectGroup_name = new XoopsFormSelectGroup("", "catalog_up", true,$post_group, 6, true);
	$SelectGroup_name->setExtra("class='span12'");
  $enable_upload_group = $SelectGroup_name->render();


	$xoopsTpl->assign('cata_select',$cata_select);
	$xoopsTpl->assign('cat_title',$cat_title);
	$xoopsTpl->assign('cat_desc',$cat_desc);
	$xoopsTpl->assign('enable_group',$enable_group);
	$xoopsTpl->assign('enable_upload_group',$enable_upload_group);
	$xoopsTpl->assign('cat_sn',$cat_sn);
	$xoopsTpl->assign('cat_count',$cat_count);
	$xoopsTpl->assign('cat_sort',$cat_sort);
	$xoopsTpl->assign('cat_enable',$cat_enable);
	$xoopsTpl->assign('cat_share',$cat_share);
}




//���ocatalog�Ҧ���ư}�C
function get_catalog_all(){
	global $xoopsDB;
	$sql = "select * from ".$xoopsDB->prefix("tad_uploader");
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, _MA_TADUP_DB_ERROR1);
	$data=$xoopsDB->fetchArray($result);
	return $data;
}





/*-----------����ʧ@�P�_��----------*/
$op = (!isset($_REQUEST['op']))? "":$_REQUEST['op'];
$cat_sn=(empty($_REQUEST['cat_sn']))?"":intval($_REQUEST['cat_sn']);

switch($op){
	case "add_catalog":
	add_catalog($cat_sn,$_POST['cat_title'],$_POST['cat_desc'],$_POST['cat_enable'],$_POST['of_cat_sn'],$_POST['cat_add_form'],$_POST['cat_share'],$_POST['cat_sort'],$_POST['cat_count'],$_POST['catalog'],$_POST['catalog_up']);
	header("location: ".$_SERVER['PHP_SELF']);
	break;

	//�R�����
	case "delete_catalog";
	delete_catalog($cat_sn);
	header("location: ".$_SERVER['PHP_SELF']);
	break;


	default:
	list_catalog($cat_sn);
	break;
}

/*-----------�q�X���G��--------------*/
include_once 'footer.php';
?>