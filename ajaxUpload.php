<?php
require_once "Score.php";
// print_r($_FILES);
// 檢查有幾個檔案上傳(可以只上傳一個)，並轉為$score2, $score
$score2 = null;
if ($_FILES['exam2']['name']) {
  $score2 = new Score($_FILES['exam2']['name'], $_FILES['exam2']['tmp_name']);
}
if ($_FILES['exam1']['name']) {
  $score = new Score($_FILES['exam1']['name'], $_FILES['exam1']['tmp_name']);
  renderHTML($score, $score2);
}

function renderHTML($score, $score2) {
  $score->trStat = $score->trStat('班平均').$score->trStat('校平均').$score->trStat('班高標').$score->trStat('班級標準差').$score->trStat('班級及格%');
  $score->iNum = array_search ('座號', $score->headers);
  $score->iName = array_search ('姓名', $score->headers);
  $score->ids = array_keys($score->cols[0]);
  $html = "";
  foreach ($score->cols[0] as $student_id=>$value) {
    $arrScore = array();
    foreach ($score->arrGrade as $kGrade=>$vGrade) {
      if ($kGrade == $score->iTotal) {
        $arrScore[$kGrade] = $score->arrTotal[$student_id];
      } else if ($kGrade == $score->iAverage) {
        $arrScore[$kGrade] = $score->arrAverage[$student_id];
      } else {
        $arrScore[$kGrade] = $score->cols[$kGrade][$student_id];
      }
    }
    $html .= HtmlScore($score, $score2, $student_id, $arrScore);
  }
  $percent = round(100/(count($score->arrGrade)+2));
$htmlAll =<<< HTML
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
</head>
<body>
    $html;
</body>
<style>
  body {
    width: 210mm; /* A4 dimension */
  }
  @page {
    margin:0;
    padding: 0;
  }
  .tblScore th { width: $percent%}
  .tblScore, .tblScore td, .tblScore th {
    border: 1px solid black;
    text-align: center;
    font-size: 12px;
    padding: 0px;
  }
  .tblScore {   border-collapse: collapse; }
  .tblReturn { font-size: 12px; }
  .name { background-color: $score->color1 }
  .hl { background-color: $score->color2 }
  .stat { background-color: $score->color3 }
  .acc { background-color: $score->color4 }
  @media print {
    footer {page-break-after: always;}
  }
</style>
HTML;
  echo $htmlAll;
}

function ArraySearch($target, $target2, $arr) {
  foreach ($arr as $key => $value) {
    if (strpos($value, $target) !== false && strpos($value, $target2) !== false) {
      // echo "find $key->$value<BR>";
      return $key;
    }
  }
  return -1;
}

function GetRank($score, $acc_id, &$cls_rank, &$grp_rank, &$grp_pr) {
  $keyClsRank = ArraySearch('班級','名次', $score->headers);
  $keyGrpRank = ArraySearch('類組','名次', $score->headers);
  $keyGrpPR = ArraySearch('類組','百分', $score->headers);
  $cls_rank = $keyClsRank>0 ? $score->cols[$keyClsRank][$acc_id] : null;
  $grp_rank = $keyGrpRank>0 ? $score->cols[$keyGrpRank][$acc_id] : null;
  $grp_pr = $keyGrpPR>0 ? $score->cols[$keyGrpPR][$acc_id] : null;
}

function _GetProgress($score1, $score2) {
  if ($score1 > $score2) {
    return "退步 ".($score1-$score2)."名";
  } else if ($score1 < $score2) {
    return "進步 ".($score2-$score1)."名";
  } 
}

function HtmlScore($score, $score2, $acc_id, $arrScore) {
  $num = $score->cols[$score->iNum][$acc_id];
  $name = $score->cols[$score->iName][$acc_id];
  GetRank($score, $acc_id, $cls_rank, $grp_rank, $grp_pr);
  if ($score2) {
    GetRank($score2, $acc_id, $cls_rank2, $grp_rank2, $grp_pr2);
  } else {
    $cls_rank2=null;  $grp_rank2=null;  $grp_pr2=null;
  }
  if ($cls_rank2 && $grp_rank2 && $grp_pr) {
    $cls_rank_p = _GetProgress($cls_rank, $cls_rank2);
    $grp_rank_p = _GetProgress($grp_rank, $grp_rank2);
    $grp_pr_p = _GetProgress($grp_pr, $grp_pr2);
$html_rank =<<< HTML_RANK
    <tr><td rowspan='3' width='25%'>學生：$name</td> 
        <td>班級名次：　{$cls_rank_p}</td><td>(本次名次：{$cls_rank}</td><td>上次名次：{$cls_rank2})</td></tr>
    <tr><td>類組名次：　{$grp_rank_p}</td><td>(本次名次：{$grp_rank}</td><td>上次名次：{$grp_rank2})</td></tr>
    <tr><td>類組百分比：　{$grp_pr_p}</td><td>(本次百分比：{$grp_pr}</td><td>上次百分比：{$grp_pr2})</td></tr>
HTML_RANK;
  } else {  // 只有本次月考
$html_rank =<<< HTML_RANK
    <tr><td rowspan='3' width='25%'>學生：$name</td> 
        <td>班級名次：{$cls_rank}</td></tr>
    <tr><td>類組名次：{$grp_rank}</td></tr>
    <tr><td>類組百分比：{$grp_pr}</td></tr>
HTML_RANK;
  }
  $th1 = "<tr><th class='name'>{$num}號{$name}</th>";
  $th2 = "<tr><th>排序</th>";
  $tr  = "";    $trAcc = "<tr class='acc'><td> $name </td>";
  foreach ($score->arrGrade as $key=>$value) {
    if ($key==$score->iTotal || $key==$score->iAverage) {
      $th1 .= "<th rowspan='2'>{$value}</th>";
    } else {
      $th1 .= "<th>{$value}</th>";
      $th2 .= "<th>*{$score->arrWeight[$key]}</th>";
    }
    $trAcc .= "<td>{$arrScore[$key]}</td>";
  }
  $th1 .= "</tr>";    $th2 .= "</tr>";    $trAcc .= "</tr>";
  // print_r($arrScore); echo "<BR>";
  foreach ($score->ids as $i) {
    $student_id = $i+1;
    $tr .= "<tr><td>{$student_id}</td>";
    foreach ($arrScore as $key=>$sc) {
      $scoreSorted = $score->arrGradeSort[$key][$i];
      $class = ($scoreSorted == $sc) ? "class='hl'" : "";
      // echo "$scoreSorted<->$score, ";
      // echo $class;
      $tr .= "<td $class>{$scoreSorted}</td>";
    }
  }
$html =<<< HTML
<div style='text-align:center; padding:20px 40px 20px 40px'>
<div style='text-align:left; font-size:14px'>{$score->memo1}</div><BR><BR><BR>
  <h3 style='margin:8px'>{$score->title}</h3>
  <center><table class='tblScore' width='80%'> $th1 $th2 $tr {$score->trStat} $trAcc </table></cetner>
  <hr style='border-top: 3px dotted black'>
  <div style='font-size:14px'> {$score->title} </div>
  <center> <table class='tblReturn' width='90%'>
    $html_rank
  </table class='tblReturn'> </center>
  <div style='text-align:left; font-size:14px'>家長回饋意見：</div><BR><BR><BR><BR>
  <div style='text-align:right; font-size:14px'>家長簽名：__________________{$score->memo2}</div>
</div>
<footer> </footer>
HTML;
  return $html;
}
// echo "<script>alert('Excel file has been uploaded successfully !');window.location.href='index.php';</script>";
?>