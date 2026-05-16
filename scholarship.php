<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GD EDU TECH | Admission Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-red: #cf5153;
            --primary-blue: #0078a8;
            --glass-bg: rgba(255, 255, 255, 0.98);
        }

        * { box-sizing: border-box; transition: all 0.2s ease-in-out; }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('back.jpeg');
            background-attachment: fixed;
            background-size: cover;
            margin: 0;
            display: flex;
            justify-content: center;
            padding: 20px 10px; /* Reduced padding for mobile */
            min-height: 100vh;
        }

        .form-card {
            width: 100%;
            max-width: 800px;
            background: var(--glass-bg);
            border-radius: 15px; /* Softer corners for mobile */
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            position: relative;
        }

        /* Responsive Header */
        .header {
            background: #ffffff;
            padding: 20px 15px;
            text-align: center;
            border-bottom: 4px solid var(--primary-red);
        }

        .logo {
            max-width: 180px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .header p { 
            margin: 8px 0 0; 
            color: #333; 
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Success Overlay */
        #successOverlay {
            display: none;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff;
            z-index: 100;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .success-icon { font-size: 4rem; color: #4CAF50; margin-bottom: 15px; }

        .container { padding: 25px 20px; }

        .section-head {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-blue);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin: 25px 0 15px;
        }

        .section-head::after { content: ""; flex: 1; height: 1px; background: #eee; }

        .field-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 6px;
        }

        .required { color: var(--primary-red); }

        /* --- THE RESPONSIVE GRID --- */
        .grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            margin-bottom: 15px; 
        }

        /* Mobile View Adjustments */
        @media (max-width: 650px) {
            .grid { grid-template-columns: 1fr; } /* Stack fields vertically */
            .container { padding: 20px 15px; }
            .header p { font-size: 0.75rem; }
            .btn-submit { padding: 15px; font-size: 1rem; }
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #fff;
            font-size: 1rem; /* Prevents iOS auto-zoom on focus */
            -webkit-appearance: none; /* Clean look on iOS */
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0,120,168,0.1);
        }

        .sub-text { font-size: 0.7rem; color: #777; margin-top: 4px; display: block; }

        .btn-submit {
            width: 100%;
            padding: 18px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 30px;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-submit:active { transform: scale(0.98); }

        .footer {
            background: #2b333f;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div id="successOverlay">
        <i class="fa fa-circle-check success-icon"></i>
        <h2 style="margin:0;">Submitted!</h2>
        <p>Your response has been recorded.</p>
        <button onclick="location.reload()" class="btn-submit" style="max-width: 200px; margin-top: 20px;">Submit Another</button>
    </div>

    <div class="header">
        <img src="logo.png" alt="GD EDU TECH" class="logo">
        <p>Scholarship Form 2026-27</p>
    </div>

<form class="container" id="fullForm" method="POST" action="submit.php" enctype="multipart/form-data">
        
        <div class="section-head"><i class="fa fa-id-card"></i> Student Identity</div>
        <div class="grid">
            <div class="field-group">
                <label class="field-label">First Name <span class="required">*</span></label>
                <input type="text" name="firstName" placeholder="As per SSLC" required>
            </div>
            <div class="field-group">
                <label class="field-label">Last Name <span class="required">*</span></label>
                <input type="text" name="lastName" placeholder="Surname" required>
            </div>
        </div>

        <div class="section-head"><i class="fa fa-home"></i> Background</div>
        <div class="field-group" style="margin-bottom: 15px;">
            <label class="field-label">Residential Address</label>
            <textarea name="address" rows="3" placeholder="Full permanent address"></textarea>
        </div>
        <div class="field-group">
            <label class="field-label">School Details</label>
            <textarea name="school" rows="2" placeholder="School name & location"></textarea>
        </div>

        <div class="section-head"><i class="fa fa-phone"></i> Verification</div>
        <div class="field-group">
            <label class="field-label">Phone Number <span class="required">*</span></label>
            <div style="display:flex; gap:5px;">
                <input type="tel" value="+91" style="width:60px; text-align:center;" readonly tabindex="-1">
                <input type="tel" name="phone1" id="p1" placeholder="9876543210" required pattern="[0-9]{10}">
            </div>
        </div>

        <div class="section-head"><i class="fa fa-book-open"></i> Course Selection</div>
        <div class="grid">
            <div class="field-group">
                <label class="field-label">Preferred Course <span class="required">*</span></label>
                <select name="course" required>
                    <option value="">Select Option</option>
                    <option>Digital Marketing</option>
                    <option>Graphic Designing</option>
                    <option>Architecture Designing</option>
                    <option>Interior Designing</option>
                    <option>FullStack development</option>
                    <option>Video Editing</option>
                </select>
            </div>
            <div class="field-group">
                <label class="field-label" style="color: purple;">SSLC Medium</label>
                <select name="medium">
                    <option value="">Select Medium</option>
                    <option>English</option>
                    <option>Kannada</option>
                    <option>Urdu</option>
                    <option>Malayalam</option>
                </select>
            </div>
        </div>

        <div class="field-group" style="margin-top:15px;">
            <label class="field-label">Languages Known <span class="required">*</span></label>
            <input type="text" name="langTyped" id="langInput" placeholder="e.g. English, Hindi, Kannada" required>
            <span class="sub-text">Separate multiple languages with commas.</span>
        </div>
        
        <div class="section-head"><i class="fa fa-upload"></i> Upload Documents</div>

<div class="field-group" style="margin-bottom:15px;">
    <label class="field-label">Upload Your Markscard <span class="required">*</span></label>
    <input type="file" name="document" required accept=".jpg,.jpeg,.png,.pdf">
</div>

<div class="field-group">
    <label class="field-label">Upload Photo</label>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png">
</div>

        <button type="submit" class="btn-submit" id="submitBtn">Submit Application</button>
    </form>

    <div class="footer">
        <strong>GD EDU TECH | Mangalore</strong>
    </div>
</div>

<script>
document.getElementById('fullForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;
});
</script>

</body>
</html>