<?php declare(strict_types=1);?>

<?php //login logic
    session_start();
    $mesage = '';
    if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {	
        if ($_POST['username'] == 'Edvinas' && $_POST['password'] == '0000') {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = 'Edvinas';
        } else {
            $mesage = 'Wrong username or password';
        }
    }

    //logout logic
    if(isset($_POST['logout'])) {
        session_start();
        unset($_SESSION['username']);
        unset($_SESSION['password']);
        unset($_SESSION['logged_in']);
    }

    //new directory creation logic
    if (isset($_GET['new_dir'])) {
        if ($_GET['new_dir'] != '') {
            $new_dir = './' .$_GET['my_way'] . $_GET['new_dir'];
            mkdir($new_dir, 0777, true);
            $refresh_start = explode('%2F', $_SERVER['QUERY_STRING'],-1);
            $refresh_last = '?' . implode('/',$refresh_start).'/';
            header('Location: ' . $refresh_last);
        } // NOT WORKING IN PARENT DIRECTORY FROM START OF PROGRAM
            $refresh_start = explode('%2F', $_SERVER['QUERY_STRING'],-1); 
            $refresh_last = '?' . implode('/',$refresh_start).'/';
            header('Location: ' . $refresh_last);
    }

    //file deletion logic
    if(isset($_POST['deletion'])){
        $file_del = './' .$_GET["my_way"] . $_POST['deletion']; 
        $file_delete = str_replace("&nbsp;", " ", htmlentities($file_del, ENT_QUOTES, 'utf-8'));
        if(is_file($file_delete)){
            unlink($file_delete);
            header('Refresh:0');
        }
    }

    //file download logic
    if(isset($_POST['download'])){
        $downloadPath='./' . $_GET["my_way"] . $_POST['download'];
        $downloadFile = str_replace("&nbsp;", " ", htmlentities($downloadPath, ENT_QUOTES, 'utf-8'));
        ob_clean();
        ob_start();
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf'); // mime type → ši forma turėtų veikti daugumai failų, su šiuo mime type. Jei neveiktų reiktų daryti sudėtingesnę logiką
        header('Content-Disposition: attachment; filename=' . basename($downloadFile));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($downloadFile)); // kiek baitų browseriui laukti, jei 0 - failas neveiks nors bus sukurtas
        ob_end_flush();
        readfile($downloadFile);
        exit;
    }

    //file upload logic
    if(isset($_FILES['fileToUpload'])){
        $errors= array();
        $file_name = $_FILES['fileToUpload']['name'];
        $file_size = $_FILES['fileToUpload']['size'];
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];
        $file_type = $_FILES['fileToUpload']['type'];
        $file_ext = strtolower(end(explode('.', $_FILES['fileToUpload']['name'])));
        
        $extensions= array('jpeg','jpg','txt','pptx','xlsx','docx');
        
        if(in_array($file_ext , $extensions) === false){
           $errors[] = "extension not allowed, please choose a JPEG, PNG, TXT, PPTX, XLSX, DOCX file.";
        }
        
        if($file_size > 1048576 ) {
           $errors[] = 'File size must be below 1 MB';
        }
        
        if(empty($errors)==true) {
           move_uploaded_file($file_tmp, './' . $_GET["my_way"] . $file_name);
           echo "Success";
        }else{
            print_r($_FILES);
            print('<br>');
            print_r($errors);
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple file browser</title>
    <style>
        table {
            width: 80%;
            margin-left: 10%;
            margin-right: 10%;
        }
        table, tr, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 5px;
        }
        th {
            background-color: #1C23FF;
            color: #FFF;
        }
        tr:nth-child(even) {
            background-color: #eee;
        }
        tr:nth-child(odd) {
            background-color: #fff;
        }
    </style>
</head>
<body <?php if(!$_SESSION['logged_in'] == true) print('style="background-color: #4C00FF;"'); 
            if($_SESSION['logged_in'] == true) print('style="background-color: #F9FFA8;"')?> >

    <?php //login form
    if(!$_SESSION['logged_in'] == true){
        print('<div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);">');
        print('<form action = "" method = "post">');
        print('<h4>' . $mesage . '</h4>');
        print('<input type="text" name="username" placeholder = "username = Edvinas" required></br>');
        print('<input type="password" name="password" placeholder = "password = 0000" required><br>');
        print('<button type="submit" name="login" style="margin-left: 55px; margin-top: 10px">Login</button>');
        print('</form>');
        print('</div>');
        die();
    }

    $path = './'.$_GET['my_way'];
    $files = scandir($path);
    print('<h2>You are in: '. $path .'</h2>');
    echo '<br>';
    print('<table>');
    print('<tr><th>File or Folder</th><th>Name</th><th>Action</th></tr>');

    // main structure - scan current directory and show files/folders
    foreach ($files as $file) {
        print('<tr>');
        if ($file != '.' && $file != '..') {
            print('<td>'. (is_dir($path . $file) ? '<img src="img/folder_pic.png">' : '<img src="img/file_pic.png">') .'</td>');
            print('<td>');
            if (is_dir($path . $file) == 'true') {
                print('<a href="');
                if (isset($_GET['my_way']) == 'true') {
                    print($_SERVER['REQUEST_URI'] . $file . '/'. '">' . $file . '</a>');
                } else print($_SERVER['REQUEST_URI'] . '?my_way=/' . $file . '/'. '">' . $file . '</a>');
            } else 
            print($file.'</td>'); 
            //delete/download file buttons
            print((is_file($path . $file) ? '<td>
                <form method="post" style="display: inline-block"><input type="hidden" name="deletion" value='.str_replace(' ', '&nbsp;', $file).'><input type="submit" value="Delete"></form>
                <form method="post" style="display: inline-block"><input type="hidden" name="download" value='.str_replace(' ', '&nbsp;', $file).'><input type="submit" value="Download"></form>
                </td>' 
                : "<td> Don't touch :) </td>"));
        }       
        print('</tr>');
    }
    print('</table>');
    echo '<br>';

    //back button
    print("\t".'<button><a href="');
    $back_fake = explode('/', $_SERVER['QUERY_STRING']);
    $back_real = explode('/', $_SERVER['QUERY_STRING'],-2);
    if (count($back_fake) == 1 || count($back_fake) == 2) {
        print('?my_way=/'.'">BACK button</a>');
    } else
        print('?'.implode('/',$back_real).'/'.'">BACK button</a>');
        print('</button><br>')
    ?>

    <!-- upload file -->
    <br>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload"/>
        <button type="submit">Upload file</button>
    </form>

    <!-- new directory -->
    <br>
    <form method="get"> 
        <input type="hidden" name="my_way" value="<?php print($_GET['my_way']) ?>" /> 
        <input placeholder="New folder name" type="text" name="new_dir">
        <button type="submit">Create</button>
    </form>

    <!-- logout button -->
    <br>
     <form action="index.php" method="post"> 
        <input type="hidden" name="logout">
        <button type="submit">Logout!</button>
    </form>

</body>
</html>