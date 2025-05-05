<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Waters Complaint Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            z-index: 1000;
            display: none;
            white-space: pre-line;
        }
        .success-positive {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .success-negative {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-neutral {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .loading-message {
            text-align: center;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Nairobi Waters Complaint Form</h1>
    <form id="complaintForm">
        @csrf

        <label for="userName">Name:</label>
        <input type="text" id="userName" name="user_name" value="{{ Auth::user()->name }}" readonly>

        <label for="userEmail">Email:</label>
        <input type="email" id="userEmail" name="user_email" value="{{ Auth::user()->email }}" readonly>

        <label for="userPhone">Phone Number:</label>
        <input type="tel" id="userPhone" name="user_phone">

        <label for="subcounty">Subcounty:</label>
        <select id="subcounty" name="subcounty" required>
            <option value="">Select Subcounty</option>
        </select>

        <label for="ward">Ward:</label>
        <select id="ward" name="ward" required>
            <option value="">Select Ward</option>
        </select>

        <label for="complaint">Complaint:</label>
        <textarea id="complaint" name="complaint" required></textarea>

        <label for="frequency">Frequency:</label>
        <select id="frequency" name="frequency" required>
            <option value="First time">First time</option>
            <option value="Daily">Daily</option>
            <option value="Weekly">Weekly</option>
            <option value="Monthly">Monthly</option>
            <option value="Other">Other</option>
        </select>

        <label for="entityType">Entity Type:</label>
        <select id="entityType" name="entity_type" required>
            <option value="Individual">Individual</option>
            <option value="Estate">Estate</option>
            <option value="School">School</option>
            <option value="Hospital">Hospital</option>
            <option value="Other">Other</option>
        </select>

        <label for="entityName">Entity Name:</label>
        <input type="text" id="entityName" name="entity_name">

        <button type="submit">Submit Complaint</button>
    </form>

    <div id="loadingMessage" class="loading-message">Submitting your complaint... Please wait.</div>
</div>

<div id="popupMessage" class="popup-message"></div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        fetch('/get-subcounties')
            .then(response => response.json())
            .then(data => {
                const subcountySelect = document.getElementById('subcounty');
                subcountySelect.innerHTML = '<option value="">Select Subcounty</option>';
                data.forEach(subcounty => {
                    const option = document.createElement('option');
                    option.value = subcounty.subcounty;
                    option.textContent = subcounty.subcounty;
                    subcountySelect.appendChild(option);
                });
            });
    });

    document.getElementById('subcounty').addEventListener('change', function () {
        const selectedSubcounty = this.value;
        const wardSelect = document.getElementById('ward');
        wardSelect.innerHTML = '<option value="">Loading wards...</option>';

        if (selectedSubcounty) {
            fetch(`/get-wards/${encodeURIComponent(selectedSubcounty)}`)
                .then(response => response.json())
                .then(data => {
                    wardSelect.innerHTML = '<option value="">Select Ward</option>';
                    data.forEach(ward => {
                        const option = document.createElement('option');
                        option.value = ward;
                        option.textContent = ward;
                        wardSelect.appendChild(option);
                    });
                });
        }
    });

    document.getElementById('complaintForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const popupMessage = document.getElementById('popupMessage');
        popupMessage.className = 'popup-message';
        popupMessage.style.display = 'none';

        const loadingMessage = document.getElementById('loadingMessage');
        loadingMessage.style.display = 'block';

        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData);

        if (!data.complaint) {
            popupMessage.classList.add('error');
            popupMessage.textContent = 'Complaint text is required.';
            popupMessage.style.display = 'block';
            loadingMessage.style.display = 'none';
            setTimeout(() => popupMessage.style.display = 'none', 5000);
            return;
        }

        fetch('http://localhost:5001/analyze', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_name: document.getElementById('userName').value,
                user_email: document.getElementById('userEmail').value,
                user_phone: document.getElementById('userPhone').value,
                subcounty: document.getElementById('subcounty').value,
                ward: document.getElementById('ward').value,
                complaint: document.getElementById('complaint').value,
                frequency: document.getElementById('frequency').value,
                entity_type: document.getElementById('entityType').value,
                entity_name: document.getElementById('entityName').value
            })
        })
        .then(response => response.json())
        .then(result => {
            loadingMessage.style.display = 'none';
            let sentimentClass = '';
            let sentimentMessage = '';

            switch (result.sentiment.toLowerCase()) {
                case 'positive':
                    sentimentClass = 'success-positive';
                    sentimentMessage = '😊 We appreciate your kind feedback! It’s great to know things are working well. ';
                    break;
                case 'negative':
                    sentimentClass = 'success-negative';
                    sentimentMessage = '😠 Thanks for speaking up! We hear your concerns and will act swiftly to address them. ';
                    break;
                case 'neutral':
                default:
                    sentimentClass = 'success-neutral';
                    sentimentMessage = '😐 Thank you for your report. We’ve received your feedback and will look into it. ';
                    break;
            }

            popupMessage.classList.add(sentimentClass);
            popupMessage.textContent = `${sentimentMessage}\n Category: ${result.category}`;
            popupMessage.style.display = 'block';
            document.getElementById('complaintForm').reset();
            setTimeout(() => popupMessage.style.display = 'none', 7000);
        })
        .catch(error => {
            loadingMessage.style.display = 'none';
            popupMessage.classList.add('error');
            popupMessage.textContent = 'Error: ' + error.message;
            popupMessage.style.display = 'block';
            setTimeout(() => popupMessage.style.display = 'none', 5000);
        });
    });
</script>
</body>
</html>
