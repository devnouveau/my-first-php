<?php

// 입력값 전달받기
$vote = $_POST['vote'];
if (empty($vote)) {
  echo '<p>You have not voted for a politician.</p>';
  exit;
}

/*******************************************
  DB 처리
*******************************************/
// DB 연결
$db = new mysqli('localhost', 'poll', 'poll', 'poll');

if (mysqli_connect_errno()) {
    echo '<p>Error: Could not connect to database.<br/>
    Please try again later.</p>';
    exit;
}

// DB 변경
$v_query = "UPDATE poll_results 
            SET num_votes = num_votes + 1 
            WHERE candidate = ?";
$v_stmt = $db->prepare($v_query);
$v_stmt->bind_param('s', $vote);  
$v_stmt->execute();
$v_stmt->free_result();

// DB 조회
$r_query = "SELECT candidate, num_votes FROM poll_results";
$r_stmt = $db->prepare($r_query);
$r_stmt->execute();
$r_stmt->store_result();
$r_stmt->bind_result($candidate, $num_votes);
$num_candidates = $r_stmt->num_rows;

// 총 투표수 
$total_votes = 0;
while ($r_stmt->fetch())
{    
    $total_votes +=  $num_votes;
}

$r_stmt->data_seek(0);






/*******************************************
  변수설정
*******************************************/
// For Unix
putenv('GDFONTPATH=/usr/share/fonts/truetype/dejavu'); 

$width = 500;        // width of image in pixels
$left_margin = 50;   // space to leave on left of graph
$right_margin= 50;   // space to leave on right of graph
$bar_height = 40;
$bar_spacing = $bar_height/2;

// For Unix
$font_name = 'DejaVuSans';

// For Windows
//$font_name = 'C:\Windows\Fonts\arial.ttf';

$title_size= 16;     // in points
$main_size= 12;      // in points
$small_size= 12;     // in points
$text_indent = 10;   // position for text labels from edge of image

// Set up initial point to draw from
$x = $left_margin + 60;  // place to draw baseline of the graph
$y = 50;                 // ditto
$bar_unit = ($width-($x+$right_margin)) / 100;   // one "point" on the graph

// Calculate height of graph - bars plus gaps plus some margin
$height = $num_candidates * ($bar_height + $bar_spacing) + 50;




/*******************************************
  이미지 그리기
*******************************************/
// Create a blank canvas
$im = imagecreatetruecolor($width,$height);

// Allocate colors
$white = imagecolorallocate($im,255,255,255);
$blue = imagecolorallocate($im,0,64,128);
$black = imagecolorallocate($im,0,0,0);
$pink = imagecolorallocate($im,255,78,243);

$text_color = $black;
$percent_color = $black;
$bg_color = $white;
$line_color = $black;
$bar_color = $blue;
$number_color = $pink;

// Create "canvas" to draw on
imagefilledrectangle($im, 0, 0, $width, $height, $bg_color);

// Draw outline around canvas
imagerectangle($im, 0, 0, $width-1, $height-1, $line_color);

// Add title
$title = 'Poll Results';
$title_dimensions = imagettfbbox($title_size, 0, $font_name, $title);
$title_length = $title_dimensions[2] - $title_dimensions[0];
$title_height = abs($title_dimensions[7] - $title_dimensions[1]);
$title_above_line = abs($title_dimensions[7]);
$title_x = ($width-$title_length)/2;  // center it in x
$title_y = ($y - $title_height)/2 + $title_above_line; // center in y gap

imagettftext($im, $title_size, 0, $title_x, $title_y,
             $text_color, $font_name, $title);

// Draw a base line from a little above first bar location
// to a little below last
imageline($im, $x, $y-5, $x, $height-15, $line_color);





/*******************************************
  데이터를 그래프로 그리기
*******************************************/

// Get each line of DB data and draw corresponding bars
while ($r_stmt->fetch())
{

    if ($total_votes > 0) {
        $percent = intval(($num_votes/$total_votes)*100);
    } else {
        $percent = 0;
    }

    // 현재 값의 백분률
    $percent_dimensions = imagettfbbox($main_size, 0, $font_name, $percent.'%');
    $percent_length = $percent_dimensions[2] - $percent_dimensions[0];
    imagettftext($im, $main_size, 0, $width-$percent_length-$text_indent,
                $y+($bar_height/2), $percent_color, $font_name, $percent.'%');

    // 현재 값의 막대길이
    $bar_length = $x + ($percent * $bar_unit);

    // 현재 값의 막대 그리기
    imagefilledrectangle($im, $x, $y-2, $bar_length, $y+$bar_height, $bar_color);

    // 현재 값의 제목 그리기
    imagettftext($im, $main_size, 0, $text_indent, $y+($bar_height/2),
                $text_color, $font_name, $candidate);

    // 100% 외곽선 그리기
    imagerectangle($im, $bar_length+1, $y-2,
                    ($x+(100*$bar_unit)), $y+$bar_height, $line_color);

    // 숫자출력
    imagettftext($im, $small_size, 0, $x+(100*$bar_unit)-50, $y+($bar_height/2),
                $number_color, $font_name, $num_votes.'/'.$total_votes);

    // y좌표값을 다음 막대 위치로 변경
    $y=$y+($bar_height+$bar_spacing);

}

/*******************************************
  이미지 출력
*******************************************/
header('Content-type:  image/png');
imagepng($im);

/*******************************************
  리소스 해제
*******************************************/
$r_stmt->free_result();
$db->close();
imagedestroy($im);

?>
