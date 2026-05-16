<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        /* Reset some default styles */
        body, h1, h2, h3, p, ul, li {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            background-color: #d30043;
            color: #fff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .profile-info h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .profile-info p {
            font-size: 16px;
        }

        .profile-details {
            background-color: #fff;
            border-radius: 0 0 5px 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-details h2 {
            color: #d30043;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .profile-details p {
            margin-bottom: 10px;
        }

        .profile-details ul {
            list-style-type: none;
            padding-left: 0;
        }

        .profile-details li {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
        }

        .profile-details li:last-child {
            border-bottom: none;
        }

        .profile-details .label {
            font-weight: bold;
        }

        .profile-details .value {
            color: #ff95008d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <img src="profile-image.jpg" alt="Profile Image" class="profile-image">
            <div class="profile-info">
                <h1>John Doe</h1>
                <p>Software Engineer</p>
            </div>
        </div>
        <div class="profile-details">
            <h2>Personal Information</h2>
            <ul>
                <li>
                    <span class="label">Date of Birth:</span>
                    <span class="value">January 1, 1985</span>
                </li>
                <li>
                    <span class="label">Email:</span>
                    <span class="value">johndoe@example.com</span>
                </li>
                <li>
                    <span class="label">Phone:</span>
                    <span class="value">+1 (555) 123-4567</span>
                </li>
                <li>
                    <span class="label">Address:</span>
                    <span class="value">123 Main Street, Anytown USA</span>
                </li>
            </ul>
            <h2>Document Details</h2>
            <ul>
                <li>
                    <span class="label">ID Number:</span>
                    <span class="value">123456789</span>
                </li>
                <li>
                    <span class="label">Expiry Date:</span>
                    <span class="value">June 30, 2025</span>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>