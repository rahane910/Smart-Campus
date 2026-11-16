<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Portal</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body{
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            background: linear-gradient(135deg, #667eea, #764ba2, #6a11cb);
            background-size: 300% 300%;
            animation: gradientMove 8s ease infinite;
            padding:20px;
        }

        @keyframes gradientMove{
            0%{background-position:0% 50%;}
            50%{background-position:100% 50%;}
            100%{background-position:0% 50%;}
        }

        .container{
            width:100%;
            max-width:450px;
        }

        .box{
            padding:45px 35px;
            border-radius:24px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(18px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
            text-align:center;
            color:white;
            border:1px solid rgba(255,255,255,0.2);
        }

        .logo{
            font-size:55px;
            margin-bottom:15px;
        }

        .box h2{
            font-size:30px;
            font-weight:700;
            margin-bottom:8px;
        }

        .subtitle{
            font-size:14px;
            opacity:0.9;
            margin-bottom:30px;
        }

        .reg-btn{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            width:100%;
            padding:16px;
            border-radius:14px;
            text-decoration:none;
            font-size:18px;
            font-weight:600;
            color:white;
            background: rgba(255,255,255,0.18);
            border:1px solid rgba(255,255,255,0.25);
            transition: all 0.3s ease;
            margin-bottom:20px;
        }

        .reg-btn:hover{
            transform: translateY(-4px);
            background: rgba(255,255,255,0.28);
            box-shadow: 0 8px 22px rgba(0,0,0,0.2);
        }

        .icon{
            font-size:24px;
        }

        .login-link{
            margin-top:18px;
            font-size:14px;
        }

        .login-link a{
            color:white;
            text-decoration:none;
            font-weight:600;
        }

        .login-link a:hover{
            text-decoration:underline;
        }

        .footer-text{
            margin-top:25px;
            font-size:13px;
            opacity:0.8;
        }

        @media(max-width:500px){
            .box{
                padding:35px 25px;
            }

            .box h2{
                font-size:26px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="box">

            <div class="logo">🎓</div>

            <h2>Student Registration</h2>
            <p class="subtitle">
                Welcome to Smart Campus Management System
            </p>

            <a href="student_register.php" class="reg-btn">
                <span class="icon">📝</span>
                Register as Student
            </a>

            <div class="login-link">
                Already have an account?
                <a href="login.php">Login here</a>
            </div>

            <p class="footer-text">
                Secure • Fast • Professional
            </p>

        </div>
    </div>

</body>
</html>