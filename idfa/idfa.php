<?php

/*
 * 
 * srcdata/dsf/2016062
 * srcdata/dm/2016062
 * 
 * descdata/dsf/2016062
 * descdata/dm/2016062
 * 
 * fxdata/2016062.txt
 * fxdata/2016062_diff.txt
 * fxdata/sx/2016062/* 筛选后的结果文件
 */
error_reporting(E_ALL);
set_time_limit(0);
ini_set("memory_limit", "1000M");
date_default_timezone_set('Europe/London');

define('OS', 'linux');
include 'PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

function get_extension($file) {
    return substr($file, strrpos($file, '.') + 1);
}

//循环建目录
function pmkdir($dir) {
    $mkdir = "";
    $arr = explode("/", $dir);
    foreach ($arr as $v) {
        $mkdir .= $v . "/";
        if (!is_dir($mkdir))
            mkdir($mkdir);
    }
}

//从目录下读取文件，并将文件路径名存储为数组
function preaddir($dir) {
    $d = dir($dir);
    $arr = array();
    while (false !== ($entry = $d->read())) {
        if ($entry == "." || $entry == "..")
            continue;
        $arr[] = array($dir, $entry);
    }
    $d->close();
    return $arr;
}

//处理第三方数据类
class dsfexcelfx {

    public $action = "";
    public $date = "";
    public $srcdir = "";
    public $descdir = "";
    public $data = array();

    function __construct($action, $date) {

        $this->action = $action;
        $this->date = $date;
    }

    function initdir() {

        $srcdir = "srcdata/" . $this->action . "/" . $this->date;
        $dir = "descdata/{$this->action}/{$this->date}";
        pmkdir($dir);

        $this->srcdir = $srcdir;
        $this->descdir = $dir;
    }

    function importexcel() {

        $filearr = preaddir($this->srcdir);
        foreach ($filearr as $tmpfile) {
            $this->getexcel($tmpfile[0], $tmpfile[1]);
        }
    }

    function writedata() {

        foreach ($this->data as $k => $v) {
            $filep = $this->descdir . "/" . $k;
            foreach ($v as $kk => $vv) {
                if (OS == "windows")
                    $file = $filep . "_" . trim(iconv("UTF-8", "GBK", $kk)) . ".txt";
                else
                    $file = $filep . "_" . trim($kk) . ".txt";
                $fp = fopen($file, "w");
                foreach ($vv as $vvv) {
                    fwrite($fp, $vvv . "\r\n");
                }
                fclose($fp);
            }
        }
    }

    function getexcel($dir, $file) {

        $filedesc = substr($file, 0, 8);
        $inputFileName = $dir . "/" . $file;
        if (get_extension($inputFileName) == "xls")
            $inputFileType = 'Excel5';
        else
            $inputFileType = 'Excel2007';

        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

        $count = $objPHPExcel->getSheetCount();
        $loadedSheetNames = $objPHPExcel->getSheetNames();

        if ($count < 1)
            return true;

        $objPHPExcel->getSheetCount();

        $count = $objPHPExcel->getSheetCount();
        $loadedSheetNames = $objPHPExcel->getSheetNames();

        if ($count < 1)
            return true;

        $tmpdata = array();
        for ($i = 0; $i < $count; $i++) {
            $objPHPExcel->setActiveSheetIndex($i);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $type = $sheetData[1];
            unset($sheetData[1]);

            foreach ($type as $k => $v) {
                if ($v == "idfa") {
                    $gkey = $k;
                    break;
                }
            }

            foreach ($sheetData as $k => $v) {
                $o_key = trim($v[$gkey]);
                if ($o_key != "")
                    $this->data[$filedesc][$loadedSheetNames[$i]][$v[$gkey]] = $v[$gkey];
            }
        }
    }

}

class dmexcelfx {

    public $action = "";
    public $date = "";
    public $srcdir = "";
    public $descdir = "";
    public $data = array();

    function __construct($action, $date) {

        $this->action = $action;
        $this->date = $date;
    }

    function initdir() {

        $srcdir = "srcdata/" . $this->action . "/" . $this->date;
        $dir = "descdata/{$this->action}/{$this->date}";
        pmkdir($dir);
        $this->srcdir = $srcdir;
        $this->descdir = $dir;
    }

