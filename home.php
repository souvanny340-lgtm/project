<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบประเมินการสอนอาจารย์</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: Arial, sans-serif;
        }

        body{
            background:#f4f6f9;
        }

        .navbar{
            background:#2563eb;
            color:white;
            padding:15px 30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .navbar h1{
            font-size:22px;
        }

        .navbar a{
            color:white;
            text-decoration:none;
            margin-left:15px;
        }

        .hero{
            height:300px;
            background:linear-gradient(to right,#2563eb,#3b82f6);
            color:white;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            text-align:center;
            padding:20px;
        }

        .hero h2{
            font-size:40px;
            margin-bottom:10px;
        }

        .hero p{
            font-size:18px;
            margin-bottom:20px;
        }

        .btn{
            background:white;
            color:#2563eb;
            padding:12px 25px;
            border-radius:8px;
            text-decoration:none;
            font-weight:bold;
        }

        .container{
            width:90%;
            margin:auto;
            padding:40px 0;
        }

        .cards{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.1);
            text-align:center;
        }

        .card h3{
            margin-bottom:10px;
            color:#2563eb;
        }

        footer{
            background:#1e293b;
            color:white;
            text-align:center;
            padding:15px;
            margin-top:30px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <h1>Evaluation System</h1>

        <div>
            <a href="#">ຫນ້າຫຼັກ</a>
            <a href="#">ປະເມີນ</a>
            <a href="#">ລາຍງາບ</a>
            <a href="#">ເຂົ້າສູ່ລະບົບ</a>
        </div>
    </div>

    <!-- Hero -->
    <section class="hero">
        <h2>ລະບົບການປະເມີນການສອນຂອງອາຈານ</h2>
        <p></p>

        <a href="#" class="btn">ເລີ່ມການປະເມີນ</a>
    </section>

    <!-- Content -->
    <div class="container">

        <div class="cards">

            <div class="card">
                <h3>ປະເມີນອອນລາຍ</h3>
                <p>ນັກສືກສາປະເມີນທາງອອນລາຍ</p>
            </div>

            <div class="card">
                <h3>ສະຫຼຸບການປະເມີນ</h3>
                <p>ສະແດງຜົນການປະເມີນ</p>
            </div>

            <div class="card">
                <h3>ໃຊ້ງ່າຍງານ</h3>
                <p>ຮອງຮັບມືຖື ແລັບທ໋ອບ</p>
            </div>

        </div>

    </div>

    <!-- Footer -->
    <footer>
        © 2026 ລະບົບການປະເມີນການສອນຂອງອາຈານ
    </footer>

</body>
</html>

