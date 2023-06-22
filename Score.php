<?php
// 使用vendor和PHPExcel庫，來讀取修改excel的資料
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Score {
  // public 外部ajaxUpload還會用到這些資料，之後要改function去讀這些資料，外部的人才不能改資料完整性
  public $title, $arrWeight, $headers, $arrGrade, $cols, $weight_total, $arrTotal, $arrAverage; 
  // 初始化物件，確保值是已知的
  function __construct($filename, $tmpname) {
    $this->title = "";     $this->arrWeight = array();        $this->headers = array();     
    $this->arrGrade = array();   $this->cols = array();       $this->weight_total=0;
    $this->arrTotal = array();   $this->arrAverage =array();  $this->arrGradeSort = array();
    $this->arrStac = array();    $this->memo1 = "";           $this->memo2 = "";
    $this->color1 = "#99ccff";  $this->color2 = "#ccccff";  $this->color3 = "#ccffcc";  $this->color4 = "#99ccff";
    $this->iTotal = 20;         $this->iAverage = 21;

    $arrFileName = explode('.', $filename);
    echo $filename.":".$tmpname."<BR>";
    if ($arrFileName[1] == 'xlsx') {
      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
      $spreadsheet = $reader->load($tmpname);
      $d=$spreadsheet->getSheet(0)->toArray();
      foreach ($d as $row) {
        // print_r($row);  echo "<BR>";
        $this->procLine($row); //逐行讀取
      }
    }
    $this->prepare();
  }

  function procLine($arr) {
    if (Strip($arr[0]) == '標題') {
      $this->title = $arr[1];
    } else if (Strip($arr[0]) == '註記一') {
       $this->memo1 = $arr[1];
    } else if (Strip($arr[0]) == '註記二') {
      $this->memo2 = $arr[1];
    } else if (Strip($arr[0]) == '顏色') {
      $this->color1 = strlen(Strip($arr[1]))>0 ? Strip($arr[1]) : $this->color1; 
      $this->color2 = strlen(Strip($arr[2]))>0 ? Strip($arr[2]) : $this->color2; 
      $this->color3 = strlen(Strip($arr[3]))>0 ? Strip($arr[3]) : $this->color4; 
      $this->color4 = strlen(Strip($arr[4]))>0 ? Strip($arr[4]) : $this->color4; 
    } else if (Strip($arr[0]) == '座號') {
      $grade = FALSE;
      foreach ($arr as $key => $value) {
        $value = Strip($value);
        $this->headers[$key] = $value;
        $this->cols[$key] = array();
        // 只計算姓名和總分間的成績
        if ('姓名' == $value) {
          $grade = TRUE;
        } else if ('總分' == $value) {
          $grade = FALSE;
        } else if ($grade) {
          $this->arrGrade[$key] = $value; // 成績欄
        }
      }
    } else if (Strip($arr[0]) == '加權') {
      foreach ($arr as $key => $value) {
        if (array_key_exists($key, $this->arrGrade)) {
          $this->arrWeight[$key] = (int)$value;
          $this->weight_total += (int)$value;
        }
      }
    } else if ((int)$arr[0]>0) {    // 學生成績
      // echo $line."<BR>";
      $total = 0;
      // print_r($arr); echo "<BR>";
      foreach ($arr as $key => $value) {
        $this->cols[$key][] = $value;
        if (array_key_exists($key, $this->arrGrade)) {
          $total += (int)$value * $this->arrWeight[$key];
        }
      }
      $this->arrTotal[] = $total;
      $this->arrAverage[] = round($total/$this->weight_total, 2); //個人平均
    } else if ($arr[1]=='校平均') {
      $this->arrStat[$arr[1]] = $arr;
    } else {
      // echo "ERROR:".$line."<BR>";
    }
    // echo "名次：";  print_r($this->cols[10]); echo "<BR>";
  }
  
  //讀完資料後，去算班平均, 班高標等
  function prepare() {
    foreach ($this->arrGrade as $col=>$val) {
      $this->arrGradeSort[$col] = $this->cols[$col];
    }
    $this->arrGradeSort[$this->iTotal] = $this->arrTotal;
    $this->arrGradeSort[$this->iAverage] = $this->arrAverage;
    $this->arrGrade[$this->iTotal] = '總分';
    $this->arrGrade[$this->iAverage] = '平均';
    // print_r($this->arrGrade);   echo "<BR>";
    foreach ($this->arrGradeSort as $col=>$val) {
      rsort($this->arrGradeSort[$col]);
      $this->arrStat['班平均'][$col] = round(Average($this->arrGradeSort[$col]), 1);
      $this->arrStat['班高標'][$col] = Quartile($this->arrGradeSort[$col], 0.25);
      $this->arrStat['班級標準差'][$col] = round(StdDev($this->arrGradeSort[$col]), 1);
      $number = 60;
      $cnt = array_reduce($this->arrGradeSort[$col], function($ret, $val) use ($number) {
        return $ret += $val >= $number;
      });
      $this->arrStat['班級及格%'][$col] = round(100*$cnt/count($this->arrGradeSort[$col]), 1);
      // print_r($this->arrGradeSort[$col]);   echo "<BR>";
    }
  }
  function trStat($col) {
    $tr = "<tr class='stat'><td>$col</td>";
    foreach ($this->arrGrade as $key=>$val) {
      if ($col!='班平均' && ($key==$this->iTotal || $key==$this->iAverage)) {
        $tr .= "<td></td>";
      } else {
        $tr .= "<td>{$this->arrStat[$col][$key]}</td>";
      }
    }
    $tr .= "</tr>";
    return $tr;
  }

  function getStatistic($col) {
    $arr = array();
    $a = array_filter($this->arrGradeSort[$col]);
    $arr['班平均'] = round(Average($a), 1);
    $arr['班高標'] = round(StdDev($a), 1);
    $arr['班級標準差'] = Quartile($a, 0.25);
    $number = 60;
    $cnt = array_reduce($a, function($ret, $val) use ($number) {
      return $ret += $val >= $number;
    });
    $arr['班級級格'] = $cnt;
    // print_r($arr);  echo "<BR>";
    return $arr;
  }
}

function Quartile($Array, $Quartile) {
  $pos = (count($Array) - 1) * $Quartile;  
  $base = floor($pos);  
  $rest = $pos - $base;  
  if( isset($Array[$base+1]) ) {  
    return $Array[$base] + $rest * ($Array[$base+1] - $Array[$base]);
  } else {
    return $Array[$base];
  }
}

function Average($Array) {
  $Array= array_filter($Array);
  return array_sum($Array) / count($Array); 
}

function StdDev($a) {
  $a = array_filter($a);
  $n = count($a);
  if ($n === 0) {
      trigger_error("The array has zero elements", E_USER_WARNING);
      return false;
  }
  $mean = array_sum($a) / $n;
  $carry = 0.0;
  foreach ($a as $val) {
      $d = ((double) $val) - $mean;
      $carry += $d * $d;
  };
  // if ($sample) {
  //    --$n;
  // }
  return sqrt($carry / $n);
}

function Strip($text) {
  $text = str_replace(" ", "", $text);
  $text = str_replace("　", "", $text);
  return $text;
}


?>