    //date标准格式应该 = [20160701]
    public function parsedate($date) {
        
        /*
        $date = str_replace("年", "", $date);
        $date = str_replace("月", "", $date);
        $date = str_replace("日", "", $date);
        $ym = substr($date, 0, 4) . "0" . substr($date, 4, 1);
        $d = (substr($date, 5)) < 10 ? "0" . substr($date, 5) : substr($date, 5);
        $str = $ym . $d;
 
        return $str;
*/
        //   $date = substr($date,0,4) . "0"  , 
        $tmpdata = explode("/", $date);
        $d_y = $tmpdata[0];
        $d_m = $tmpdata[1];
        $d_d = $tmpdata[2];
        //$tpmdata = explode(" ", $d_d);
        //$d_d = $tpmdata [0];
        $d_m = ($d_m < 10) ? "0" . $d_m : $d_m;
        $d_d = ($d_d < 10) ? "0" . $d_d : $d_d;
        return $d_y . $d_m . $d_d;
    }

    public function getexcel($dir, $file) {
        $inputFileName = $dir . "/" . $file;
        if (get_extension($inputFileName) == "xls")
            $inputFileType = 'Excel5';
        else
            $inputFileType = 'Excel2007';

        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

        //$objReader = PHPExcel_IOFactory::createReader($inputFileType);
        //$worksheetNames = $objReader->listWorksheetNames($inputFileName);
        //print_r( $worksheetNames);

        $count = $objPHPExcel->getSheetCount();
        $loadedSheetNames = $objPHPExcel->getSheetNames();

        if ($count < 1)
            return true;

        $tmpdata = array();
        for ($i = 0; $i < $count; $i++) {
            $objPHPExcel->setActiveSheetIndex($i);

            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

            $type = $sheetData[1];
            unset($sheetData[1]);
            /*
             * Array
              (
              [A] => id
              [B] => idfa
              [C] => user_id
              [D] => create_at
              [E] => modify_at
              [F] => date
              )
             */
            foreach ($type as $k => $v) {
                if (strtolower($v) == "idfa")
                    $g_key = $k;

                if (strtolower($v) == "date")
                    $g_date = $k;
            }

    
            foreach ($sheetData as $k => $v) {
                $sj = trim($v[$g_key]);
                $date = trim($v[$g_date]);
                if ($date == "" || $sj == "")
                    continue;

                    //echo $date  . "\n" ;
                $date = $this->parsedate($date);
                   //echo $date  . "\n" ;
    
    
                $this->data[$date][$sj] = 1;
            }
        }
    }

    function writedata() {

        foreach ($this->data as $k => $v) {
            $file = $this->descdir . "/" . str_replace("-", "", $k) . ".txt";
            $fp = fopen($file, "w");
            foreach ($v as $kk => $vv) {
                fwrite($fp, $kk . "\r\n");
            }
            fclose($fp);
        }
    }

    function importexcel() {
        $filearr = preaddir($this->srcdir);
        foreach ($filearr as $tmpfile) {
            $this->getexcel($tmpfile[0], $tmpfile[1]);
        }
    }
}

class fx {

    public $dsfdir = "";
    public $dmdir = "";
    public $sxdir = "";
    public $fxdir = "";
    public $fxfile = "";
    public $fxdifffile = "";
    public $excelfile = "";
    public $data = array();

    function __construct($date) {
        $this->dsfdir = "descdata/dsf/" . $date;
        $this->dmdir = "descdata/dm/" . $date;
        $this->fxdir = "fxdata";
        $this->sxdir = $this->fxdir . "/sx/" . $date;
        $this->fxfile = $this->fxdir . "/" . $date . "_fx.txt";
        $this->fxdifffile = $this->fxdir . "/" . $date . "_fxdiff.txt";
        $this->excelfile = $this->fxdir . "/" . $date . ".xlsx";
        $this->date = $date;
    }

    function initdir() {
        pmkdir($this->sxdir);
    }

    function getfilecount($file) {
        return system("wc  {$file} | awk '{print $1}' ");
    }

