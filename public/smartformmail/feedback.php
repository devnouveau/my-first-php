<!DOCTYPE html>
<html>
  <head>
    <title>Bob's Auto Parts - Customer Feedback</title>
  </head>
  <body>

    <h1>Customer Feedback</h1>
    <p>Please tell us what you think.</p>

    <form action="processfeedback.php" method="post">
    <p><strong>Your name:</strong><br/>
    <input type="text" name="name" size="40" /></p>
     
    <p><strong>Your email address:</strong><br/>
    <input type="text" name="email" size="40" /></p>
     
    <p><strong>Your feedback:</strong><br/>
    <textarea name="feedback" rows="8" cols="40">
    </textarea></p>
     
    <p><input type="submit" value="Send Feedback" /></p>  
    </form>

  <?php 
  // 입력값 유효성 확인
  // isset() 함수 사용
  ?>

  </body>
</html>
