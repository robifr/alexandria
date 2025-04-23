<?php
// inisialisasi session
session_start();
require 'conn.php';
// cek user aktif apa ngak
if(!isset($_SESSION['user'])){
    // link untuk mengarah ke login
    header('location:login.php');
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexandria</title>
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Eagle+Lake&family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

<!-- link css -->
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <?php
    if (isset($_POST['submit']) && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $user_id = $_SESSION['user']['id_user'];
    
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
    
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $target_dir = 'Profile/';
            $target_file = $target_dir . $new_file_name;
    
            // hapus file lama kalau ada
            $query = mysqli_query($conn, "SELECT profile FROM user WHERE id_user = '$user_id'");
            $data = mysqli_fetch_assoc($query);
            if (!empty($data['profile']) && file_exists($target_dir . $data['profile'])) {
                unlink($target_dir . $data['profile']);
            }
    
            if (move_uploaded_file($file_tmp, $target_file)) {
                $sql = "UPDATE user SET profile = '$new_file_name' WHERE id_user = '$user_id'";
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['user']['profile'] = $new_file_name;
                    echo "Foto berhasil di-upload.";
                } else {
                    echo "Gagal menyimpan ke database.";
                }
            } else {
                echo "Gagal memindahkan file.";
            }
        } else {
            echo "Ekstensi file tidak diizinkan.";
        }
    } else {
        echo "Silakan pilih gambar sebelum meng-upload.";
    }
    

    $profile = isset($_SESSION['user']['profile']) && $_SESSION['user']['profile'] != null
    ? 'Profile/' . $_SESSION['user']['profile']
    : 'asset/icon/account.svg'; // jika NULL, pakai default
?>

    <div class="top_nav">
        <a href="#"><span>Alexandria</span></a>
        <div class="option">
            <input type="search" name="search" placeholder="Search book">
            <a href="#" id="profileToggle"><?php echo $_SESSION['user']['name']; ?></a>
            <a href="#" id="profileToggleImg"><img src="<?php echo $profile;?>" alt=""></a>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="profile_picture" id="profilePanel">
        <div class="content">
            <form action="" method="post" autocomplete="off" enctype="multipart/form-data" class="upload-form">
            <label for="image" class="image-label">
        <img 
            src="Profile/<?php echo isset($_SESSION['user']['profile']) ? $_SESSION['user']['profile'] : '../asset/img/default.jpg'; ?>" 
            alt="Profile" 
            class="profile-img"
        >
        <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png">
    </label>
    <button type="submit" name="submit" class="upload-btn">+</button>
            </form>
            <p><?php echo $_SESSION['user']['name']; ?></p>
            <a href="logout.php"><img src="asset/icon/logout.svg" alt="logout"><span>Log out</span></a>
        </div>
    </div>



     <div class="left_nav">
        <a href=""><img src="asset/icon/book.svg" alt="book"><span>Book</span></a>
        <a href=""><img src="asset/icon/star.svg" alt="star"><span>Favorite</span></a>
        <a href=""><img src="asset/icon/history.svg" alt="history"><span>History</span></a>
        <a href="logout.php"><img src="asset/icon/logout.svg" alt="logout"><span>Log out</span></a>
     </div>


    <div class="sort">
        <a href="">All</a>
        <a href="">History</a>
        <a href="">Novel</a>
        <a href="">Education</a>
    </div>

    <div class="container">
        <div class="card">
            <img src="asset/img/bumi.jpg" alt="image">
            <span>Bumi <br> Tere liye</span>
        </div>
        <div class="card">
            <img src="asset/img/Comet.jpg" alt="image">
            <span>Comet <br> Tere liye</span>
        </div>
        <div class="card">
            <img src="asset/img/Matahari.jpg" alt="image">
            <span>Matahari <br> Tere liye</span>
        </div>
        <div class="card">
            <img src="asset/img/Siputih.jpg" alt="image">
            <span>Si putih <br> Tere liye</span>
        </div>
        <div class="card">
            <img src="asset/img/Constatinopel.jpg" alt="image">
            <span>Constatinopel <br> Roger crowley</span>
        </div>
        <div class="card">
            <img src="asset/img/hatta.jpg" alt="image">
            <span>Hatta<br> wartawan tempo</span>
        </div>
        <div class="card">
            <img src="asset/img/kisah TJ.jpg" alt="image">
            <span>Kisah Tanah Jawa <br>Bonaventura D. Genta</span>
        </div>
        <div class="card">
            <img src="asset/img/Teh dan penghianatan.jpg" alt="image">
            <span>Teh dan Penghianatan<br> Iksaka banu</span>
        </div>
        <div class="card">
            <img src="asset/img/Harry potter.jpg" alt="image">
            <span>Harry potter <br>j.k rowling</span>
        </div>
        <div class="card">
            <img src="asset/img/The Lovely Dark.jpg" alt="image">
            <span>The Lovely Dark<br>Mathew fox</span>
        </div>
        <div class="card">
            <img src="asset/img/The skeleton man.jpg" alt="image">
            <span>The Skeleton Man <br>joseph bruchac</span>
        </div>
        <div class="card">
            <img src="asset/img/Night books.jpg" alt="image">
            <span>NightBooks <br> J.A White</span>
        </div>
    </div>

    <!-- javascript -->
     <script src="java/script.js"></script>
</body>
</html>