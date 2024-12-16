<?php
// Tidak perlu memulai session atau memuat file database pada halaman ini
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Services</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header .logo {
            max-width: 120px; /* Ukuran logo yang lebih kecil */
        }

        header .logo img {
            width: 100%; /* Pastikan logo tidak melebihi ukuran container */
            height: auto;
            transform: scale(1.2); /* Memperbesar logo 20% */
            transition: transform 0.3s ease; /* Animasi transisi agar perubahan ukuran lebih halus */
        }

        header .logo:hover img {
            transform: scale(1.1); /* Menambahkan efek saat hover */
        }

        header nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        header nav ul li {
            margin: 0 10px;
        }

        header nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }
        header nav ul li a.btn {
            display: inline-block;
            padding: 12px 25px; /* Menambah padding agar tombol lebih besar */
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px; /* Membuat tombol lebih bulat */
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center; /* Menjaga teks tombol tetap terpusat */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Menambahkan transisi */
        }

        header nav ul li a.btn:hover {
            background-color: #218838; /* Warna lebih gelap saat hover */
            transform: scale(1.05); /* Efek pembesaran saat hover */
        }


        .hero-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 40px 20px;
            background-color: #ffffff;
        }
        .hero-section .content {
            max-width: 50%;
            margin-top: -30px; /* Memindahkan teks lebih tinggi sedikit */
            padding-right: 20px; /* Memberikan sedikit ruang di sisi kanan agar teks tidak terlalu rapat dengan gambar */
        }

        .hero-section .content h1 {
            font-size: 50px; /* Membesarkan ukuran teks judul */
            color: #333;
            line-height: 1.3; /* Memberikan jarak antar baris yang lebih nyaman untuk teks panjang */
            font-weight: bold; /* Menambahkan ketebalan pada teks */
            margin-bottom: 20px; /* Memberikan ruang antara judul dan paragraf */
        }

        .hero-section .content p {
            color: #666;
            margin: 20px 0;
            font-size: 20px; /* Membesarkan ukuran teks paragraf */
            line-height: 1.6; /* Memberikan jarak antar baris untuk kenyamanan membaca */
        }

        .hero-section .content .btn {
            display: inline-block;
            padding: 14px 30px; /* Menambah padding agar tombol lebih besar */
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px; /* Membuat tombol lebih bulat */
            margin-top: 20px; /* Jarak antara tombol dan teks */
            font-size: 20px; /* Membesarkan ukuran teks tombol */
            font-weight: bold; /* Menebalkan teks tombol */
            text-align: center; /* Menjaga agar teks tetap terpusat dalam tombol */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Menambahkan transisi saat hover */
        }

        .hero-section .content .btn:hover {
            background-color: #218838; /* Warna tombol lebih gelap saat hover */
            transform: scale(1.05); /* Efek pembesaran saat hover */
        }


        .hero-section .product-item {
            width: 40%; /* Menyesuaikan lebar gambar */
            aspect-ratio: 1; /* Menetapkan rasio 1:1 */
            position: relative;
            overflow: hidden; /* Agar gambar tidak keluar dari container */
            border-radius: 50%; /* Membuat gambar menjadi bulat */
        }

        .hero-section .product-item img {
            object-fit: cover; /* Memastikan gambar mengisi container dengan benar */
            width: 100%;
            height: 100%;
            border-radius: 50%; /* Memastikan gambar tetap bulat */
        }
        
        header .logo:hover img {
            transform: scale(1.1); /* Menambahkan efek saat hover */
        }
        header .logo {
            max-width: 180px; /* Ukuran logo yang lebih besar */
            margin-left: 20px; /* Menambahkan jarak ke kiri agar logo tidak terlalu dekat dengan sisi */
        }

        header .logo img {
            width: 100%; /* Pastikan logo mengisi lebar container */
            height: auto;
            transform: scale(1.5); /* Memperbesar logo 50% */
            transition: transform 0.3s ease; /* Animasi transisi agar perubahan ukuran lebih halus */
        }

        header .logo:hover img {
            transform: scale(1.3); /* Menambahkan efek saat hover (memperbesar sedikit saat hover) */
        }


    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="./img/logo.png" alt="logo">
        </div>
        <nav>
            <ul>
                <li><a href="login.php" class="btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero-section">
        <div class="content">
            <h1>Pelayanan Kesehatan 
            <span style="color: #28a745;">Poliklinik X</span></h1>
            <p>
                Poliklinik X hadir untuk memberikan pelayanan kesehatan bagi masyarakat dengan mutu pelayanan yang baik. Serta dengan kesetiaan dan kesiapsediaan untuk terus melakukan pengembangan pelayanan kesehatan demi keselamatan dan kesembuhan pasien.
            </p>
            <br>
            <p>Apabila belum mempunyai akun bisa klik tombol register di bawah ini</p>
            <a href="./patient/register.php" class="btn">Register Now</a>
        </div>
        <div class="product-item">
            <img src="./img/doctor.jpg" alt="doctor">
        </div>
    </section>
</body>
</html>