    function parse() {

        $filearr = preaddir($this->dsfdir);
        $this->data = array();
        $tmpfile = $this->fxdir . "/tmp.log";
        foreach ($filearr as $k => $v) {
            $dsffile_prex = $v[1];

            $tmpdata = explode("_", $dsffile_prex);
            $g_date = $tmpdata[0];
            $g_type = $tmpdata[1];
            $g_type = str_replace(".txt", "", $g_type);

            $dsffile = $v[0] . "/" . $v[1];
            $dmfile = $this->dmdir . "/" . $g_date . ".txt";
            $sxfile = $this->sxdir . "/" . $g_date . $g_type . ".txt"; //筛选文件

            $cmd = "sort {$dsffile} {$dmfile}  | uniq -d >{$tmpfile} && sort {$dsffile} {$tmpfile}| uniq -u > {$sxfile}  ";
            system($cmd);
            $this->data[] = array($g_date, $g_type, $this->getfilecount($tmpfile), $this->getfilecount($dsffile));
        }
        unlink($tmpfile);
    }

    function writedata() {
        $fxtmp = $this->fxdir . "/fxtmp.txt";
        $fp = fopen($fxtmp, "w");
        foreach ($this->data as $k => $v) {
            $str = implode("_", $v);
            fwrite($fp, $str . "\r\n");
        }
        fclose($fp);

        $cmd = "sort {$fxtmp }  -k 2 >  {$this->fxfile}  ";
        system($cmd);
        unlink($fxtmp);
    }

    function saveexcel() {

        $file = $this->excelfile;
        if (!file_exists($file)) {
            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($file);
            $objPHPExcel = NULL;
            $objWriter = NULL;
        }

        $sheet = "x1";
        $objPHPExcel = PHPExcel_IOFactory::load($file);
        $count = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($count);
        $objPHPExcel->getActiveSheet()->setTitle($sheet);

        $dataarr = file($this->fxdifffile);
        $row = 1;
        foreach ($dataarr as $v) {

            $v = trim($v);
            if ($v == "")
                continue;
            $tmpstr = explode("_", $v);

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $tmpstr[1]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $tmpstr[2]);
            $row++;
        }

        $sheet = "x2";
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($count + 1);
        $objPHPExcel->getActiveSheet()->setTitle($sheet);

        $dataarr = file($this->fxfile);
        $row = 1;
        foreach ($dataarr as $v) {

            $v = trim($v);
            if ($v == "")
                continue;
            $tmpstr = explode("_", $v);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $tmpstr[0]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $tmpstr[1]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $tmpstr[2]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $tmpstr[3]);
            $row++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file);
    }

    function exportdata() {
        $arr = file($this->fxfile);
        $this->data = array();
        foreach ($arr as $k => $v) {
            $t = explode("_", $v);
            if ($t[2] / $t[3] < 0.95) {
                $file = $this->sxdir . "/" . $t[0] . $t[1] . ".txt";

                $tmp = file($file);
                foreach ($tmp as $v) {
                    $v = trim($v);
                    if ($v != "")
                        $this->data[] = array($t[0], $t[1], $v);
                }
            }
        }

        $fp = fopen($this->fxdifffile, "w");
        if ($fp) {
            foreach ($this->data as $v) {
                fwrite($fp, implode("_", $v) . "\r\n");
            }
            fclose($fp);
        }
    }
}

$action = $argv[1];
$date = $argv[2]; //日期

<<<<<<< HEAD
//$action = "export";
//$date = "2016083";
=======
//$action = "dm";
//$date = "2016091";
>>>>>>> ffa0e057cc89b6e5bc78e6a321bc2a62ce9451d4

switch ($action) {
    case "dsf":
        //文件名应该是 [20160607]这样的前缀
        $obj = new dsfexcelfx($action, $date);

        $obj->initdir();
        $obj->importexcel();
        $obj->writedata();
        break;

    case "dm":
        //excel 里面日期的标准格式是20160607,假如不是需要做些修改,具体是修改parsedate函数
        //excel 的sheet 头应该是 [idfa]和[date]
        $obj = new dmexcelfx($action, $date);
        $obj->initdir();
        $obj->importexcel();
        $obj->writedata();
        break;

    //比较dm和disf的数据,并生成一个文本文件
    case "fx":
        $obj = new fx($date);
        $obj->initdir();
        $obj->parse();
        $obj->writedata();
        break;

    //将结果文件导出到excel中
    case "export":

        $obj = new fx($date);
        $obj->initdir();
        $obj->exportdata();
        $obj->saveexcel();
        break;
}
        
