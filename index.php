<!DOCTYPE html>
<html lang="en">
<head>
    <title>建國中學 月考學生成績產生器</title>
    <!-- 引入外部資源，以便在網頁上調整樣式和功能 -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="icon" href="https://codingbirdsonline.com/wp-content/uploads/2019/12/cropped-coding-birds-favicon-2-1-192x192.png" type="image/x-icon">
</head>
<body>
<div class="jumbotron text-center">
    <h1>建國中學 月考學生成績產生器</h1>
</div>
<!-- 在網頁上創建一個文件上傳表單，讓老師上傳本次月考和上次月考的文件，並提交資料到 ajaxUpload.php -->
<div class="container">
    <form action="ajaxUpload.php" method="post" enctype="multipart/form-data">
        本次月考
        <div class="form-group">
            <input type="file" name="exam1" id="exam1" class="form-control" />
        </div>
        上次月考
        <div class="form-group">
            <input type="file" name="exam2" id="exam2" class="form-control" />
        </div>
        <input type="submit" name="uploadBtn" id="uploadBtn" value="Upload Excel" class="btn btn-success" />

    </form>
</div>
</body>
</html>
