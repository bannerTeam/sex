<?php
namespace app\common\taglib;
use think\template\TagLib;
use think\Db;

class Maccms extends Taglib {

	protected $tags = [
	    'link'=> ['attr'=>'order,by,type,start,num'],
        'area'=> ['attr'=>'order,start,num'],
        'lang'=> ['attr'=>'order,start,num'],
        'year'=> ['attr'=>'order,start,num'],
        'class'=> ['attr'=>'order,start,num'],
        'version'=> ['attr'=>'order,start,num'],
        'state'=> ['attr'=>'order,start,num'],
        'letter'=> ['attr'=>'order,start,num'],
        'type' => ['attr' =>'order,by,start,num,id,ids,flag,mid,format'],
        'role'=>['attr' =>'order,by,start,num,paging,id,ids,rid,actor,level,letter,half'],
        'actor'=>['attr' =>'order,by,start,num,paging,id,ids,area,level,letter,half'],
        'comment'=>['attr' =>'order,by,start,num,paging,type,id,pid,rid,half'],
        'gbook'=>['attr' =>'order,by,start,num,paging,type,half'],
        'topic' => ['attr' =>'order,by,start,num,id,ids,paging,half'],
        'art' => ['attr' =>'order,by,start,num,id,ids,paging,pageurl,type,class,tag,level,letter,half'],
        'vod' => ['attr' =>'order,by,start,num,id,ids,paging,pageurl,type,class,area,lang,year,level,letter,half'],
        'foreach' => ['attr'=>'name,id,key'],
        'for' => ['attr'=>'start,end,comparison,step,name'],
	    'ad' => ['attr'=>'flag'],
    ];

    public function tagFor($tag,$content)
    {
        $start = $tag['start'];
        $end = $tag['end'];
        $comparison = $tag['comparison'];
        $step = $tag['step'];
        $name = $tag['name'];

        if(empty($start)){
            $start = 1;
        }
        if(empty($end)){
            $end = 5;
        }
        if(empty($comparison)){
            $comparison = 'elt';
        }
        if(empty($step)){
            $step = 1;
        }
        if(empty($name)){
            $name = 'i';
        }

        $parse='';
        $parse .= '{for start="'.$start.'" end="'.$end.'" comparison="'.$comparison.'" step="'.$step.'" name="'.$name.'"}';
        $parse .= $content;
        $parse .= '{/for}';

        return $parse;
    }

    public function tagForeach($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        $name = $tag['name'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }
        $parse='';
        $parse .= '{foreach name="'.$name.'" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/foreach}';
        
        return $parse;
    }

    public function tagArea($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->areaData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id .'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagLang($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->langData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagClass($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->classData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagYear($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->YearData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id .'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }


    public function tagVersion($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->versionData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagState($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->stateData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }


    public function tagLetter($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Extend")->letterData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagLink($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Link")->listCacheData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }


    public function tagType($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Type")->listCacheData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagComment($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
		$rid = $tag['rid'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Comment")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagGbook($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Gbook")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }


    public function tagTopic($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Topic")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagActor($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Actor")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagRole($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Role")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagArt($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Art")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagVod($tag,$content)
    {
        $id = $tag['id'];
        $key = $tag['key'];
        if(empty($id)){
            $id = 'vo';
        }
        if(empty($tag['key'])){
            $key = 'key';
        }

        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = model("Vod")->listCacheData($__TAG__);';
        if($tag['paging']=='yes'){
            $parse .= '$__PAGING__ = mac_page_param($__LIST__[\'total\'],$__LIST__[\'limit\'],$__LIST__[\'page\'],$__LIST__[\'pageurl\'],$__LIST__[\'half\']);';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__[\'list\']" id="'.$id.'" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }
    
    
    public function tagAd($tag,$content)
    {
        $id = $tag['id'];
        $flag = $tag['flag'];
        if(empty($id)){
            $id = $flag;
        }        
       
        config('ad');
        $datas = controller('ad')->get_adv('i_banner');
        
        
        
        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__LIST__ = controller("ad")->get_adv("'.$flag.'");';
        
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="'.$id.'" }';
        $parse .= $content;
        $parse .= '{/volist}';
        
        return $parse;
    }
}
